<?php

namespace App\Console\Commands;

use App\Models\AmcContract;
use App\Models\AmcVisit;
use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\Quotation;
use App\Services\AlertNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAutomatedRemindersCommand extends Command
{
    protected $signature = 'alerts:send-reminders {--force : Bypass deduplication checks}';

    protected $description = 'Scan CRM for upcoming AMC visits (48h), expiring quotations (3 days), expiring AMC contracts (15 days), and overdue invoices, and send automated WhatsApp/SMS/Email alerts.';

    public function handle(AlertNotificationService $alertService, \App\Services\StockAlertService $stockAlertService): int
    {
        $this->info("🚀 Starting automated CCTV CRM reminder sweep...");
        $force = (bool) $this->option('force');
        $stats = [
            'quotation_expiries'  => 0,
            'amc_expiries'        => 0,
            'amc_visits'          => 0,
            'overdue_invoices'    => 0,
            'low_stock_alerts'    => 0,
        ];

        // 1. Quotation Expiry Reminders (Expiring within the next 3 days)
        $expiringQuotes = Quotation::with('lead')
            ->whereIn('status', ['draft', 'sent'])
            ->whereNotNull('valid_until')
            ->whereBetween('valid_until', [Carbon::today(), Carbon::today()->addDays(3)])
            ->get();

        foreach ($expiringQuotes as $quote) {
            $lead = $quote->lead;
            if (!$lead) {
                continue;
            }

            // Deduplication: check if already notified for quotation_expiring_soon within the last 3 days
            $alreadyNotified = !$force && NotificationLog::where('event_type', 'quotation_expiring_soon')
                ->where('reference_type', Quotation::class)
                ->where('reference_id', $quote->id)
                ->where('created_at', '>=', Carbon::now()->subDays(3))
                ->exists();

            if (!$alreadyNotified) {
                $alertService->sendAlert('quotation_expiring_soon', $lead, [
                    'customer_name' => $lead->customer_name,
                    'quote_no'      => $quote->quotation_no,
                    'total_amount'  => '₹' . number_format($quote->total, 2),
                    'valid_until'   => $quote->valid_until ? $quote->valid_until->format('d M Y') : '3 days',
                    'link'          => route('quotations.public-pdf', ['quotation' => $quote, 'format' => '1']),
                ], $quote);
                $stats['quotation_expiries']++;
            }
        }
        $this->info("  ✓ Dispatched {$stats['quotation_expiries']} Quotation expiry reminders (3 days notice).");

        // 2. Expiring AMC Contracts (Expiring within next 15 days)
        $expiringContracts = AmcContract::with('lead')
            ->where('status', 'active')
            ->whereBetween('end_date', [Carbon::today(), Carbon::today()->addDays(15)])
            ->get();

        foreach ($expiringContracts as $contract) {
            $lead = $contract->lead;
            if (!$lead) {
                continue;
            }

            // Deduplication: check if already notified for amc_expiry_alert within the last 14 days
            $alreadyNotified = !$force && NotificationLog::where('event_type', 'amc_expiry_alert')
                ->where('reference_type', AmcContract::class)
                ->where('reference_id', $contract->id)
                ->where('created_at', '>=', Carbon::now()->subDays(14))
                ->exists();

            if (!$alreadyNotified) {
                $alertService->sendAlert('amc_expiry_alert', $lead, [
                    'customer_name' => $lead->customer_name,
                    'contract_no'   => $contract->contract_no,
                    'expiry_date'   => $contract->end_date->format('d M Y'),
                    'renewal_link'  => route('portal.amc'),
                ], $contract);
                $stats['amc_expiries']++;
            }
        }
        $this->info("  ✓ Dispatched {$stats['amc_expiries']} AMC expiration renewal alerts (15 days notice).");

        // 3. Upcoming AMC Visits (Within next 48 hours)
        $upcomingVisits = AmcVisit::with(['amcContract.lead', 'assignedTechnician'])
            ->where('status', 'pending')
            ->whereBetween('scheduled_date', [Carbon::today(), Carbon::today()->addDays(2)])
            ->get();

        foreach ($upcomingVisits as $visit) {
            $lead = $visit->amcContract?->lead;
            if (!$lead) {
                continue;
            }

            $alreadyNotified = !$force && NotificationLog::where('event_type', 'amc_visit_reminder')
                ->where('reference_type', AmcVisit::class)
                ->where('reference_id', $visit->id)
                ->where('created_at', '>=', Carbon::now()->subDays(2))
                ->exists();

            if (!$alreadyNotified) {
                $alertService->sendAlert('amc_visit_reminder', $lead, [
                    'customer_name'   => $lead->customer_name,
                    'contract_no'     => $visit->amcContract->contract_no,
                    'visit_date'      => $visit->scheduled_date->format('d M Y'),
                    'technician_name' => $visit->assignedTechnician?->name ?? 'Assigned Engineer',
                ], $visit);
                $stats['amc_visits']++;
            }
        }
        $this->info("  ✓ Dispatched {$stats['amc_visits']} AMC visit reminders (48h notice).");

        // 4. Overdue Invoices
        $overdueInvoices = Invoice::with(['quotation.lead', 'payments'])
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->where('due_date', '<', Carbon::today())
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $lead = $invoice->quotation?->lead;
            if (!$lead) {
                continue;
            }

            $dueAmount = $invoice->balanceDue();
            $daysOverdue = (int) $invoice->due_date->diffInDays(Carbon::today());

            if ($dueAmount > 0) {
                $alreadyNotified = !$force && NotificationLog::where('event_type', 'payment_overdue')
                    ->where('reference_type', Invoice::class)
                    ->where('reference_id', $invoice->id)
                    ->where('created_at', '>=', Carbon::now()->subDays(7))
                    ->exists();

                if (!$alreadyNotified) {
                    $alertService->sendAlert('payment_overdue', $lead, [
                        'customer_name' => $lead->customer_name,
                        'invoice_no'    => $invoice->invoice_no,
                        'amount_due'    => '₹' . number_format($dueAmount, 2),
                        'days_overdue'  => $daysOverdue,
                    ], $invoice);
                    $stats['overdue_invoices']++;
                }
            }
        }
        $this->info("  ✓ Dispatched {$stats['overdue_invoices']} Overdue payment notices.");

        // 5. Stock Low-Threshold Sweep
        $stats['low_stock_alerts'] = $stockAlertService->sweepLowStock($force);
        $this->info("  ✓ Dispatched {$stats['low_stock_alerts']} Inventory low-stock threshold alerts.");

        $this->info("✅ Automated reminder sweep complete!");
        return Command::SUCCESS;
    }
}

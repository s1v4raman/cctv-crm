<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Lead;
use App\Models\NotificationLog;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AccountsReceivableService
{
    protected AlertNotificationService $alertService;

    public function __construct(AlertNotificationService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * Compute macro Debtors Aging & Accounts Receivable Summary
     */
    public function getDebtorsAgingSummary(?Carbon $asOfDate = null): array
    {
        $asOf = $asOfDate ?: now();

        $invoices = Invoice::with(['quotation.lead', 'payments'])
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->orderBy('invoice_date')
            ->get();

        $totalReceivableBalance = 0.0;
        $totalOverdueBalance = 0.0;
        $debtorLeadIds = [];

        $buckets = [
            '0_30' => [
                'label'         => 'Current (0–30 Days)',
                'days_range'    => '0 to 30 Days',
                'amount'        => 0.0,
                'count'         => 0,
                'badge_class'   => 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-300 dark:border-blue-800',
                'color'         => 'blue',
            ],
            '31_60' => [
                'label'         => '31–60 Days Overdue',
                'days_range'    => '31 to 60 Days',
                'amount'        => 0.0,
                'count'         => 0,
                'badge_class'   => 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300 dark:border-amber-800',
                'color'         => 'amber',
            ],
            '61_90' => [
                'label'         => '61–90 Days Overdue',
                'days_range'    => '61 to 90 Days',
                'amount'        => 0.0,
                'count'         => 0,
                'badge_class'   => 'bg-orange-100 text-orange-800 dark:bg-orange-950/70 dark:text-orange-300 border-orange-300 dark:border-orange-800',
                'color'         => 'orange',
            ],
            '90_plus' => [
                'label'         => '90+ Days Critical',
                'days_range'    => 'Over 90 Days',
                'amount'        => 0.0,
                'count'         => 0,
                'badge_class'   => 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-300 dark:border-rose-800 animate-pulse',
                'color'         => 'rose',
            ],
        ];

        /** @var \App\Models\Invoice $inv */
        foreach ($invoices as $inv) {
            $balance = (float) $inv->balanceDue();
            if ($balance <= 0.01) {
                continue;
            }

            $totalReceivableBalance += $balance;
            if ($inv->quotation?->lead_id) {
                $debtorLeadIds[$inv->quotation->lead_id] = true;
            }

            // Calculate overdue age
            $dueDate = $inv->due_date ? Carbon::parse($inv->due_date) : Carbon::parse($inv->invoice_date)->addDays(15);
            $isOverdue = $asOf->gt($dueDate);
            $daysOverdue = $isOverdue ? $asOf->diffInDays($dueDate) : 0;

            if ($isOverdue) {
                $totalOverdueBalance += $balance;
            }

            // Assign aging bucket
            if ($daysOverdue <= 30) {
                $bucketKey = '0_30';
            } elseif ($daysOverdue <= 60) {
                $bucketKey = '31_60';
            } elseif ($daysOverdue <= 90) {
                $bucketKey = '61_90';
            } else {
                $bucketKey = '90_plus';
            }

            $buckets[$bucketKey]['amount'] += $balance;
            $buckets[$bucketKey]['count']++;
        }

        // Compute DSO (Days Sales Outstanding) based on last 90 days total billing
        $ninetyDaysAgo = $asOf->copy()->subDays(90);
        $totalSalesLast90Days = (float) Invoice::whereBetween('invoice_date', [$ninetyDaysAgo->toDateString(), $asOf->toDateString()])->sum('total');
        $dailyAvgSales = $totalSalesLast90Days > 0 ? ($totalSalesLast90Days / 90.0) : 0.0;
        $dsoDays = $dailyAvgSales > 0 ? round($totalReceivableBalance / $dailyAvgSales, 1) : 0.0;
        $totalInvoicesCount = $buckets['0_30']['count'] + $buckets['31_60']['count'] + $buckets['61_90']['count'] + $buckets['90_plus']['count'];

        return [
            'as_of_date'                 => $asOf->format('d M Y'),
            'total_receivable_balance'   => round($totalReceivableBalance, 2),
            'total_outstanding'          => round($totalReceivableBalance, 2),
            'total_overdue_balance'      => round($totalOverdueBalance, 2),
            'total_current_balance'      => round($buckets['0_30']['amount'], 2),
            'current_0_30'               => round($buckets['0_30']['amount'], 2),
            'overdue_31_60'              => round($buckets['31_60']['amount'], 2),
            'critical_61_90'             => round($buckets['61_90']['amount'], 2),
            'high_risk_90_plus'          => round($buckets['90_plus']['amount'], 2),
            'debtors_count'              => count($debtorLeadIds),
            'total_debtors_count'        => count($debtorLeadIds),
            'total_invoices_count'       => $totalInvoicesCount,
            'overdue_invoices_count'     => $buckets['31_60']['count'] + $buckets['61_90']['count'] + $buckets['90_plus']['count'],
            'dso_days'                   => $dsoDays,
            'buckets'                    => $buckets,
        ];
    }

    /**
     * Get Customer-wise aggregated Debtors Ledger
     */
    public function getCustomerDebtorsList(array $filters = [], ?Carbon $asOfDate = null): array
    {
        $asOf = $asOfDate ?: now();

        $query = Lead::whereHas('quotations.invoices', function ($q) {
            $q->whereIn('status', ['unpaid', 'partially_paid', 'overdue']);
        })->with(['quotations.invoices' => function ($q) {
            $q->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->with('payments');
        }]);

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('customer_name', 'like', "%{$s}%")
                  ->orWhere('company_legal_name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('gstin', 'like', "%{$s}%");
            });
        }

        $leads = $query->get();
        $debtorsList = [];

        foreach ($leads as $lead) {
            $invoices = $lead->quotations->flatMap->invoices;
            $unpaidInvoiceCount = 0;
            $totalInvoiced      = 0.0;
            $totalPaid          = 0.0;
            $balanceDue         = 0.0;
            $days_0_30          = 0.0;
            $days_31_60         = 0.0;
            $days_61_90         = 0.0;
            $days_90_plus       = 0.0;
            $oldestDueDate      = null;
            $highestDaysOverdue = 0;

            foreach ($invoices as $inv) {
                $invBalance = (float) $inv->balanceDue();
                if ($invBalance <= 0.01) {
                    continue;
                }

                $unpaidInvoiceCount++;
                $totalInvoiced += (float) $inv->total;
                $totalPaid     += (float) $inv->amount_paid;
                $balanceDue    += $invBalance;

                $dueDate = $inv->due_date ? Carbon::parse($inv->due_date) : Carbon::parse($inv->invoice_date)->addDays(15);
                if (!$oldestDueDate || $dueDate->lt($oldestDueDate)) {
                    $oldestDueDate = $dueDate;
                }

                $invDaysOverdue = 0;
                if ($asOf->gt($dueDate)) {
                    $invDaysOverdue = $asOf->diffInDays($dueDate);
                    if ($invDaysOverdue > $highestDaysOverdue) {
                        $highestDaysOverdue = $invDaysOverdue;
                    }
                }

                if ($invDaysOverdue <= 30) {
                    $days_0_30 += $invBalance;
                } elseif ($invDaysOverdue <= 60) {
                    $days_31_60 += $invBalance;
                } elseif ($invDaysOverdue <= 90) {
                    $days_61_90 += $invBalance;
                } else {
                    $days_90_plus += $invBalance;
                }
            }

            if ($balanceDue <= 0.01) {
                continue;
            }

            // Determine customer-level aging bucket
            if ($highestDaysOverdue <= 30) {
                $bucketLabel = '0–30 Days';
                $bucketBadgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-300 dark:border-blue-800';
            } elseif ($highestDaysOverdue <= 60) {
                $bucketLabel = '31–60 Days';
                $bucketBadgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300 dark:border-amber-800';
            } elseif ($highestDaysOverdue <= 90) {
                $bucketLabel = '61–90 Days';
                $bucketBadgeClass = 'bg-orange-100 text-orange-800 dark:bg-orange-950/70 dark:text-orange-300 border-orange-300 dark:border-orange-800';
            } else {
                $bucketLabel = '90+ Days Critical';
                $bucketBadgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-300 dark:border-rose-800 animate-pulse';
            }

            // Bucket filter
            if (!empty($filters['bucket'])) {
                if ($filters['bucket'] === '0_30' && $highestDaysOverdue > 30) continue;
                if ($filters['bucket'] === '31_60' && ($highestDaysOverdue <= 30 || $highestDaysOverdue > 60)) continue;
                if ($filters['bucket'] === '61_90' && ($highestDaysOverdue <= 60 || $highestDaysOverdue > 90)) continue;
                if ($filters['bucket'] === '90_plus' && $highestDaysOverdue <= 90) continue;
            }

            // Check latest reminder log
            $latestReminder = NotificationLog::where('recipient_phone', $lead->phone)
                ->whereIn('event_type', ['payment_overdue', 'payment_reminder', 'invoice_generated'])
                ->latest('sent_at')
                ->first();

            $debtorsList[] = [
                'lead_id'               => $lead->id,
                'customer_name'         => $lead->company_legal_name ?: $lead->customer_name,
                'contact_person'        => $lead->customer_name,
                'phone'                 => $lead->phone,
                'email'                 => $lead->email,
                'site_address'          => $lead->site_address ?? 'N/A',
                'gstin'                 => $lead->gstin ?? 'Unregistered',
                'unpaid_invoice_count'  => $unpaidInvoiceCount,
                'unpaid_invoices_count' => $unpaidInvoiceCount,
                'days_0_30'             => round($days_0_30, 2),
                'days_31_60'            => round($days_31_60, 2),
                'days_61_90'            => round($days_61_90, 2),
                'days_90_plus'          => round($days_90_plus, 2),
                'total_invoiced'        => round($totalInvoiced, 2),
                'total_paid'            => round($totalPaid, 2),
                'balance_due'           => round($balanceDue, 2),
                'total_due'             => round($balanceDue, 2),
                'oldest_due_date'       => $oldestDueDate ? $oldestDueDate->format('d M Y') : 'N/A',
                'highest_days_overdue'  => $highestDaysOverdue,
                'bucket_label'          => $bucketLabel,
                'bucket_badge_class'    => $bucketBadgeClass,
                'last_reminder_at'      => $latestReminder?->sent_at?->format('d M Y, h:i A') ?: 'Never Sent',
            ];
        }

        // Sort by balance due descending
        usort($debtorsList, fn($a, $b) => $b['balance_due'] <=> $a['balance_due']);

        return $debtorsList;
    }

    /**
     * Get Invoice-level Overdue Details list
     */
    public function getOverdueInvoicesList(array $filters = [], ?Carbon $asOfDate = null): array
    {
        $asOf = $asOfDate ?: now();

        $query = Invoice::with(['quotation.lead', 'payments'])
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->orderBy('due_date');

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('invoice_no', 'like', "%{$s}%")
                  ->orWhereHas('quotation.lead', function ($l) use ($s) {
                      $l->where('customer_name', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%");
                  });
            });
        }

        $invoices = $query->get();
        $overdueList = [];

        /** @var \App\Models\Invoice $inv */
        foreach ($invoices as $inv) {
            $balance = (float) $inv->balanceDue();
            if ($balance <= 0.01) {
                continue;
            }

            $lead = $inv->quotation?->lead;
            $dueDate = $inv->due_date ? Carbon::parse($inv->due_date) : Carbon::parse($inv->invoice_date)->addDays(15);
            $isOverdue = $asOf->gt($dueDate);
            $daysOverdue = $isOverdue ? $asOf->diffInDays($dueDate) : 0;

            if ($daysOverdue <= 30) {
                $bucketKey = '0_30';
                $bucketLabel = 'Current (0–30d)';
                $badgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-300 dark:border-blue-800';
            } elseif ($daysOverdue <= 60) {
                $bucketKey = '31_60';
                $bucketLabel = '31–60d Overdue';
                $badgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300 dark:border-amber-800';
            } elseif ($daysOverdue <= 90) {
                $bucketKey = '61_90';
                $bucketLabel = '61–90d Overdue';
                $badgeClass = 'bg-orange-100 text-orange-800 dark:bg-orange-950/70 dark:text-orange-300 border-orange-300 dark:border-orange-800';
            } else {
                $bucketKey = '90_plus';
                $bucketLabel = '90+d Critical';
                $badgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-300 dark:border-rose-800 animate-pulse';
            }

            if (!empty($filters['bucket']) && $filters['bucket'] !== $bucketKey) {
                continue;
            }

            $checkoutUrl = route('payment.checkout.invoice', $inv);

            $latestReminder = NotificationLog::where('reference_type', Invoice::class)
                ->where('reference_id', $inv->id)
                ->whereIn('event_type', ['payment_overdue', 'payment_reminder'])
                ->latest('sent_at')
                ->first();

            $overdueList[] = [
                'invoice'            => $inv,
                'invoice_id'         => $inv->id,
                'invoice_no'         => $inv->invoice_no,
                'customer_name'      => $lead?->company_legal_name ?: ($lead?->customer_name ?: 'Client'),
                'customer_phone'     => $lead?->phone ?? 'N/A',
                'customer_email'     => $lead?->email ?? null,
                'invoice_date'       => $inv->invoice_date?->format('d M Y'),
                'due_date'           => $dueDate->format('d M Y'),
                'days_overdue'       => $daysOverdue,
                'is_overdue'         => $isOverdue,
                'total_amount'       => (float) $inv->total,
                'amount_paid'        => (float) $inv->amount_paid,
                'balance_due'        => $balance,
                'checkout_url'       => $checkoutUrl,
                'bucket_label'       => $bucketLabel,
                'bucket_badge_class' => $badgeClass,
                'last_reminder_at'   => $latestReminder?->sent_at?->format('d M Y, h:i A') ?: 'Never Sent',
            ];
        }

        return $overdueList;
    }

    /**
     * Dispatch Payment Reminder / Dunning notice via WhatsApp, SMS, or Email
     */
    public function sendPaymentReminder(Invoice $invoice, string $channel = 'whatsapp'): array
    {
        $invoice->loadMissing(['quotation.lead', 'payments']);
        $lead = $invoice->quotation?->lead;

        if (!$lead) {
            return ['success' => false, 'message' => 'No customer contact linked to this invoice.'];
        }

        $balanceDue = $invoice->balanceDue();
        $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date) : Carbon::parse($invoice->invoice_date)->addDays(15);
        $daysOverdue = now()->gt($dueDate) ? now()->diffInDays($dueDate) : 0;
        $checkoutUrl = route('payment.checkout.invoice', $invoice);

        $params = [
            'customer_name' => $lead->customer_name,
            'invoice_no'    => $invoice->invoice_no,
            'amount_due'    => '₹' . number_format($balanceDue, 2),
            'days_overdue'  => $daysOverdue > 0 ? "{$daysOverdue} days" : 'due today',
            'link'          => $checkoutUrl,
            'due_date'      => $dueDate->format('d M Y'),
        ];

        // Format direct WhatsApp URL
        $whatsappMessage = "Hello {$lead->customer_name},\n\nThis is a gentle payment reminder for Invoice *{$invoice->invoice_no}*.\n\nPending Balance: *₹" . number_format($balanceDue, 2) . "*\nDue Date: {$dueDate->format('d M Y')}" . ($daysOverdue > 0 ? " (Overdue by {$daysOverdue} days)" : "") . "\n\n💳 Pay Instantly Online (UPI / Card / NetBanking):\n{$checkoutUrl}\n\nThank you for choosing CCTV Security Solutions!";
        
        $phoneClean = preg_replace('/[^0-9]/', '', $lead->phone);
        if (strlen($phoneClean) === 10) {
            $phoneClean = '91' . $phoneClean;
        }
        $whatsappUrl = "https://wa.me/{$phoneClean}?text=" . urlencode($whatsappMessage);

        try {
            NotificationLog::create([
                'channel'         => $channel === 'all' ? 'whatsapp' : $channel,
                'event_type'      => 'payment_overdue',
                'recipient_type'  => 'customer',
                'recipient_name'  => $lead->customer_name,
                'recipient_phone' => $lead->phone,
                'recipient_email' => $lead->email,
                'subject'         => "Payment Reminder: Invoice #{$invoice->invoice_no} (Due: ₹" . number_format($balanceDue, 2) . ")",
                'message_body'    => $whatsappMessage,
                'action_url'      => $checkoutUrl,
                'status'          => 'sent',
                'reference_type'  => Invoice::class,
                'reference_id'    => $invoice->id,
                'sent_at'         => now(),
                'created_by'      => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("Payment Reminder NotificationLog error: " . $e->getMessage());
        }

        return [
            'success'      => true,
            'whatsapp_url' => $whatsappUrl,
            'checkout_url' => $checkoutUrl,
            'message'      => "Payment reminder logged for Invoice #{$invoice->invoice_no}.",
        ];
    }

    /**
     * Run Bulk Reminder Sweep across all overdue debtors with smart deduplication
     */
    public function runBulkOverdueReminderSweep(): array
    {
        $invoices = Invoice::with(['quotation.lead'])
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->where('due_date', '<', now()->toDateString())
            ->get();

        $dispatchedCount = 0;
        $skippedCount = 0;

        /** @var \App\Models\Invoice $inv */
        foreach ($invoices as $inv) {
            if ($inv->balanceDue() <= 0.01 || !$inv->quotation?->lead) {
                continue;
            }

            // Deduplication check: sent in last 3 days?
            $recentReminder = NotificationLog::where('reference_type', Invoice::class)
                ->where('reference_id', $inv->id)
                ->where('created_at', '>=', now()->subDays(3))
                ->exists();

            if ($recentReminder) {
                $skippedCount++;
                continue;
            }

            $this->sendPaymentReminder($inv, 'whatsapp');
            $dispatchedCount++;
        }

        return [
            'dispatched_count' => $dispatchedCount,
            'skipped_count'    => $skippedCount,
            'total_scanned'    => $invoices->count(),
        ];
    }

    /**
     * Export Debtors Aging CSV Spreadsheet
     */
    public function exportAgingCsv(): string
    {
        $summary = $this->getDebtorsAgingSummary();
        $debtors = $this->getCustomerDebtorsList();
        $invoices = $this->getOverdueInvoicesList();

        $output = fopen('php://temp', 'r+');

        fputcsv($output, ['--- ACCOUNTS RECEIVABLE & DEBTORS AGING LEDGER ---']);
        fputcsv($output, ['As Of Date', $summary['as_of_date']]);
        fputcsv($output, ['Total Outstanding Receivables (₹)', number_format($summary['total_receivable_balance'], 2, '.', '')]);
        fputcsv($output, ['Total Overdue Balance (₹)', number_format($summary['total_overdue_balance'], 2, '.', '')]);
        fputcsv($output, ['Days Sales Outstanding (DSO)', $summary['dso_days'] . ' Days']);
        fputcsv($output, ['Total Debtor Accounts', $summary['debtors_count']]);
        fputcsv($output, []);

        // Aging Buckets Summary Table
        fputcsv($output, ['--- AGING BUCKET DISTRIBUTION ---']);
        fputcsv($output, ['Aging Category', 'Days Range', 'Invoices Count', 'Outstanding Amount (₹)']);
        foreach ($summary['buckets'] as $bucket) {
            fputcsv($output, [
                $bucket['label'],
                $bucket['days_range'],
                $bucket['count'],
                number_format($bucket['amount'], 2, '.', ''),
            ]);
        }
        fputcsv($output, []);

        // Customer Debtors Table
        fputcsv($output, ['--- CUSTOMER DEBTORS STATEMENT ---']);
        fputcsv($output, [
            'Customer / Legal Name',
            'Phone',
            'Email',
            'GSTIN',
            'Unpaid Invoices',
            'Total Invoiced (₹)',
            'Total Paid (₹)',
            'Outstanding Balance (₹)',
            'Oldest Due Date',
            'Highest Overdue Days',
            'Aging Tier',
            'Last Reminder Sent',
        ]);

        foreach ($debtors as $row) {
            fputcsv($output, [
                $row['customer_name'],
                $row['phone'],
                $row['email'] ?: 'N/A',
                $row['gstin'],
                $row['unpaid_invoice_count'],
                number_format($row['total_invoiced'], 2, '.', ''),
                number_format($row['total_paid'], 2, '.', ''),
                number_format($row['balance_due'], 2, '.', ''),
                $row['oldest_due_date'],
                $row['highest_days_overdue'],
                $row['bucket_label'],
                $row['last_reminder_at'],
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }
}

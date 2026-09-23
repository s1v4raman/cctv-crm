<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Lead;
use App\Services\AccountsReceivableService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AccountsReceivableController extends Controller
{
    protected AccountsReceivableService $arService;

    public function __construct(AccountsReceivableService $arService)
    {
        $this->arService = $arService;
    }

    /**
     * Accounts Receivable & Debtors Aging Dashboard
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'bucket' => $request->input('bucket'),
        ];

        $viewTab = $request->input('tab', 'customers'); // customers, invoices

        $summary = $this->arService->getDebtorsAgingSummary();
        $debtors = $this->arService->getCustomerDebtorsList($filters);
        $overdueInvoices = $this->arService->getOverdueInvoicesList($filters);

        return view('finance.receivables.index', compact(
            'summary',
            'debtors',
            'overdueInvoices',
            'filters',
            'viewTab'
        ));
    }

    /**
     * Customer Statement of Account / Debtor Ledger
     */
    public function customerLedger(Lead $lead): View
    {
        $lead->load([
            'quotations.invoices.payments.recordedBy',
            'quotations.items',
        ]);

        $invoices = $lead->quotations->flatMap->invoices->sortBy('invoice_date');
        $totalInvoiced = (float) $invoices->sum('total');
        $totalPaid = (float) $invoices->sum('amount_paid');
        $totalOutstanding = max(0.0, $totalInvoiced - $totalPaid);

        $transactions = [];
        $runningBalance = 0.0;

        foreach ($invoices as $inv) {
            $runningBalance += (float) $inv->total;
            $transactions[] = [
                'date'        => $inv->invoice_date ? Carbon::parse($inv->invoice_date) : $inv->created_at,
                'type'        => 'invoice',
                'invoice_id'  => $inv->id,
                'reference'   => $inv->invoice_no,
                'description' => "Invoice #{$inv->invoice_no} (" . ucfirst($inv->status ?? 'unpaid') . ")",
                'debit'       => (float) $inv->total,
                'credit'      => 0.0,
                'balance'     => $runningBalance,
            ];

            foreach ($inv->payments as $pmt) {
                $runningBalance -= (float) $pmt->amount;
                $transactions[] = [
                    'date'        => $pmt->paid_on ? Carbon::parse($pmt->paid_on) : $pmt->created_at,
                    'type'        => 'payment',
                    'reference'   => $pmt->receipt_no ?: ($pmt->reference_no ?: "REC-{$pmt->id}"),
                    'description' => "Payment via " . ucfirst($pmt->method ?? 'Payment') . ($pmt->reference_no ? " (Ref: {$pmt->reference_no})" : ''),
                    'debit'       => 0.0,
                    'credit'      => (float) $pmt->amount,
                    'balance'     => $runningBalance,
                ];
            }
        }

        $ledger = [
            'transactions'        => $transactions,
            'total_billed'        => $totalInvoiced,
            'total_paid'          => $totalPaid,
            'outstanding_balance' => $totalOutstanding,
        ];

        return view('finance.receivables.customer_ledger', compact(
            'lead',
            'invoices',
            'ledger',
            'totalInvoiced',
            'totalPaid',
            'totalOutstanding'
        ));
    }

    /**
     * Send Multi-Channel Payment Reminder to Client
     */
    public function sendReminder(Request $request, Invoice $invoice)
    {
        $channel = $request->input('channel', 'whatsapp');
        $result = $this->arService->sendPaymentReminder($invoice, $channel);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($result);
        }

        if (!empty($result['whatsapp_url']) && $channel === 'whatsapp') {
            return redirect()->away($result['whatsapp_url']);
        }

        return back()->with('status', "✅ Payment reminder sent to customer for Invoice #{$invoice->invoice_no}.");
    }

    /**
     * Trigger 1-Click Bulk Overdue Dunning Sweep
     */
    public function runBulkSweep(Request $request): RedirectResponse
    {
        $stats = $this->arService->runBulkOverdueReminderSweep();

        return back()->with('status', "🚀 Bulk Reminder Sweep completed: {$stats['dispatched_count']} reminder(s) dispatched ({$stats['skipped_count']} skipped due to anti-spam deduplication).");
    }

    /**
     * Export Debtors Aging Analysis Report PDF
     */
    public function exportAgingPdf(Request $request)
    {
        $summary = $this->arService->getDebtorsAgingSummary();
        $debtors = $this->arService->getCustomerDebtorsList();
        $overdueInvoices = $this->arService->getOverdueInvoicesList();
        $generatedAt = now()->format('d M Y, h:i A');

        $pdf = Pdf::loadView('finance.receivables.aging_pdf', compact('summary', 'debtors', 'overdueInvoices', 'generatedAt'));
        $filename = "Debtors_Aging_Analysis_Report_" . now()->format('Y_m_d') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Export Debtors Ledger CSV
     */
    public function exportAgingCsv(Request $request): Response
    {
        $csv = $this->arService->exportAgingCsv();
        $filename = "Debtors_Aging_Ledger_" . now()->format('Y_m_d') . ".csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Download Customer Statement of Account PDF
     */
    public function customerStatementPdf(Lead $lead)
    {
        $lead->load([
            'quotations.invoices.payments.recordedBy',
            'quotations.items',
        ]);

        $invoices = $lead->quotations->flatMap->invoices->sortBy('invoice_date');
        $totalInvoiced = (float) $invoices->sum('total');
        $totalPaid = (float) $invoices->sum('amount_paid');
        $totalOutstanding = max(0.0, $totalInvoiced - $totalPaid);

        $transactions = [];
        $runningBalance = 0.0;

        foreach ($invoices as $inv) {
            $runningBalance += (float) $inv->total;
            $transactions[] = [
                'date'        => $inv->invoice_date ? Carbon::parse($inv->invoice_date) : $inv->created_at,
                'type'        => 'invoice',
                'invoice_id'  => $inv->id,
                'reference'   => $inv->invoice_no,
                'description' => "Invoice #{$inv->invoice_no} (" . ucfirst($inv->status ?? 'unpaid') . ")",
                'debit'       => (float) $inv->total,
                'credit'      => 0.0,
                'balance'     => $runningBalance,
            ];

            foreach ($inv->payments as $pmt) {
                $runningBalance -= (float) $pmt->amount;
                $transactions[] = [
                    'date'        => $pmt->paid_on ? Carbon::parse($pmt->paid_on) : $pmt->created_at,
                    'type'        => 'payment',
                    'reference'   => $pmt->receipt_no ?: ($pmt->reference_no ?: "REC-{$pmt->id}"),
                    'description' => "Payment via " . ucfirst($pmt->method ?? 'Payment') . ($pmt->reference_no ? " (Ref: {$pmt->reference_no})" : ''),
                    'debit'       => 0.0,
                    'credit'      => (float) $pmt->amount,
                    'balance'     => $runningBalance,
                ];
            }
        }

        $ledger = [
            'transactions'        => $transactions,
            'total_billed'        => $totalInvoiced,
            'total_paid'          => $totalPaid,
            'outstanding_balance' => $totalOutstanding,
        ];
        $generatedAt = now()->format('d M Y, h:i A');

        $pdf = Pdf::loadView('finance.receivables.customer_statement_pdf', compact(
            'lead',
            'invoices',
            'ledger',
            'totalInvoiced',
            'totalPaid',
            'totalOutstanding',
            'generatedAt'
        ));

        $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $lead->customer_name ?: 'Customer');
        $filename = "Statement_Of_Account_{$cleanName}.pdf";

        return $pdf->download($filename);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\DailyCashReconciliation;
use App\Models\InstallationJob;
use App\Models\Invoice;
use App\Models\PettyCashAccount;
use App\Models\PettyCashTransaction;
use App\Models\ServiceTicket;
use App\Models\User;
use App\Services\PettyCashService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PettyCashController extends Controller
{
    protected PettyCashService $cashService;

    public function __construct(PettyCashService $cashService)
    {
        $this->cashService = $cashService;
    }

    /**
     * Petty Cash & Field Float Hub
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'       => $request->input('search'),
            'account_type' => $request->input('account_type'),
        ];

        $summary = $this->cashService->getPettyCashSummary();
        $accounts = $this->cashService->getAccountsList($filters);
        
        $technicians = User::whereIn('role', ['technician', 'staff'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $recentTransactions = PettyCashTransaction::with(['account', 'destinationAccount', 'user', 'job', 'ticket', 'invoice'])
            ->latest('transaction_date')
            ->latest('id')
            ->limit(25)
            ->get();

        $recentReconciliations = DailyCashReconciliation::with(['account', 'custodian', 'verifiedBy'])
            ->latest('reconciliation_date')
            ->limit(10)
            ->get();

        $activeJobs = InstallationJob::with(['quotation.lead', 'lead'])->whereIn('status', ['scheduled', 'in_progress'])->get();
        $activeTickets = ServiceTicket::with('lead')->whereIn('status', ['open', 'assigned', 'in_progress'])->get();
        $pendingInvoices = Invoice::with('quotation.lead')->whereIn('status', ['issued', 'partially_paid'])->get();

        return view('finance.petty_cash.index', compact(
            'summary',
            'accounts',
            'technicians',
            'recentTransactions',
            'recentReconciliations',
            'activeJobs',
            'activeTickets',
            'pendingInvoices',
            'filters'
        ));
    }

    /**
     * Issue Cash Float Advance from Vault to Technician
     */
    public function issueAdvance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'custodian_id'     => ['required', 'exists:users,id'],
            'amount'           => ['required', 'numeric', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $tx = $this->cashService->recordFloatAdvance($validated);
            $msg = "✅ Float advance of ₹" . number_format($tx->amount, 2) . " disbursed successfully ({$tx->voucher_no}).";
            return back()->with('status', $msg)->with('success', $msg);
        } catch (\Exception $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Record On-Site Customer Cash Collection
     */
    public function recordCollection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'custodian_id'      => ['nullable', 'exists:users,id'],
            'amount'            => ['required', 'numeric', 'min:1'],
            'transaction_date'  => ['required', 'date'],
            'customer_name'     => ['nullable', 'string', 'max:255'],
            'invoice_id'        => ['nullable', 'exists:invoices,id'],
            'job_id'            => ['nullable', 'exists:installation_jobs,id'],
            'ticket_id'         => ['nullable', 'exists:service_tickets,id'],
            'receipt_reference' => ['nullable', 'string', 'max:255'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $tx = $this->cashService->recordFieldCollection($validated);
            $msg = "✅ Cash collection of ₹" . number_format($tx->amount, 2) . " recorded successfully ({$tx->voucher_no}).";
            return back()->with('status', $msg)->with('success', $msg);
        } catch (\Exception $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Record Localized Field Petty Expense / Site Purchase
     */
    public function recordExpense(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'petty_cash_account_id' => ['required', 'exists:petty_cash_accounts,id'],
            'amount'                => ['required', 'numeric', 'min:0.50'],
            'transaction_date'      => ['required', 'date'],
            'category'              => ['required', 'string', 'in:materials,hardware_conduits,travel_fuel,food_refreshment,tolls_parking,office_supplies,other'],
            'vendor_payee_name'     => ['nullable', 'string', 'max:255'],
            'bill_receipt_no'       => ['nullable', 'string', 'max:255'],
            'job_id'                => ['nullable', 'exists:installation_jobs,id'],
            'ticket_id'             => ['nullable', 'exists:service_tickets,id'],
            'receipt_photo'         => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes'                 => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $tx = $this->cashService->recordDirectExpense($validated);
            $msg = "✅ Expense of ₹" . number_format($tx->amount, 2) . " logged successfully ({$tx->voucher_no}).";
            return back()->with('status', $msg)->with('success', $msg);
        } catch (\Exception $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Record Cash Handover back to Office Vault or Bank Deposit
     */
    public function recordHandover(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_account_id' => ['required', 'exists:petty_cash_accounts,id'],
            'destination_type'  => ['required', 'in:main_vault,bank_deposit'],
            'amount'            => ['required', 'numeric', 'min:1'],
            'transaction_date'  => ['required', 'date'],
            'bank_name'         => ['nullable', 'string', 'max:255'],
            'bank_ack_no'       => ['nullable', 'string', 'max:255'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $tx = $this->cashService->recordCashHandover($validated);
            $msg = "✅ Cash handover of ₹" . number_format($tx->amount, 2) . " processed successfully ({$tx->voucher_no}).";
            return back()->with('status', $msg)->with('success', $msg);
        } catch (\Exception $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show Daily Cash Reconciliation Screen with Interactive Denomination Counter
     */
    public function showReconcile(Request $request, PettyCashAccount $account): View
    {
        $date = $request->filled('date') ? \Carbon\Carbon::parse($request->date) : \Carbon\Carbon::today();
        $summary = $this->cashService->getPettyCashSummary($date);
        
        $existingReconciliation = DailyCashReconciliation::where('petty_cash_account_id', $account->id)
            ->whereDate('reconciliation_date', $date->toDateString())
            ->first();

        return view('finance.petty_cash.reconcile', compact(
            'account',
            'date',
            'summary',
            'existingReconciliation'
        ));
    }

    /**
     * Submit Physical Denomination Breakdown & Reconcile Cash
     */
    public function storeReconcile(Request $request, PettyCashAccount $account): RedirectResponse
    {
        $validated = $request->validate([
            'reconciliation_date'   => ['required', 'date'],
            'denominations'         => ['required', 'array'],
            'reconciliation_notes'  => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['petty_cash_account_id'] = $account->id;

        $rec = $this->cashService->recordDailyReconciliation($validated);

        $statusMsg = match($rec->variance_status) {
            'matched'  => "🟢 Perfect Match! Physical cash exactly equals system expected balance (₹" . number_format($rec->physical_counted_balance, 2) . ").",
            'shortage' => "🔴 Cash Shortage Detected: Physical count is ₹" . number_format(abs($rec->variance_amount), 2) . " less than system expected balance.",
            'excess'   => "🟡 Cash Excess Detected: Physical count is ₹" . number_format($rec->variance_amount, 2) . " more than system expected balance.",
        };

        return redirect()->route('finance.petty_cash.index')
            ->with('status', "Reconciliation {$rec->reconciliation_no} saved. {$statusMsg}")
            ->with('success', "Reconciliation {$rec->reconciliation_no} saved. {$statusMsg}");
    }

    /**
     * Account Statement & Running Balance Ledger
     */
    public function accountLedger(Request $request, PettyCashAccount $account): View
    {
        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date'   => $request->input('to_date'),
        ];

        $ledger = $this->cashService->getAccountLedger($account, $filters);

        return view('finance.petty_cash.ledger', compact('account', 'ledger', 'filters'));
    }

    /**
     * Download Printable Cash Voucher PDF
     */
    public function voucherPdf(PettyCashTransaction $transaction)
    {
        $transaction->load(['account', 'destinationAccount', 'user', 'approvedBy', 'job', 'ticket', 'invoice']);
        $generatedAt = now()->format('d M Y, h:i A');

        $pdf = Pdf::loadView('finance.petty_cash.voucher_pdf', compact('transaction', 'generatedAt'));
        $filename = "Voucher_{$transaction->voucher_no}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Download Cashbook Audit Summary PDF
     */
    public function exportPdf(Request $request)
    {
        $summary = $this->cashService->getPettyCashSummary();
        $accounts = $this->cashService->getAccountsList();
        $transactions = PettyCashTransaction::with(['account', 'destinationAccount', 'user'])
            ->latest('transaction_date')
            ->limit(50)
            ->get();
        $generatedAt = now()->format('d M Y, h:i A');

        $pdf = Pdf::loadView('finance.petty_cash.cashbook_pdf', compact('summary', 'accounts', 'transactions', 'generatedAt'));
        $filename = "Petty_Cashbook_Report_" . now()->format('Y_m_d') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Download Cashbook CSV
     */
    public function exportCsv(Request $request): Response
    {
        $csv = $this->cashService->exportCashbookCsv();
        $filename = "Petty_Cashbook_" . now()->format('Y_m_d') . ".csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\AccountsPayableService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AccountsPayableController extends Controller
{
    protected AccountsPayableService $apService;

    public function __construct(AccountsPayableService $apService)
    {
        $this->apService = $apService;
    }

    /**
     * Vendor Accounts Payable & 3-Way Matching Dashboard
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'       => $request->input('search'),
            'bucket'       => $request->input('bucket'),
            'match_status' => $request->input('match_status'),
        ];

        $summary = $this->apService->getAccountsPayableSummary();
        $suppliers = $this->apService->getSupplierPayablesList($filters);
        $threeWayAudit = $this->apService->getThreeWayMatchingAuditList($filters);
        $disbursements = $this->apService->getPendingDisbursementsList($filters);
        $allSuppliers = Supplier::orderBy('company_name')->get(['id', 'company_name', 'name']);

        return view('finance.payables.index', compact(
            'summary',
            'suppliers',
            'threeWayAudit',
            'disbursements',
            'allSuppliers',
            'filters'
        ));
    }

    /**
     * Supplier Statement of Account & Transaction Ledger
     */
    public function supplierLedger(Supplier $supplier): View
    {
        $ledger = $this->apService->getSupplierStatementLedger($supplier);

        return view('finance.payables.supplier_ledger', compact(
            'supplier',
            'ledger'
        ));
    }

    /**
     * Record Vendor Payment Disbursement
     */
    public function recordPayment(Request $request): RedirectResponse
    {
        $po = null;
        if ($request->filled('purchase_order_id')) {
            $po = PurchaseOrder::find($request->purchase_order_id);
            if ($po && !$request->filled('supplier_id')) {
                $request->merge(['supplier_id' => $po->supplier_id]);
            }
        }

        $validated = $request->validate([
            'supplier_id'           => ['required', 'exists:suppliers,id'],
            'purchase_order_id'     => ['nullable', 'exists:purchase_orders,id'],
            'amount'                => ['required', 'numeric', 'min:0.01'],
            'payment_date'          => ['required', 'date'],
            'payment_method'        => ['required', 'in:neft_rtgs,neft,rtgs,cheque,upi,bank_transfer,cash'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
        ]);

        if ($po) {
            $balance = (float) $po->balanceDue();
            if ((float) $validated['amount'] > ($balance + 0.01)) {
                return back()->withErrors([
                    'amount' => "Payment amount (₹" . number_format($validated['amount'], 2) . ") exceeds the outstanding PO balance of ₹" . number_format($balance, 2) . "."
                ])->withInput();
            }
        }

        $payment = $this->apService->recordVendorPayment($validated);
        $msg = "✅ Vendor disbursement of ₹" . number_format($payment->amount, 2) . " recorded successfully ({$payment->payment_reference}).";

        return back()->with('status', $msg)->with('success', $msg);
    }

    /**
     * Export Vendor Accounts Payable Aging & 3-Way Match PDF
     */
    public function exportApPdf(Request $request)
    {
        $summary = $this->apService->getAccountsPayableSummary();
        $suppliers = $this->apService->getSupplierPayablesList();
        $threeWayAudit = $this->apService->getThreeWayMatchingAuditList();
        $generatedAt = now()->format('d M Y, h:i A');

        $pdf = Pdf::loadView('finance.payables.ap_pdf', compact('summary', 'suppliers', 'threeWayAudit', 'generatedAt'));
        $filename = "Vendor_Accounts_Payable_Report_" . now()->format('Y_m_d') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Export Vendor Accounts Payable Aging CSV
     */
    public function exportApCsv(Request $request): Response
    {
        $csv = $this->apService->exportApAgingCsv();
        $filename = "Vendor_Accounts_Payable_Ledger_" . now()->format('Y_m_d') . ".csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export 3-Way Matching Procurement Audit CSV
     */
    public function exportThreeWayMatchCsv(Request $request): Response
    {
        $csv = $this->apService->exportThreeWayMatchCsv();
        $filename = "Three_Way_Matching_Audit_" . now()->format('Y_m_d') . ".csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Download Supplier Statement of Account PDF
     */
    public function supplierStatementPdf(Supplier $supplier)
    {
        $ledger = $this->apService->getSupplierStatementLedger($supplier);
        $generatedAt = now()->format('d M Y, h:i A');

        $pdf = Pdf::loadView('finance.payables.supplier_statement_pdf', compact(
            'supplier',
            'ledger',
            'generatedAt'
        ));

        $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $supplier->company_name ?: $supplier->name);
        $filename = "Supplier_Statement_{$cleanName}.pdf";

        return $pdf->download($filename);
    }
}

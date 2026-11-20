<?php

namespace App\Http\Controllers;

use App\Helpers\GstHelper;
use App\Models\GatewaySetting;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\VendorBill;
use App\Models\VendorBillItem;
use App\Models\VendorPayment;
use App\Services\BillPaymentService;
use App\Services\DocumentSequenceService;
use App\Services\ThreeWayMatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VendorBillController extends Controller
{
    public function __construct(
        protected ThreeWayMatchService $matchService,
        protected BillPaymentService $paymentService
    ) {}

    /**
     * Display listing of vendor bills.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', VendorBill::class);

        $query = VendorBill::with(['supplier', 'purchaseOrder', 'approvedBy', 'createdBy'])
            ->latest('invoice_date')
            ->latest('id');

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('bill_number', 'ilike', "%{$term}%")
                  ->orWhere('vendor_invoice_no', 'ilike', "%{$term}%")
                  ->orWhereHas('supplier', function ($supQ) use ($term) {
                      $supQ->where('name', 'ilike', "%{$term}%")
                           ->orWhere('company_name', 'ilike', "%{$term}%");
                  })
                  ->orWhereHas('purchaseOrder', function ($poQ) use ($term) {
                      $poQ->where('po_number', 'ilike', "%{$term}%");
                  });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('match_status')) {
            $query->where('match_status', $request->match_status);
        }

        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        $bills = $query->paginate(20)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('vendor_bills.index', compact('bills', 'suppliers'));
    }

    /**
     * Display vendor bill detail with 3-way match audit and payment ledger.
     */
    public function show(VendorBill $vendorBill): View
    {
        $this->authorize('view', $vendorBill);

        $vendorBill->load([
            'supplier',
            'purchaseOrder.items',
            'purchaseOrder.goodsReceipts.items',
            'items.product',
            'items.purchaseOrderItem',
            'approvedBy',
            'createdBy',
            'paymentAllocations.payment',
            'payments',
        ]);

        return view('vendor_bills.show', compact('vendorBill'));
    }

    /**
     * Show form to record a new vendor bill.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', VendorBill::class);

        $po = null;
        $supplier = null;
        if ($request->filled('purchase_order_id')) {
            $po = PurchaseOrder::with(['supplier', 'items.product', 'goodsReceipts.items'])->find($request->purchase_order_id);
            if ($po) {
                $supplier = $po->supplier;
            }
        }

        $suppliers = Supplier::orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::whereIn('status', ['sent', 'partially_received', 'received'])
            ->latest()
            ->get();

        return view('vendor_bills.create', compact('suppliers', 'purchaseOrders', 'po', 'supplier'));
    }

    /**
     * Store a vendor bill and run 3-way match verification.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', VendorBill::class);

        $validated = $request->validate([
            'supplier_id'       => ['required', 'exists:suppliers,id'],
            'vendor_invoice_no' => ['required', 'string', 'max:100'],
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'invoice_date'      => ['required', 'date'],
            'due_date'          => ['required', 'date', 'after_or_equal:invoice_date'],
            'invoice_file'      => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB
            'items'             => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.product_id'=> ['nullable', 'exists:products,id'],
            'items.*.purchase_order_item_id' => ['nullable', 'exists:purchase_order_items,id'],
            'items.*.hsn_code'  => ['nullable', 'string', 'max:30'],
            'items.*.quantity'  => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate'  => ['nullable', 'numeric', 'min:0'],
        ], [
            'vendor_invoice_no.required' => 'Supplier tax invoice number is required.',
        ]);

        // Guard against duplicate vendor invoice numbers per supplier
        $existing = VendorBill::where('supplier_id', $validated['supplier_id'])
            ->where('vendor_invoice_no', trim($validated['vendor_invoice_no']))
            ->first();

        if ($existing) {
            return back()->withInput()->withErrors([
                'vendor_invoice_no' => "Invoice #{$validated['vendor_invoice_no']} has already been recorded for this supplier under Bill #{$existing->bill_number} (Duplicate Invoice Blocked).",
            ]);
        }

        // Store invoice file outside public folder
        $filePath = null;
        if ($request->hasFile('invoice_file')) {
            $filePath = $request->file('invoice_file')->store('vendor_invoices', 'local');
        }

        $supplier = Supplier::findOrFail($validated['supplier_id']);
        $settings = GatewaySetting::first();
        $companyStateCode = $settings?->company_state_code ?: '33';
        $vendorStateCode = $supplier->gst_number ? GstHelper::extractStateCode($supplier->gst_number) : null;

        $bill = DB::transaction(function () use ($validated, $filePath, $vendorStateCode, $companyStateCode) {
            $billNumber = DocumentSequenceService::nextVendorBillNumber($validated['invoice_date']);

            $bill = VendorBill::create([
                'bill_number'        => $billNumber,
                'vendor_invoice_no'  => trim($validated['vendor_invoice_no']),
                'supplier_id'        => $validated['supplier_id'],
                'purchase_order_id'  => $validated['purchase_order_id'] ?? null,
                'invoice_date'       => $validated['invoice_date'],
                'due_date'           => $validated['due_date'],
                'invoice_file_path'  => $filePath,
                'status'             => VendorBill::STATUS_DRAFT,
                'match_status'       => VendorBill::MATCH_NOT_CHECKED,
                'created_by'         => auth()->id(),
            ]);

            foreach ($validated['items'] as $itemData) {
                $qty = (float) $itemData['quantity'];
                $unitCost = (float) $itemData['unit_cost'];
                $lineTotal = round($qty * $unitCost, 2);
                $taxRate = isset($itemData['tax_rate']) && $itemData['tax_rate'] !== '' ? (float) $itemData['tax_rate'] : 18.0;

                $taxSplit = GstHelper::calculateTaxSplit($lineTotal, $taxRate, $vendorStateCode, $companyStateCode);

                VendorBillItem::create([
                    'vendor_bill_id'        => $bill->id,
                    'purchase_order_item_id'=> $itemData['purchase_order_item_id'] ?? null,
                    'product_id'            => $itemData['product_id'] ?? null,
                    'item_name'             => $itemData['item_name'],
                    'hsn_code'              => $itemData['hsn_code'] ?? '8525',
                    'quantity'              => $qty,
                    'unit_cost'             => $unitCost,
                    'total_cost'            => $lineTotal,
                    'tax_rate'              => $taxRate,
                    'cgst_rate'             => $taxSplit['cgst_rate'],
                    'cgst_amount'           => $taxSplit['cgst_amount'],
                    'sgst_rate'             => $taxSplit['sgst_rate'],
                    'sgst_amount'           => $taxSplit['sgst_amount'],
                    'igst_rate'             => $taxSplit['igst_rate'],
                    'igst_amount'           => $taxSplit['igst_amount'],
                ]);
            }

            $bill->recalculateTotals();

            return $bill;
        });

        // Run automated 3-way match
        $matchResult = $this->matchService->verifyBill($bill);

        if (!$matchResult['matched']) {
            return redirect()->route('vendor-bills.show', $bill)
                ->with('warning', "Vendor Bill #{$bill->bill_number} recorded but placed ON HOLD due to 3-way match discrepancy. Review discrepancies below.");
        }

        return redirect()->route('vendor-bills.show', $bill)
            ->with('status', "Vendor Bill #{$bill->bill_number} created and approved via 3-way match verification.");
    }

    /**
     * Admin manual override to approve an on-hold mismatched bill.
     */
    public function approve(Request $request, VendorBill $vendorBill): RedirectResponse
    {
        $this->authorize('approve', $vendorBill);

        $validated = $request->validate([
            'approval_reason' => ['required', 'string', 'min:5'],
        ], [
            'approval_reason.required' => 'A written reason is strictly required to override and approve a mismatched bill.',
        ]);

        $vendorBill->update([
            'status'          => VendorBill::STATUS_APPROVED,
            'approved_by'     => auth()->id(),
            'approved_at'     => now(),
            'approval_reason' => $validated['approval_reason'],
        ]);

        return back()->with('status', "Bill #{$vendorBill->bill_number} manually approved by Administrator.");
    }

    /**
     * Re-run 3-way matching check (e.g. after newly inwarded goods receipts).
     */
    public function reverifyMatch(VendorBill $vendorBill): RedirectResponse
    {
        $this->authorize('update', $vendorBill);

        $result = $this->matchService->verifyBill($vendorBill);

        if ($result['matched']) {
            return back()->with('status', '3-Way Match Verified: Billed items match PO and Goods Receipts.');
        }

        return back()->with('warning', 'Discrepancies remain between Bill, Goods Receipts, and PO.');
    }

    /**
     * Securely download or view invoice file stored outside public directory.
     */
    public function downloadInvoiceFile(VendorBill $vendorBill): BinaryFileResponse|StreamedResponse
    {
        $this->authorize('downloadInvoiceFile', $vendorBill);

        if (!$vendorBill->invoice_file_path || !Storage::disk('local')->exists($vendorBill->invoice_file_path)) {
            abort(404, 'Invoice attachment file not found.');
        }

        return Storage::disk('local')->response($vendorBill->invoice_file_path);
    }

    /**
     * Admin disburse payment against this bill or split payment.
     */
    public function storePayment(Request $request, VendorBill $vendorBill): RedirectResponse
    {
        $this->authorize('pay', $vendorBill);

        $validated = $request->validate([
            'amount'                => ['required', 'numeric', 'min:0.01'],
            'payment_date'          => ['required', 'date'],
            'payment_method'        => ['required', 'string'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'notes'                 => ['nullable', 'string'],
        ]);

        try {
            $payment = $this->paymentService->recordPayment([
                'vendor_bill_id'        => $vendorBill->id,
                'supplier_id'           => $vendorBill->supplier_id,
                'amount'                => (float) $validated['amount'],
                'payment_date'          => $validated['payment_date'],
                'payment_method'        => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'notes'                 => $validated['notes'] ?? null,
                'kind'                  => VendorPayment::KIND_AGAINST_BILL,
            ], auth()->id());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        }

        return back()->with('status', "Payment #{$payment->payment_reference} of ₹" . number_format($payment->amount, 2) . " disbursed against Bill #{$vendorBill->bill_number}.");
    }
}

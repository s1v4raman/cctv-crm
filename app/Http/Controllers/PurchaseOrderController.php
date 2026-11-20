<?php

namespace App\Http\Controllers;

use App\Helpers\GstHelper;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Models\GatewaySetting;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\DocumentSequenceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use InvalidArgumentException;

class PurchaseOrderController extends Controller
{
    use \App\Http\Controllers\Concerns\ListsRecords;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $search = trim((string) $request->input('search', ''));
        $supplierId = $request->input('supplier_id');
        $status = $request->input('status');

        $query = PurchaseOrder::with(['supplier', 'items', 'createdBy']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($iq) use ($search) {
                        $iq->where('item_name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($status) {
            $query->where('status', $status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        $this->applyListSort($query, $request, [
            'date_desc'  => ['order_date', 'desc'],
            'date_asc'   => ['order_date', 'asc'],
            'total_desc' => ['total', 'desc'],
            'po_number'  => ['po_number', 'asc'],
        ]);
        $purchaseOrders = $query->paginate($this->listPerPage($request))->withQueryString();

        // Metrics
        $totalOrders = PurchaseOrder::count();
        $confirmedStatuses = ['approved', 'sent', 'partially_received', 'received', 'closed', 'ordered'];
        $totalSpend = (float) PurchaseOrder::whereIn('status', $confirmedStatuses)->sum('total');
        $pendingDeliveryCount = PurchaseOrder::whereIn('status', ['approved', 'sent', 'partially_received', 'ordered'])->count();
        $unpaidBalance = (float) PurchaseOrder::whereIn('status', ['partially_received', 'received', 'closed'])->sum(DB::raw('total - amount_paid'));

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'company_name']);

        return view('purchase_orders.index', compact(
            'purchaseOrders',
            'totalOrders',
            'totalSpend',
            'pendingDeliveryCount',
            'unpaidBalance',
            'suppliers'
        ));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', PurchaseOrder::class);

        $selectedSupplierId = $request->input('supplier_id');
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('purchase_orders.create', compact('suppliers', 'products', 'selectedSupplierId'));
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $this->authorize('create', PurchaseOrder::class);

        $validated = $request->validated();

        $po = DB::transaction(function () use ($validated) {
            $supplier = Supplier::findOrFail($validated['supplier_id']);
            $vendorStateCode = $supplier->gst_number ? GstHelper::extractStateCode($supplier->gst_number) : null;
            $companyStateCode = GstHelper::getCompanyStateCode();

            $subtotal = 0.0;
            $totalTax = 0.0;
            $cgstTotal = 0.0;
            $sgstTotal = 0.0;
            $igstTotal = 0.0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['quantity_ordered'];
                $cost = (float) $item['unit_cost'];
                $lineSubtotal = round($qty * $cost, 2);
                $subtotal += $lineSubtotal;

                $taxRate = isset($item['tax_rate']) && $item['tax_rate'] !== null
                    ? (float) $item['tax_rate']
                    : (float) ($validated['tax_percent'] ?? 18.0);

                // If line item is linked to product and tax_rate wasn't explicitly overridden, use product tax_rate
                if (!empty($item['product_id']) && (!isset($item['tax_rate']) || $item['tax_rate'] === null)) {
                    $prod = Product::find($item['product_id']);
                    if ($prod && isset($prod->tax_rate) && $prod->tax_rate > 0) {
                        $taxRate = (float) $prod->tax_rate;
                    }
                }

                $taxSplit = GstHelper::calculateTaxSplit($lineSubtotal, $taxRate, $vendorStateCode, $companyStateCode);

                $totalTax += $taxSplit['total_tax'];
                $cgstTotal += $taxSplit['cgst_amount'];
                $sgstTotal += $taxSplit['sgst_amount'];
                $igstTotal += $taxSplit['igst_amount'];

                $itemsData[] = [
                    'product_id'        => $item['product_id'] ?? null,
                    'item_name'         => $item['item_name'],
                    'sku'               => $item['sku'] ?? null,
                    'hsn_code'          => $item['hsn_code'] ?? null,
                    'unit_cost'         => $cost,
                    'quantity_ordered'  => $qty,
                    'quantity_received' => 0,
                    'total_cost'        => $lineSubtotal,
                    'tax_rate'          => $taxRate,
                    'cgst_rate'         => $taxSplit['cgst_rate'],
                    'cgst_amount'       => $taxSplit['cgst_amount'],
                    'sgst_rate'         => $taxSplit['sgst_rate'],
                    'sgst_amount'       => $taxSplit['sgst_amount'],
                    'igst_rate'         => $taxSplit['igst_rate'],
                    'igst_amount'       => $taxSplit['igst_amount'],
                ];
            }

            $shipping = (float) ($validated['shipping_cost'] ?? 0.0);
            $total = round($subtotal + $totalTax + $shipping, 2);

            // Sequential PO number generation using document_sequences table with lockForUpdate
            $poNumber = DocumentSequenceService::nextPurchaseOrderNumber($validated['order_date'] ?? null);

            // Determine initial status:
            // All new POs start as draft by default.
            // If created by admin, check optional setting po_auto_approve_limit:
            // POs at or below the limit created by admin are auto-approved.
            $initialStatus = PurchaseOrder::STATUS_DRAFT;
            $approvedBy = null;
            $approvedAt = null;

            $settings = GatewaySetting::getSettings();
            $autoApproveLimit = $settings?->po_auto_approve_limit;

            if (auth()->user()?->isAdmin()) {
                if (!empty($validated['status'])) {
                    $initialStatus = $validated['status'];
                    if (in_array($initialStatus, ['approved', 'ordered', 'sent'], true)) {
                        $approvedBy = auth()->id();
                        $approvedAt = now();
                    }
                } elseif ($autoApproveLimit !== null && $total <= (float) $autoApproveLimit) {
                    $initialStatus = PurchaseOrder::STATUS_APPROVED;
                    $approvedBy = auth()->id();
                    $approvedAt = now();
                }
            }

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id'            => $validated['supplier_id'],
                'po_number'              => $poNumber,
                'order_date'             => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'status'                 => $initialStatus,
                'approved_by'            => $approvedBy,
                'approved_at'            => $approvedAt,
                'subtotal'               => $subtotal,
                'tax_percent'            => (float) ($validated['tax_percent'] ?? 18.0),
                'tax_amount'             => $totalTax,
                'cgst_amount'            => $cgstTotal,
                'sgst_amount'            => $sgstTotal,
                'igst_amount'            => $igstTotal,
                'shipping_cost'          => $shipping,
                'total'                  => $total,
                'payment_status'         => 'unpaid',
                'amount_paid'            => 0.00,
                'notes'                  => $validated['notes'] ?? null,
                'created_by'             => auth()->id(),
            ]);

            foreach ($itemsData as $item) {
                $purchaseOrder->items()->create($item);
            }

            return $purchaseOrder;
        });

        return redirect()->route('purchase-orders.show', $po)->with(
            'status',
            "Purchase Order {$po->po_number} created successfully (" . ucfirst(str_replace('_', ' ', $po->status)) . ")."
        );
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $this->authorize('view', $purchaseOrder);

        $purchaseOrder->load([
            'supplier',
            'items.product',
            'createdBy',
            'approvedBy',
            'rejectedBy',
            'vendorPayments.recordedBy'
        ]);

        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder): View|RedirectResponse
    {
        $this->authorize('view', $purchaseOrder);

        if (!$purchaseOrder->canBeEdited()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->withErrors(['edit' => "Purchase Order is {$purchaseOrder->status} and cannot be edited. Only draft or rejected orders can be modified."]);
        }

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('purchase_orders.edit', compact('purchaseOrder', 'suppliers', 'products'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('update', $purchaseOrder);

        if (!$purchaseOrder->canBeEdited()) {
            return back()->withErrors(['edit' => "Purchase Order is {$purchaseOrder->status} and cannot be edited."]);
        }

        $validated = $request->validate([
            'supplier_id'              => ['required', 'exists:suppliers,id'],
            'order_date'               => ['required', 'date'],
            'expected_delivery_date'   => ['nullable', 'date'],
            'tax_percent'              => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shipping_cost'            => ['nullable', 'numeric', 'min:0'],
            'notes'                    => ['nullable', 'string'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.product_id'       => ['nullable', 'exists:products,id'],
            'items.*.item_name'        => ['required', 'string', 'max:255'],
            'items.*.sku'              => ['nullable', 'string', 'max:100'],
            'items.*.hsn_code'         => ['nullable', 'string', 'max:20'],
            'items.*.unit_cost'        => ['required', 'numeric', 'min:0'],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
            'items.*.tax_rate'         => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($purchaseOrder, $validated) {
            $supplier = Supplier::findOrFail($validated['supplier_id']);
            $vendorStateCode = $supplier->gst_number ? GstHelper::extractStateCode($supplier->gst_number) : null;
            $companyStateCode = GstHelper::getCompanyStateCode();

            $purchaseOrder->update([
                'supplier_id'            => $validated['supplier_id'],
                'order_date'             => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'tax_percent'            => (float) ($validated['tax_percent'] ?? 18.0),
                'shipping_cost'          => (float) ($validated['shipping_cost'] ?? 0.0),
                'notes'                  => $validated['notes'] ?? null,
            ]);

            // Replace line items
            $purchaseOrder->items()->delete();

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['quantity_ordered'];
                $cost = (float) $item['unit_cost'];
                $lineSubtotal = round($qty * $cost, 2);

                $taxRate = isset($item['tax_rate']) && $item['tax_rate'] !== null
                    ? (float) $item['tax_rate']
                    : (float) ($validated['tax_percent'] ?? 18.0);

                $split = GstHelper::calculateTaxSplit($lineSubtotal, $taxRate, $vendorStateCode, $companyStateCode);

                $purchaseOrder->items()->create([
                    'product_id'        => $item['product_id'] ?? null,
                    'item_name'         => $item['item_name'],
                    'sku'               => $item['sku'] ?? null,
                    'hsn_code'          => $item['hsn_code'] ?? null,
                    'unit_cost'         => $cost,
                    'quantity_ordered'  => $qty,
                    'quantity_received' => 0,
                    'total_cost'        => $lineSubtotal,
                    'tax_rate'          => $taxRate,
                    'cgst_rate'         => $split['cgst_rate'],
                    'cgst_amount'       => $split['cgst_amount'],
                    'sgst_rate'         => $split['sgst_rate'],
                    'sgst_amount'       => $split['sgst_amount'],
                    'igst_rate'         => $split['igst_rate'],
                    'igst_amount'       => $split['igst_amount'],
                ]);
            }

            $purchaseOrder->recalculateTotals();
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('status', "Purchase Order {$purchaseOrder->po_number} updated successfully.");
    }

    /**
     * Submit PO for admin approval (Staff or Admin).
     */
    public function submitForApproval(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('submitForApproval', $purchaseOrder);

        if (!$purchaseOrder->submitForApproval()) {
            return back()->withErrors(['status' => "Cannot submit this Purchase Order for approval (current status: {$purchaseOrder->status})."]);
        }

        return back()->with('status', "Purchase Order {$purchaseOrder->po_number} submitted for admin approval.");
    }

    /**
     * Admin approves Purchase Order.
     */
    public function approve(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('approve', $purchaseOrder);

        if (!$purchaseOrder->approve(auth()->id())) {
            return back()->withErrors(['status' => "Cannot approve this Purchase Order (current status: {$purchaseOrder->status})."]);
        }

        return back()->with('status', "Purchase Order {$purchaseOrder->po_number} approved successfully.");
    }

    /**
     * Admin rejects Purchase Order with reason.
     */
    public function reject(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('reject', $purchaseOrder);

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!$purchaseOrder->reject(auth()->id(), $validated['rejection_reason'] ?? null)) {
            return back()->withErrors(['status' => "Cannot reject this Purchase Order (current status: {$purchaseOrder->status})."]);
        }

        return back()->with('status', "Purchase Order {$purchaseOrder->po_number} was rejected.");
    }

    /**
     * Mark approved PO as sent to vendor.
     */
    public function markSent(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('updateStatus', $purchaseOrder);

        if (!$purchaseOrder->markSent()) {
            return back()->withErrors(['status' => "Only approved Purchase Orders can be marked as sent."]);
        }

        return back()->with('status', "Purchase Order {$purchaseOrder->po_number} marked as sent to vendor.");
    }

    /**
     * Close a received PO.
     */
    public function close(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('updateStatus', $purchaseOrder);

        if (!$purchaseOrder->close()) {
            return back()->withErrors(['status' => "Only received Purchase Orders can be closed."]);
        }

        return back()->with('status', "Purchase Order {$purchaseOrder->po_number} has been closed.");
    }

    /**
     * Inward shipment items into central warehouse stock ledger and generate Goods Receipt Note (GRN).
     */
    public function receiveItems(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('receive', $purchaseOrder);

        $validated = $request->validate([
            'received'         => ['sometimes', 'array'],
            'received.*'       => ['nullable', 'integer', 'min:0'],
            'rejected'         => ['nullable', 'array'],
            'rejected.*'       => ['nullable', 'integer', 'min:0'],
            'reject_reason'    => ['nullable', 'array'],
            'reject_reason.*'  => ['nullable', 'string', 'max:255'],
            'serials'          => ['nullable', 'array'],
            'delivery_note_no' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string'],
            'received_date'    => ['nullable', 'date'],
        ]);

        $itemsData = [];
        $receivedInputs = $validated['received'] ?? [];
        $rejectedInputs = $validated['rejected'] ?? [];
        $rejectReasons = $validated['reject_reason'] ?? [];
        $serialsInputs = $validated['serials'] ?? [];

        foreach ($purchaseOrder->items as $item) {
            $itemId = $item->id;
            $qtyReceived = isset($receivedInputs[$itemId]) ? (int) $receivedInputs[$itemId] : 0;
            $qtyRejected = isset($rejectedInputs[$itemId]) ? (int) $rejectedInputs[$itemId] : 0;
            $reason = $rejectReasons[$itemId] ?? null;

            $rawSerials = $serialsInputs[$itemId] ?? [];
            if (is_string($rawSerials)) {
                $rawSerials = preg_split('/[\r\n,]+/', $rawSerials, -1, PREG_SPLIT_NO_EMPTY);
            }

            if ($qtyReceived > 0 || $qtyRejected > 0) {
                $itemsData[$itemId] = [
                    'qty_received'  => $qtyReceived,
                    'qty_rejected'  => $qtyRejected,
                    'reject_reason' => $reason,
                    'serials'       => $rawSerials,
                ];
            }
        }

        if (empty($itemsData)) {
            return back()->withErrors(['received' => 'Please enter at least 1 unit to inward or reject.']);
        }

        try {
            $goodsReceipt = $purchaseOrder->inwardStock([
                'delivery_note_no' => $validated['delivery_note_no'] ?? null,
                'notes'            => $validated['notes'] ?? null,
                'received_date'    => $validated['received_date'] ?? now()->toDateString(),
                'items'            => $itemsData,
            ], auth()->id());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['received' => $e->getMessage()]);
        }

        // Optional Goods Received Note alert to Supplier (Setting: default off)
        $settings = GatewaySetting::first();
        if ($settings && $settings->po_alert_on_goods_received) {
            try {
                $purchaseOrder->load('supplier');
                if ($purchaseOrder->supplier) {
                    $alertService = app(\App\Services\AlertNotificationService::class);
                    $alertService->sendAlert('po_goods_received', $purchaseOrder->supplier, [
                        'supplier_name'   => $purchaseOrder->supplier->company_name ?: $purchaseOrder->supplier->name,
                        'po_number'       => $purchaseOrder->po_number,
                        'grn_number'      => $goodsReceipt->grn_number,
                        'total_amount'    => '₹' . number_format((float) $purchaseOrder->total, 2),
                        'received_status' => ucfirst(str_replace('_', ' ', $purchaseOrder->status)),
                        'received_date'   => now()->format('d M Y, h:i A'),
                    ], $purchaseOrder);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Purchase Order goods receipt alert failed: " . $e->getMessage());
            }
        }

        return back()->with('status', "Goods Receipt Note {$goodsReceipt->grn_number} created and items inwarded to stock ledger.");
    }

    /**
     * Update PO status through strict state machine transitions.
     */
    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('updateStatus', $purchaseOrder);

        $validated = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $targetStatus = $validated['status'];

        // 'received' can only be set by inwardStock(), never by hand
        if ($targetStatus === PurchaseOrder::STATUS_RECEIVED) {
            return back()->withErrors(['status' => "The 'received' status cannot be set manually. It is automatically assigned when all items are inwarded into warehouse stock."]);
        }

        if ($targetStatus === PurchaseOrder::STATUS_CANCELLED) {
            if (!$purchaseOrder->canBeCancelled()) {
                return back()->withErrors(['status' => 'Cannot cancel this Purchase Order because goods have already been received or vendor payments have been recorded.']);
            }
        }

        if (!$purchaseOrder->canTransitionTo($targetStatus)) {
            return back()->withErrors(['status' => "Invalid status transition from '{$purchaseOrder->status}' to '{$targetStatus}'."]);
        }

        $purchaseOrder->update(['status' => $targetStatus]);

        return back()->with('status', "Purchase Order status updated to " . ucfirst(str_replace('_', ' ', $targetStatus)) . ".");
    }

    public function updatePayment(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('updatePayment', $purchaseOrder);

        $validated = $request->validate([
            'amount_paid'    => ['required', 'numeric', 'min:0', 'max:' . $purchaseOrder->total],
            'payment_status' => ['required', 'in:unpaid,partially_paid,paid'],
        ]);

        $purchaseOrder->update([
            'amount_paid'    => $validated['amount_paid'],
            'payment_status' => $validated['payment_status'],
        ]);

        return back()->with('status', 'Vendor payment record updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('delete', $purchaseOrder);

        if ($purchaseOrder->items()->sum('quantity_received') > 0) {
            return back()->withErrors(['delete' => 'Cannot delete a Purchase Order with inwarded stock.']);
        }

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')->with('status', 'Purchase Order deleted.');
    }

    /**
     * Download Purchase Order as official PDF.
     */
    public function downloadPdf(PurchaseOrder $purchaseOrder): \Illuminate\Http\Response
    {
        $this->authorize('view', $purchaseOrder);

        $purchaseOrder->load(['supplier', 'items.product', 'createdBy', 'approvedBy']);
        $pdf = Pdf::loadView('purchase_orders.pdf', compact('purchaseOrder'));
        $pdf->setPaper('a4', 'portrait');

        $safeSupplier = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $purchaseOrder->supplier?->name ?? 'Vendor'));
        $filename = 'PO_' . $purchaseOrder->po_number . ($safeSupplier ? '_' . $safeSupplier : '') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Stream Purchase Order PDF in browser.
     */
    public function streamPdf(PurchaseOrder $purchaseOrder): \Illuminate\Http\Response
    {
        $this->authorize('view', $purchaseOrder);

        $purchaseOrder->load(['supplier', 'items.product', 'createdBy', 'approvedBy']);
        $pdf = Pdf::loadView('purchase_orders.pdf', compact('purchaseOrder'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('PO_' . $purchaseOrder->po_number . '.pdf');
    }

    /**
     * Public temporary signed route for suppliers to view/download Purchase Order PDF from WhatsApp link.
     * Protected with 14-day expiry and strict security headers (noindex, noarchive).
     */
    public function publicPdf(Request $request, PurchaseOrder $purchaseOrder): \Illuminate\Http\Response
    {
        $purchaseOrder->load(['supplier', 'items.product', 'createdBy', 'approvedBy']);
        $pdf = Pdf::loadView('purchase_orders.pdf', compact('purchaseOrder'));
        $pdf->setPaper('a4', 'portrait');

        $response = $request->has('download')
            ? $pdf->download('PO_' . $purchaseOrder->po_number . '.pdf')
            : $pdf->stream('PO_' . $purchaseOrder->po_number . '.pdf');

        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $response->headers->set('Cache-Control', 'private, no-cache, no-store, must-revalidate');

        return $response;
    }

    /**
     * Generate prefilled WhatsApp deep link for the user to open. Never sent in the background.
     * Uses a temporary signed PDF URL (14 days).
     */
    public function shareWhatsApp(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $purchaseOrder);

        $validated = $request->validate([
            'phone'             => ['nullable', 'string', 'max:25'],
            'message'           => ['nullable', 'string', 'max:5000'],
            'template_type'     => ['nullable', 'in:po_dispatch,stock_inquiry,payment_confirmation'],
            'vendor_payment_id' => ['nullable', 'integer', 'exists:vendor_payments,id'],
        ]);

        $purchaseOrder->load(['supplier', 'items', 'vendorPayments']);
        $phone = $validated['phone'] ?: ($purchaseOrder->supplier?->phone ?? '');
        $customNote = $validated['message'] ?? null;
        $templateType = $validated['template_type'] ?? 'po_dispatch';

        // 14-day temporary signed link for vendor access
        $publicPdfUrl = URL::temporarySignedRoute(
            'purchase-orders.public-pdf',
            now()->addDays(14),
            ['purchase_order' => $purchaseOrder]
        );

        if ($templateType === 'stock_inquiry') {
            $messageText = $purchaseOrder->buildStockInquiryWhatsAppMessage($customNote);
            $whatsappUrl = "https://api.whatsapp.com/send?phone=" . preg_replace('/[^0-9]/', '', (strlen($phone) === 10 ? '91' . $phone : $phone)) . "&text=" . urlencode($messageText);
        } elseif ($templateType === 'payment_confirmation') {
            $paymentId = $validated['vendor_payment_id'] ?? null;
            $payment = $paymentId ? $purchaseOrder->vendorPayments->firstWhere('id', (int) $paymentId) : $purchaseOrder->vendorPayments->first();
            $messageText = $purchaseOrder->buildPaymentConfirmationWhatsAppMessage($payment, $customNote);
            $whatsappUrl = "https://api.whatsapp.com/send?phone=" . preg_replace('/[^0-9]/', '', (strlen($phone) === 10 ? '91' . $phone : $phone)) . "&text=" . urlencode($messageText);
        } else {
            $messageText = $purchaseOrder->buildWhatsAppMessage($customNote, $publicPdfUrl);
            $whatsappUrl = $purchaseOrder->getWhatsAppUrl($phone, $customNote, $publicPdfUrl);

            // Auto-transition to sent if currently approved
            if ($purchaseOrder->status === PurchaseOrder::STATUS_APPROVED) {
                $purchaseOrder->markSent();
            }
        }

        // Record in Notification Log
        try {
            \App\Models\NotificationLog::create([
                'channel'         => 'whatsapp',
                'event_type'      => 'po_shared',
                'recipient_type'  => 'vendor',
                'recipient_name'  => $purchaseOrder->supplier?->company_name ?: ($purchaseOrder->supplier?->name ?? 'Vendor'),
                'recipient_phone' => $phone,
                'recipient_email' => $purchaseOrder->supplier?->email,
                'subject'         => "PO #{$purchaseOrder->po_number} WhatsApp Share ({$templateType})",
                'message_body'    => $messageText,
                'action_url'      => route('purchase-orders.show', $purchaseOrder),
                'status'          => 'initiated',
                'reference_type'  => PurchaseOrder::class,
                'reference_id'    => $purchaseOrder->id,
                'sent_at'         => now(),
                'created_by'      => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Purchase Order WhatsApp log failed: " . $e->getMessage());
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'whatsapp_url' => $whatsappUrl,
                'pdf_url'      => route('purchase-orders.pdf', $purchaseOrder),
                'public_pdf'   => $publicPdfUrl,
                'phone'        => $phone,
                'message'      => $messageText,
            ]);
        }

        return redirect()->away($whatsappUrl);
    }

    /**
     * Send official Purchase Order / Inquiry / Payment Advice to Supplier via Email.
     * Catches and displays clear errors on failure.
     */
    public function sendEmail(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('view', $purchaseOrder);

        $validated = $request->validate([
            'email'             => ['required', 'email'],
            'subject'           => ['nullable', 'string', 'max:255'],
            'custom_message'    => ['nullable', 'string', 'max:2000'],
            'template_type'     => ['nullable', 'in:po_dispatch,stock_inquiry,payment_confirmation'],
            'vendor_payment_id' => ['nullable', 'integer', 'exists:vendor_payments,id'],
        ]);

        $purchaseOrder->load(['supplier', 'items', 'vendorPayments']);
        $recipientEmail = $validated['email'];
        $templateType = $validated['template_type'] ?? 'po_dispatch';
        $customMessage = $validated['custom_message'] ?? null;
        $vendorName = $purchaseOrder->supplier?->contact_person ?: ($purchaseOrder->supplier?->company_name ?: $purchaseOrder->supplier?->name ?: 'Vendor Partner');

        $publicPdfUrl = URL::temporarySignedRoute(
            'purchase-orders.public-pdf',
            now()->addDays(14),
            ['purchase_order' => $purchaseOrder]
        );

        if ($templateType === 'stock_inquiry') {
            $subject = $validated['subject'] ?: "Stock & Price Inquiry for PO #{$purchaseOrder->po_number}";
            $itemsHtml = '';
            foreach ($purchaseOrder->items as $idx => $item) {
                $num = $idx + 1;
                $itemsHtml .= "
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 10px 12px; text-align: center; color: #64748b;'>{$num}</td>
                        <td style='padding: 10px 12px; color: #0f172a; font-weight: bold;'>
                            " . e($item->item_name) . "
                            " . ($item->sku ? "<br><span style='font-size: 11px; color: #64748b; font-family: monospace;'>SKU: " . e($item->sku) . "</span>" : "") . "
                        </td>
                        <td style='padding: 10px 12px; text-align: right; font-weight: bold; color: #0f172a;'>" . (int) $item->quantity_ordered . "</td>
                        <td style='padding: 10px 12px; text-align: right; color: #64748b;'>Pending Quote</td>
                    </tr>";
            }

            $styledBody = "
                <div style='font-family: Arial, sans-serif; font-size: 14px; color: #1e293b; max-width: 680px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;'>
                    <div style='background-color: #0369a1; padding: 20px 24px; color: #ffffff;'>
                        <h2 style='margin: 0; font-size: 18px; color: #e0f2fe;'>Stock & Price Inquiry</h2>
                        <p style='margin: 4px 0 0 0; font-size: 12px; color: #bae6fd;'>PO Reference: #{$purchaseOrder->po_number}</p>
                    </div>
                    <div style='padding: 24px; background-color: #ffffff;'>
                        <p>Dear <strong>" . e($vendorName) . "</strong>,</p>
                        <p>Kindly provide stock availability and best quotes for the following items:</p>
                        <table style='width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 13px;'>
                            <thead>
                                <tr style='background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569;'>
                                    <th style='padding: 8px 12px;'>#</th>
                                    <th style='padding: 8px 12px; text-align: left;'>Item</th>
                                    <th style='padding: 8px 12px; text-align: right;'>Qty</th>
                                    <th style='padding: 8px 12px; text-align: right;'>Quote Rate</th>
                                </tr>
                            </thead>
                            <tbody>{$itemsHtml}</tbody>
                        </table>
                        " . ($customMessage ? "<div style='margin: 16px 0; padding: 12px 16px; background-color: #f8fafc; border-left: 4px solid #0284c7; font-size: 13px;'>" . nl2br(e($customMessage)) . "</div>" : "") . "
                    </div>
                </div>";
        } else {
            // Default PO Dispatch
            $subject = $validated['subject'] ?: "Purchase Order #{$purchaseOrder->po_number}";
            $itemsHtml = '';
            foreach ($purchaseOrder->items as $idx => $item) {
                $num = $idx + 1;
                $itemsHtml .= "
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 10px 12px; text-align: center; color: #64748b;'>{$num}</td>
                        <td style='padding: 10px 12px; color: #0f172a; font-weight: bold;'>
                            " . e($item->item_name) . "
                            " . ($item->sku ? "<br><span style='font-size: 11px; color: #64748b; font-family: monospace;'>SKU: " . e($item->sku) . "</span>" : "") . "
                        </td>
                        <td style='padding: 10px 12px; text-align: right; color: #0f172a;'>" . (int) $item->quantity_ordered . "</td>
                        <td style='padding: 10px 12px; text-align: right; color: #0f172a;'>₹" . number_format((float) $item->unit_cost, 2) . "</td>
                        <td style='padding: 10px 12px; text-align: right; color: #0f172a; font-weight: bold;'>₹" . number_format((float) ($item->total_cost ?: ($item->quantity_ordered * $item->unit_cost)), 2) . "</td>
                    </tr>";
            }

            $styledBody = "
                <div style='font-family: Arial, sans-serif; font-size: 14px; color: #1e293b; max-width: 700px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;'>
                    <div style='background-color: #0f172a; padding: 24px; color: #ffffff;'>
                        <table style='width: 100%;'>
                            <tr>
                                <td>
                                    <h2 style='margin: 0; font-size: 20px; color: #38bdf8;'>Purchase Order Dispatch</h2>
                                    <p style='margin: 4px 0 0 0; font-size: 12px; color: #94a3b8;'>PO #{$purchaseOrder->po_number}</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style='padding: 24px; background-color: #ffffff;'>
                        <p>Dear <strong>" . e($vendorName) . "</strong>,</p>
                        <p>Please find our official Purchase Order details below. Kindly acknowledge receipt.</p>
                        " . ($customMessage ? "<div style='margin: 16px 0; padding: 12px 16px; background-color: #f8fafc; border-left: 4px solid #3b82f6; font-size: 13px; color: #334155;'>" . nl2br(e($customMessage)) . "</div>" : "") . "
                        <table style='width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px;'>
                            <thead>
                                <tr style='background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569;'>
                                    <th style='padding: 10px 12px; text-align: center;'>#</th>
                                    <th style='padding: 10px 12px; text-align: left;'>Product Description</th>
                                    <th style='padding: 10px 12px; text-align: right;'>Qty</th>
                                    <th style='padding: 10px 12px; text-align: right;'>Unit Rate</th>
                                    <th style='padding: 10px 12px; text-align: right;'>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>{$itemsHtml}</tbody>
                            <tfoot>
                                <tr style='border-top: 2px solid #e2e8f0;'>
                                    <td colspan='4' style='padding: 10px 12px; text-align: right; font-weight: bold;'>Grand Total:</td>
                                    <td style='padding: 10px 12px; text-align: right; font-weight: bold; color: #2563eb;'>₹" . number_format((float) $purchaseOrder->total, 2) . "</td>
                                </tr>
                            </tfoot>
                        </table>
                        <div style='text-align: center; margin: 24px 0;'>
                            <a href='{$publicPdfUrl}' style='display: inline-block; background-color: #0f172a; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 13px;'>View Official Signed PDF (Valid 14 Days)</a>
                        </div>
                    </div>
                </div>";

            if ($purchaseOrder->status === PurchaseOrder::STATUS_APPROVED) {
                $purchaseOrder->markSent();
            }
        }

        try {
            if (class_exists(\App\Services\MailConfigService::class)) {
                \App\Services\MailConfigService::applyDynamicMailConfig();
            }
            \Illuminate\Support\Facades\Mail::html($styledBody, function ($msg) use ($recipientEmail, $purchaseOrder, $subject) {
                $msg->to($recipientEmail, $purchaseOrder->supplier?->name ?? 'Vendor')->subject($subject);
            });
            $status = 'sent';
            $error = null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("PO Email dispatch failed: " . $e->getMessage());
            $status = 'failed';
            $error = $e->getMessage();
        }

        // Record in Notification Log
        try {
            \App\Models\NotificationLog::create([
                'channel'         => 'email',
                'event_type'      => 'po_shared',
                'recipient_type'  => 'vendor',
                'recipient_name'  => $purchaseOrder->supplier?->company_name ?: ($purchaseOrder->supplier?->name ?? 'Vendor'),
                'recipient_phone' => $purchaseOrder->supplier?->phone,
                'recipient_email' => $recipientEmail,
                'subject'         => $subject,
                'message_body'    => strip_tags(str_replace('<br>', "\n", $styledBody)),
                'action_url'      => route('purchase-orders.show', $purchaseOrder),
                'status'          => $status,
                'error_message'   => $error,
                'reference_type'  => PurchaseOrder::class,
                'reference_id'    => $purchaseOrder->id,
                'sent_at'         => now(),
                'created_by'      => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            // Ignore log failure
        }

        if ($status === 'failed') {
            return back()->withErrors(['email' => "Email dispatch failed: {$error}"]);
        }

        return back()->with('status', "✅ Purchase Order #{$purchaseOrder->po_number} emailed successfully to {$recipientEmail}.");
    }
}

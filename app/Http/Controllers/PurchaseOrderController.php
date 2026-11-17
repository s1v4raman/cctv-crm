<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseOrderRequest;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    use \App\Http\Controllers\Concerns\ListsRecords;

    public function index(Request $request): View
    {
        if (auth()->user()?->role === 'technician') {
            abort(403, 'Technicians are not authorized to view purchase orders.');
        }
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
        $totalSpend = (float) PurchaseOrder::whereIn('status', ['ordered', 'partially_received', 'received'])->sum('total');
        $pendingDeliveryCount = PurchaseOrder::whereIn('status', ['ordered', 'partially_received'])->count();
        $unpaidBalance = (float) PurchaseOrder::whereIn('status', ['ordered', 'partially_received', 'received'])->sum(DB::raw('total - amount_paid'));

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
        $selectedSupplierId = $request->input('supplier_id');
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('purchase_orders.create', compact('suppliers', 'products', 'selectedSupplierId'));
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $po = DB::transaction(function () use ($validated, $request) {
            $subtotal = 0.0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['quantity_ordered'];
                $cost = (float) $item['unit_cost'];
                $itemTotal = $qty * $cost;
                $subtotal += $itemTotal;

                $itemsData[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'sku' => $item['sku'] ?? null,
                    'unit_cost' => $cost,
                    'quantity_ordered' => $qty,
                    'quantity_received' => 0,
                    'total_cost' => $itemTotal,
                ];
            }

            $taxPercent = (float) ($validated['tax_percent'] ?? 18.0);
            $taxAmount = $subtotal * ($taxPercent / 100.0);
            $shipping = (float) ($validated['shipping_cost'] ?? 0.0);
            $total = $subtotal + $taxAmount + $shipping;

            $countToday = PurchaseOrder::whereDate('created_at', now()->toDateString())->count();
            $poNumber = 'PO-' . now()->format('Ymd') . '-' . str_pad((string) ($countToday + 1), 4, '0', STR_PAD_LEFT);

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'po_number' => $poNumber,
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'status' => $validated['status'] ?? 'ordered',
                'subtotal' => $subtotal,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shipping,
                'total' => $total,
                'payment_status' => 'unpaid',
                'amount_paid' => 0.00,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($itemsData as $item) {
                $purchaseOrder->items()->create($item);
            }

            return $purchaseOrder;
        });

        // Dispatch Purchase Order notification to Supplier
        try {
            $po->load(['supplier', 'items']);
            if ($po->supplier) {
                $alertService = app(\App\Services\AlertNotificationService::class);
                $alertService->sendAlert('po_created', $po->supplier, [
                    'supplier_name'          => $po->supplier->company_name ?: $po->supplier->name,
                    'po_number'              => $po->po_number,
                    'order_date'             => \Carbon\Carbon::parse($po->order_date)->format('d M Y'),
                    'total_amount'           => '₹' . number_format((float) $po->total, 2),
                    'item_count'             => $po->items->count(),
                    'expected_delivery_date' => $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('d M Y') : 'Immediate',
                    'po_link'                => route('purchase-orders.show', $po),
                ], $po);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Purchase Order creation alert failed: " . $e->getMessage());
        }

        return redirect()->route('purchase-orders.show', $po)->with('status', "Purchase Order {$po->po_number} created successfully.");
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'items.product', 'createdBy', 'vendorPayments.recordedBy']);
        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        return redirect()->route('purchase-orders.show', $purchaseOrder);
    }

    public function receiveItems(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $validated = $request->validate([
            'received' => ['required', 'array'],
            'received.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $receivedQuantities = array_filter($validated['received'], fn($q) => (int) $q > 0);

        if (empty($receivedQuantities)) {
            return back()->withErrors(['received' => 'Please enter at least 1 unit to inward.']);
        }

        $purchaseOrder->inwardStock($receivedQuantities, auth()->id());

        // Dispatch Goods Received Note alert to Supplier
        try {
            $purchaseOrder->load('supplier');
            if ($purchaseOrder->supplier) {
                $alertService = app(\App\Services\AlertNotificationService::class);
                $alertService->sendAlert('po_goods_received', $purchaseOrder->supplier, [
                    'supplier_name'   => $purchaseOrder->supplier->company_name ?: $purchaseOrder->supplier->name,
                    'po_number'       => $purchaseOrder->po_number,
                    'total_amount'    => '₹' . number_format((float) $purchaseOrder->total, 2),
                    'received_status' => ucfirst(str_replace('_', ' ', $purchaseOrder->status)),
                    'received_date'   => now()->format('d M Y, h:i A'),
                ], $purchaseOrder);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Purchase Order goods receipt alert failed: " . $e->getMessage());
        }

        return back()->with('status', 'Shipment items inwarded into central warehouse inventory.');
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,ordered,cancelled'],
        ]);

        $purchaseOrder->update(['status' => $validated['status']]);

        return back()->with('status', "Purchase Order status updated to {$validated['status']}.");
    }

    public function updatePayment(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $validated = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:0', 'max:' . $purchaseOrder->total],
            'payment_status' => ['required', 'in:unpaid,partially_paid,paid'],
        ]);

        $purchaseOrder->update([
            'amount_paid' => $validated['amount_paid'],
            'payment_status' => $validated['payment_status'],
        ]);

        return back()->with('status', 'Vendor payment record updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if ($purchaseOrder->status === 'received' || $purchaseOrder->status === 'partially_received') {
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
        $purchaseOrder->load(['supplier', 'items.product', 'createdBy']);
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
        $purchaseOrder->load(['supplier', 'items.product', 'createdBy']);
        $pdf = Pdf::loadView('purchase_orders.pdf', compact('purchaseOrder'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('PO_' . $purchaseOrder->po_number . '.pdf');
    }

    /**
     * Public signed route for suppliers to view/download Purchase Order PDF from WhatsApp link.
     */
    public function publicPdf(Request $request, PurchaseOrder $purchaseOrder): \Illuminate\Http\Response
    {
        $purchaseOrder->load(['supplier', 'items.product', 'createdBy']);
        $pdf = Pdf::loadView('purchase_orders.pdf', compact('purchaseOrder'));
        $pdf->setPaper('a4', 'portrait');

        if ($request->has('download')) {
            return $pdf->download('PO_' . $purchaseOrder->po_number . '.pdf');
        }

        return $pdf->stream('PO_' . $purchaseOrder->po_number . '.pdf');
    }

    /**
     * Share Purchase Order with Supplier via WhatsApp with 1-click template support.
     */
    public function shareWhatsApp(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'phone'             => ['nullable', 'string', 'max:25'],
            'custom_note'       => ['nullable', 'string', 'max:2000'],
            'template_type'     => ['nullable', 'in:po_dispatch,stock_inquiry,payment_confirmation'],
            'vendor_payment_id' => ['nullable', 'integer', 'exists:vendor_payments,id'],
        ]);

        $purchaseOrder->load(['supplier', 'items.product', 'createdBy', 'vendorPayments']);
        $phone = $validated['phone'] ?: ($purchaseOrder->supplier?->phone ?? '');
        $customNote = $validated['custom_note'] ?? null;
        $templateType = $validated['template_type'] ?? 'po_dispatch';

        $publicPdfUrl = URL::signedRoute('purchase-orders.public-pdf', ['purchase_order' => $purchaseOrder]);

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
                'subject'         => "Purchase Order #{$purchaseOrder->po_number} (" . ucfirst(str_replace('_', ' ', $templateType)) . ")",
                'message_body'    => $messageText,
                'action_url'      => $publicPdfUrl,
                'status'          => 'sent',
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
     */
    public function sendEmail(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
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

        if ($templateType === 'stock_inquiry') {
            $subject = $validated['subject'] ?: "Stock & Price Inquiry for PO #{$purchaseOrder->po_number} — Precision IT Systems";
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
                        <table style='width: 100%;'>
                            <tr>
                                <td>
                                    <h2 style='margin: 0; font-size: 18px; color: #e0f2fe;'>Precision IT Systems</h2>
                                    <p style='margin: 4px 0 0 0; font-size: 12px; color: #bae6fd;'>Stock Availability & Dealer Price Quotation Request</p>
                                </td>
                                <td style='text-align: right;'>
                                    <span style='background-color: #0284c7; color: #ffffff; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; font-family: monospace;'>PO #{$purchaseOrder->po_number}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style='padding: 24px; background-color: #ffffff; line-height: 1.6;'>
                        <p style='margin-top: 0;'>Dear <strong>" . e($vendorName) . "</strong>,</p>
                        <p>Greetings from Precision IT Systems. We have procurement requirements matching your product line. Kindly check inventory availability and furnish your best dealer quotation for the following items/SKUs:</p>

                        " . ($customMessage ? "<div style='margin: 16px 0; padding: 12px 16px; background-color: #f0f9ff; border-left: 4px solid #0284c7; font-size: 13px; color: #0369a1;'>" . nl2br(e($customMessage)) . "</div>" : "") . "

                        <table style='width: 100%; border-collapse: collapse; margin: 18px 0; font-size: 13px;'>
                            <thead>
                                <tr style='background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569;'>
                                    <th style='padding: 10px 12px; text-align: center; width: 40px;'>#</th>
                                    <th style='padding: 10px 12px; text-align: left;'>Product Name & Model / SKU</th>
                                    <th style='padding: 10px 12px; text-align: right;'>Required Qty</th>
                                    <th style='padding: 10px 12px; text-align: right;'>Quotation</th>
                                </tr>
                            </thead>
                            <tbody>{$itemsHtml}</tbody>
                        </table>

                        <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 18px; margin-top: 16px; font-size: 12px; color: #334155;'>
                            <strong>📍 Delivery Location:</strong> Precision IT Systems Central Warehouse, Coimbatore, Tamil Nadu<br>
                            <strong>Timeline:</strong> Within 2-3 Business Days
                        </div>

                        <div style='margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b;'>
                            <strong>Precision IT Systems — Commercial & Surveillance Division</strong><br>
                            Phone: +91 96772 57774 | Email: operations@precisionit.co.in<br>
                            GSTIN: 33AAACP1234A1Z5
                        </div>
                    </div>
                </div>";
        } elseif ($templateType === 'payment_confirmation') {
            $paymentId = $validated['vendor_payment_id'] ?? null;
            $payment = $paymentId ? $purchaseOrder->vendorPayments->firstWhere('id', (int) $paymentId) : $purchaseOrder->vendorPayments->first();
            $amount = $payment ? (float) $payment->amount : (float) $purchaseOrder->amount_paid;
            $utr = $payment?->transaction_reference ?: 'N/A';
            $subject = $validated['subject'] ?: "Payment Confirmation & Remittance Advice: ₹" . number_format($amount, 2) . " (PO #{$purchaseOrder->po_number}) — Precision IT Systems";

            if ($payment) {
                $styledBody = $payment->buildEmailHtml($customMessage);
            } else {
                $styledBody = "
                    <div style='font-family: Arial, sans-serif; font-size: 14px; color: #1e293b; max-width: 650px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;'>
                        <div style='background-color: #064e3b; padding: 20px 24px; color: #ffffff;'>
                            <h2 style='margin: 0; font-size: 18px; color: #34d399;'>Precision IT Systems</h2>
                            <p style='margin: 4px 0 0 0; font-size: 12px; color: #a7f3d0;'>Payment Remittance Advice</p>
                        </div>
                        <div style='padding: 24px; line-height: 1.6; background-color: #ffffff;'>
                            <p style='margin-top: 0;'>Dear <strong>" . e($vendorName) . "</strong>,</p>
                            <p>We have processed payment against Purchase Order <strong>#{$purchaseOrder->po_number}</strong>. Summary below:</p>
                            <div style='margin: 18px 0; padding: 18px; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px;'>
                                <div style='font-size: 11px; font-weight: bold; color: #065f46;'>Amount Remitted</div>
                                <div style='font-size: 26px; font-weight: bold; color: #047857; font-family: monospace;'>₹" . number_format($amount, 2) . "</div>
                                <div style='margin-top: 10px; font-size: 13px; color: #334155;'>
                                    <strong>PO Number:</strong> {$purchaseOrder->po_number}<br>
                                    <strong>Status:</strong> " . ucfirst(str_replace('_', ' ', $purchaseOrder->payment_status)) . "
                                </div>
                            </div>
                            " . ($customMessage ? "<div style='margin: 14px 0; padding: 12px 14px; background-color: #f8fafc; border-left: 4px solid #10b981; font-size: 13px;'>" . nl2br(e($customMessage)) . "</div>" : "") . "
                        </div>
                    </div>";
            }
        } else {
            // Default PO Dispatch
            $subject = $validated['subject'] ?: "Purchase Order #{$purchaseOrder->po_number} — Precision IT Systems";
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

            $publicPdfUrl = URL::signedRoute('purchase-orders.public-pdf', ['purchase_order' => $purchaseOrder]);

            $styledBody = "
                <div style='font-family: Arial, sans-serif; font-size: 14px; color: #1e293b; max-width: 700px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;'>
                    <div style='background-color: #0f172a; padding: 24px; color: #ffffff;'>
                        <table style='width: 100%;'>
                            <tr>
                                <td>
                                    <h2 style='margin: 0; font-size: 20px; color: #38bdf8;'>Precision IT Systems</h2>
                                    <p style='margin: 4px 0 0 0; font-size: 12px; color: #94a3b8;'>Purchase Order & Supply Chain Operations</p>
                                </td>
                                <td style='text-align: right;'>
                                    <span style='display: inline-block; background-color: #2563eb; color: #ffffff; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 12px; font-family: monospace;'>PO #{$purchaseOrder->po_number}</span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style='padding: 24px; background-color: #ffffff;'>
                        <p style='margin-top: 0;'>Dear <strong>" . e($vendorName) . "</strong>,</p>
                        <p>Please find our official Purchase Order details below. Kindly acknowledge receipt and confirm expected delivery date.</p>

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
                            <tbody>
                                {$itemsHtml}
                            </tbody>
                            <tfoot>
                                <tr style='border-top: 2px solid #e2e8f0;'>
                                    <td colspan='4' style='padding: 10px 12px; text-align: right; font-weight: bold; color: #475569;'>Subtotal:</td>
                                    <td style='padding: 10px 12px; text-align: right; font-weight: bold;'>₹" . number_format((float) $purchaseOrder->subtotal, 2) . "</td>
                                </tr>
                                " . ($purchaseOrder->tax_amount > 0 ? "
                                <tr>
                                    <td colspan='4' style='padding: 6px 12px; text-align: right; color: #475569;'>Tax / GST (" . ($purchaseOrder->tax_percent > 0 ? $purchaseOrder->tax_percent . "%" : "") . "):</td>
                                    <td style='padding: 6px 12px; text-align: right;'>₹" . number_format((float) $purchaseOrder->tax_amount, 2) . "</td>
                                </tr>" : "") . "
                                " . ($purchaseOrder->shipping_cost > 0 ? "
                                <tr>
                                    <td colspan='4' style='padding: 6px 12px; text-align: right; color: #475569;'>Shipping & Handling:</td>
                                    <td style='padding: 6px 12px; text-align: right;'>₹" . number_format((float) $purchaseOrder->shipping_cost, 2) . "</td>
                                </tr>" : "") . "
                                <tr style='background-color: #f8fafc; font-size: 15px;'>
                                    <td colspan='4' style='padding: 12px; text-align: right; font-weight: bold; color: #0f172a;'>Grand Total:</td>
                                    <td style='padding: 12px; text-align: right; font-weight: bold; color: #2563eb;'>₹" . number_format((float) $purchaseOrder->total, 2) . "</td>
                                </tr>
                            </tfoot>
                        </table>

                        <div style='text-align: center; margin: 24px 0;'>
                            <a href='{$publicPdfUrl}' style='display: inline-block; background-color: #0f172a; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 13px;'>View & Download Official Signed PDF</a>
                        </div>

                        <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 18px; margin-top: 20px; font-size: 12px; color: #334155;'>
                            <strong>📍 Delivery & Inward Warehouse:</strong><br>
                            Precision IT Systems Central Warehouse, Coimbatore, Tamil Nadu<br>
                            " . ($purchaseOrder->expected_delivery_date ? "<strong>Expected by:</strong> " . $purchaseOrder->expected_delivery_date->format('d M Y') . "<br>" : "") . "
                            " . ($purchaseOrder->notes ? "<strong>Order Instructions:</strong> " . e($purchaseOrder->notes) . "<br>" : "") . "
                        </div>

                        <div style='margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b;'>
                            <strong>Precision IT Systems — Commercial & Surveillance Division</strong><br>
                            Phone: +91 96772 57774 | Email: operations@precisionit.co.in<br>
                            GSTIN: 33AAACP1234A1Z5
                        </div>
                    </div>
                </div>";
        }

        try {
            \App\Services\MailConfigService::applyDynamicMailConfig();
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
            // Log fallback
        }

        if ($status === 'failed') {
            return back()->with('error', "Email dispatch failed: {$error}");
        }

        return back()->with('status', "✅ Purchase Order #{$purchaseOrder->po_number} emailed successfully to {$recipientEmail}.");
    }
}

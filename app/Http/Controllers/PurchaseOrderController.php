<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseOrderRequest;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $purchaseOrder->load(['supplier', 'items.product', 'createdBy']);
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
     * Share Purchase Order with Supplier via WhatsApp.
     */
    public function shareWhatsApp(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'phone'       => ['nullable', 'string', 'max:25'],
            'custom_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $purchaseOrder->load(['supplier', 'items']);
        $phone = $validated['phone'] ?: ($purchaseOrder->supplier?->phone ?? '');
        $customNote = $validated['custom_note'] ?? null;

        $messageText = $purchaseOrder->buildWhatsAppMessage($customNote);
        $whatsappUrl = $purchaseOrder->getWhatsAppUrl($phone, $customNote);

        // Record in Notification Log
        try {
            \App\Models\NotificationLog::create([
                'channel'         => 'whatsapp',
                'event_type'      => 'po_shared',
                'recipient_type'  => 'vendor',
                'recipient_name'  => $purchaseOrder->supplier?->company_name ?: ($purchaseOrder->supplier?->name ?? 'Vendor'),
                'recipient_phone' => $phone,
                'recipient_email' => $purchaseOrder->supplier?->email,
                'subject'         => "Purchase Order #{$purchaseOrder->po_number}",
                'message_body'    => $messageText,
                'action_url'      => route('purchase-orders.show', $purchaseOrder),
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
                'phone'        => $phone,
                'message'      => $messageText,
            ]);
        }

        return redirect()->away($whatsappUrl);
    }

    /**
     * Send official Purchase Order to Supplier via Email.
     */
    public function sendEmail(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $validated = $request->validate([
            'email'          => ['required', 'email'],
            'subject'        => ['nullable', 'string', 'max:255'],
            'custom_message' => ['nullable', 'string', 'max:2000'],
        ]);

        $purchaseOrder->load(['supplier', 'items']);
        $recipientEmail = $validated['email'];
        $subject = $validated['subject'] ?: "Purchase Order #{$purchaseOrder->po_number} — Precision IT Systems";
        $customMessage = $validated['custom_message'] ?? null;

        // Build itemized HTML table
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
                </tr>
            ";
        }

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
                    <p style='margin-top: 0;'>Dear <strong>" . e($purchaseOrder->supplier?->contact_person ?: ($purchaseOrder->supplier?->company_name ?: $purchaseOrder->supplier?->name ?: 'Vendor Partner')) . "</strong>,</p>
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
            </div>
        ";

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

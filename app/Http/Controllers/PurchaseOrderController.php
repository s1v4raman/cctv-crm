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
    public function index(Request $request): View
    {
        if (auth()->user()?->role === 'technician') {
            abort(403, 'Technicians are not authorized to view purchase orders.');
        }
        $search = trim((string) $request->input('search', ''));
        $supplierId = $request->input('supplier_id');
        $status = $request->input('status');

        $query = PurchaseOrder::with(['supplier', 'items', 'createdBy'])->latest();

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

        $purchaseOrders = $query->paginate(15)->withQueryString();

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
}

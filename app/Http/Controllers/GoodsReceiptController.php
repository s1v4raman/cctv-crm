<?php

namespace App\Http\Controllers;

use App\Models\GatewaySetting;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use InvalidArgumentException;

class GoodsReceiptController extends Controller
{
    /**
     * Display a listing of Goods Receipt Notes (GRNs).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', GoodsReceipt::class);

        $query = GoodsReceipt::with(['supplier', 'purchaseOrder', 'receivedBy', 'items'])
            ->latest('received_date')
            ->latest('id');

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('grn_number', 'ilike', "%{$term}%")
                    ->orWhere('delivery_note_no', 'ilike', "%{$term}%")
                    ->orWhereHas('purchaseOrder', function ($poQ) use ($term) {
                        $poQ->where('po_number', 'ilike', "%{$term}%");
                    })
                    ->orWhereHas('supplier', function ($supQ) use ($term) {
                        $supQ->where('name', 'ilike', "%{$term}%")
                            ->orWhere('company_name', 'ilike', "%{$term}%");
                    });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('date_from')) {
            $query->where('received_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('received_date', '<=', $request->date_to);
        }

        $goodsReceipts = $query->paginate(20)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('goods_receipts.index', compact('goodsReceipts', 'suppliers'));
    }

    /**
     * Display the specified Goods Receipt Note detail.
     */
    public function show(GoodsReceipt $goodsReceipt): View
    {
        $this->authorize('view', $goodsReceipt);

        $goodsReceipt->load([
            'supplier',
            'purchaseOrder',
            'receivedBy',
            'items.product',
            'items.purchaseOrderItem',
            'items.serials',
        ]);

        return view('goods_receipts.show', compact('goodsReceipt'));
    }

    /**
     * Stream or download Goods Receipt Note PDF.
     */
    public function pdf(Request $request, GoodsReceipt $goodsReceipt): Response
    {
        $this->authorize('pdf', $goodsReceipt);

        $goodsReceipt->load([
            'supplier',
            'purchaseOrder',
            'receivedBy',
            'items.product',
            'items.purchaseOrderItem',
            'items.serials',
        ]);

        $settings = GatewaySetting::first();
        $pdf = Pdf::loadView('goods_receipts.pdf', compact('goodsReceipt', 'settings'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'GRN_' . $goodsReceipt->grn_number . '.pdf';

        return $request->has('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    /**
     * Show dedicated goods receipt form for a purchase order.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', GoodsReceipt::class);

        $poId = $request->query('purchase_order_id');
        if (!$poId) {
            return redirect()->route('purchase-orders.index')
                ->withErrors(['error' => 'Please select a Purchase Order to inward items.']);
        }

        $purchaseOrder = PurchaseOrder::with(['supplier', 'items.product'])->findOrFail($poId);

        if (!in_array($purchaseOrder->status, ['sent', 'partially_received', 'ordered'], true)) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->withErrors(['error' => "Purchase Order cannot be received in status '{$purchaseOrder->status}'."]);
        }

        return view('goods_receipts.create', compact('purchaseOrder'));
    }

    /**
     * Process Goods Receipt submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', GoodsReceipt::class);

        $validated = $request->validate([
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            'delivery_note_no'  => ['nullable', 'string', 'max:100'],
            'received_date'     => ['required', 'date'],
            'notes'             => ['nullable', 'string'],
            'received'          => ['sometimes', 'array'],
            'received.*'        => ['nullable', 'integer', 'min:0'],
            'rejected'          => ['nullable', 'array'],
            'rejected.*'        => ['nullable', 'integer', 'min:0'],
            'reject_reason'     => ['nullable', 'array'],
            'reject_reason.*'   => ['nullable', 'string', 'max:255'],
            'serials'           => ['nullable', 'array'],
            'mac_addresses'     => ['nullable', 'array'],
            'warranties'        => ['nullable', 'array'],
        ]);

        $purchaseOrder = PurchaseOrder::with('items.product')->findOrFail($validated['purchase_order_id']);

        $itemsData = [];
        $receivedInputs = $validated['received'] ?? [];
        $rejectedInputs = $validated['rejected'] ?? [];
        $rejectReasons = $validated['reject_reason'] ?? [];
        $serialsInputs = $validated['serials'] ?? [];
        $macInputs = $validated['mac_addresses'] ?? [];
        $warrantyInputs = $validated['warranties'] ?? [];

        foreach ($purchaseOrder->items as $item) {
            $itemId = $item->id;
            $qtyReceived = isset($receivedInputs[$itemId]) ? (int) $receivedInputs[$itemId] : 0;
            $qtyRejected = isset($rejectedInputs[$itemId]) ? (int) $rejectedInputs[$itemId] : 0;
            $reason = $rejectReasons[$itemId] ?? null;

            // Parse serial numbers
            $rawSerials = $serialsInputs[$itemId] ?? [];
            if (is_string($rawSerials)) {
                $rawSerials = preg_split('/[\r\n,]+/', $rawSerials, -1, PREG_SPLIT_NO_EMPTY);
            }

            $parsedSerials = [];
            foreach ($rawSerials as $idx => $sVal) {
                $sn = trim((string) $sVal);
                if ($sn !== '') {
                    $parsedSerials[] = [
                        'serial_no'      => $sn,
                        'mac_address'    => !empty($macInputs[$itemId][$idx]) ? trim($macInputs[$itemId][$idx]) : null,
                        'warranty_until' => !empty($warrantyInputs[$itemId][$idx]) ? $warrantyInputs[$itemId][$idx] : null,
                    ];
                }
            }

            if ($qtyReceived > 0 || $qtyRejected > 0) {
                $itemsData[$itemId] = [
                    'qty_received'  => $qtyReceived,
                    'qty_rejected'  => $qtyRejected,
                    'reject_reason' => $reason,
                    'serials'       => $parsedSerials,
                ];
            }
        }

        if (empty($itemsData)) {
            return back()->withInput()->withErrors(['received' => 'Please enter at least 1 unit to receive or reject.']);
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

        return redirect()->route('goods-receipts.show', $goodsReceipt)
            ->with('status', "Goods Receipt Note {$goodsReceipt->grn_number} created successfully.");
    }
}

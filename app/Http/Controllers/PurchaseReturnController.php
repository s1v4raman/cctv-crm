<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Services\PurchaseReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseReturnController extends Controller
{
    public function __construct(
        protected PurchaseReturnService $returnService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PurchaseReturn::class);

        $query = PurchaseReturn::with(['supplier', 'goodsReceipt', 'purchaseOrder', 'createdBy', 'debitNote'])
            ->latest('return_date')
            ->latest('id');

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('return_number', 'ilike', "%{$term}%")
                    ->orWhere('debit_note_number', 'ilike', "%{$term}%")
                    ->orWhereHas('supplier', function ($sq) use ($term) {
                        $sq->where('name', 'ilike', "%{$term}%")
                            ->orWhere('company_name', 'ilike', "%{$term}%");
                    })
                    ->orWhereHas('goodsReceipt', function ($gq) use ($term) {
                        $gq->where('grn_number', 'ilike', "%{$term}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchaseReturns = $query->paginate(20)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchase_returns.index', compact('purchaseReturns', 'suppliers'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', PurchaseReturn::class);

        $selectedReceipt = null;
        if ($request->filled('goods_receipt_id')) {
            $selectedReceipt = GoodsReceipt::with(['items.product', 'supplier', 'purchaseOrder'])
                ->findOrFail($request->goods_receipt_id);
        }

        $goodsReceipts = GoodsReceipt::with(['supplier', 'purchaseOrder'])
            ->latest('received_date')
            ->limit(50)
            ->get();

        return view('purchase_returns.create', compact('selectedReceipt', 'goodsReceipts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PurchaseReturn::class);

        $validated = $request->validate([
            'goods_receipt_id'           => ['required', 'exists:goods_receipts,id'],
            'return_date'                => ['required', 'date'],
            'reason'                     => ['nullable', 'string', 'max:255'],
            'notes'                      => ['nullable', 'string'],
            'items'                      => ['required', 'array', 'min:1'],
            'items.*.product_id'         => ['required', 'exists:products,id'],
            'items.*.goods_receipt_item_id' => ['nullable', 'exists:goods_receipt_items,id'],
            'items.*.quantity'           => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'          => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate'           => ['nullable', 'numeric', 'min:0'],
            'items.*.reason'             => ['nullable', 'string', 'max:255'],
        ]);

        $purchaseReturn = $this->returnService->createReturn($validated, auth()->id());

        // If the user clicked "Save & Confirm Now" and is admin
        if ($request->input('action') === 'confirm' && auth()->user()->isAdmin()) {
            $this->returnService->confirmReturn($purchaseReturn, auth()->id());
            return redirect()->route('purchase-returns.show', $purchaseReturn)
                ->with('success', "Purchase Return {$purchaseReturn->return_number} confirmed and Debit Note {$purchaseReturn->debit_note_number} generated.");
        }

        return redirect()->route('purchase-returns.show', $purchaseReturn)
            ->with('success', "Purchase Return {$purchaseReturn->return_number} created as draft.");
    }

    public function show(PurchaseReturn $purchaseReturn): View
    {
        $this->authorize('view', $purchaseReturn);

        $purchaseReturn->loadMissing([
            'supplier',
            'goodsReceipt.purchaseOrder',
            'items.product',
            'items.goodsReceiptItem',
            'debitNote',
            'createdBy',
            'approvedBy',
        ]);

        return view('purchase_returns.show', compact('purchaseReturn'));
    }

    public function confirm(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        $this->authorize('confirm', $purchaseReturn);

        try {
            $this->returnService->confirmReturn($purchaseReturn, auth()->id());

            return redirect()->route('purchase-returns.show', $purchaseReturn)
                ->with('success', "Return {$purchaseReturn->return_number} confirmed. Stock reduced and Debit Note {$purchaseReturn->debit_note_number} generated.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to confirm return: ' . $e->getMessage());
        }
    }
}

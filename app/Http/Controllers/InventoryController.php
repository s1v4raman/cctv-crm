<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $category = $request->input('category');
        $status = $request->input('status'); // all, in_stock, low_stock, out_of_stock

        $query = Product::query()->where('is_active', true);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model_no', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($status === 'low_stock') {
            $query->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'min_stock_alert');
        } elseif ($status === 'out_of_stock') {
            $query->where('stock_quantity', '<=', 0);
        } elseif ($status === 'in_stock') {
            $query->whereColumn('stock_quantity', '>', 'min_stock_alert');
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();

        // Metrics
        $totalProducts = Product::where('is_active', true)->count();
        $lowStockCount = Product::where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'min_stock_alert')
            ->count();
        $outOfStockCount = Product::where('is_active', true)
            ->where('stock_quantity', '<=', 0)
            ->count();
        $totalUnits = (int) Product::where('is_active', true)->sum('stock_quantity');
        $totalValuation = (float) Product::where('is_active', true)
            ->sum(DB::raw('stock_quantity * cost_price'));

        $categories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        $recentMovements = StockMovement::with(['product', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        return view('inventory.index', compact(
            'products',
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'totalUnits',
            'totalValuation',
            'categories',
            'recentMovements'
        ));
    }

    public function adjustStock(Request $request, Product $product, \App\Services\StockAlertService $stockAlertService): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:in,out,adjustment,return'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $type = $validated['type'];
        $qty = (int) $validated['quantity'];
        $currentStock = $product->stock_quantity;

        if ($type === 'in' || $type === 'return') {
            $newStock = $currentStock + $qty;
        } elseif ($type === 'out') {
            if ($qty > $currentStock) {
                return back()->withErrors(['quantity' => "Cannot dispatch {$qty} units. Only {$currentStock} in stock."]);
            }
            $newStock = $currentStock - $qty;
        } elseif ($type === 'adjustment') {
            // Direct override to the specified quantity
            $newStock = $qty;
            $qty = $newStock - $currentStock; // Difference
        } else {
            $newStock = $currentStock;
        }

        DB::transaction(function () use ($product, $type, $qty, $newStock, $validated) {
            $product->update(['stock_quantity' => $newStock]);

            StockMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $qty,
                'balance_after' => $newStock,
                'notes' => $validated['notes'] ?? 'Manual stock update',
                'user_id' => auth()->id(),
            ]);
        });

        // Trigger real-time low stock alert if threshold breached
        $product->refresh();
        $stockAlertService->checkAndTriggerAlert($product);

        return back()->with('status', "Stock for '{$product->name}' updated successfully (New balance: {$newStock} {$product->unit}).");
    }

    public function movements(Request $request): View
    {
        $query = StockMovement::with(['product', 'user'])->latest();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $movements = $query->paginate(25)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('inventory.movements', compact('movements', 'products'));
    }
}

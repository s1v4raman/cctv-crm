<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->input('status', 'all');
        $query = Product::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model_no', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($statusFilter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($statusFilter === 'approved') {
            $query->where(function ($q) {
                $q->where('status', 'approved')->orWhereNull('status');
            });
        }

        $pendingCount = Product::where('status', 'pending')->count();
        $approvedCount = Product::where(function ($q) {
            $q->where('status', 'approved')->orWhereNull('status');
        })->count();
        $totalCount = Product::count();

        $products = $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $allProducts = Product::select('id', 'name', 'sku', 'status')
            ->orderBy('name')
            ->get();

        return view('products.index', compact(
            'products',
            'statusFilter',
            'pendingCount',
            'approvedCount',
            'totalCount',
            'allProducts'
        ));
    }

    public function create(): View
    {
        $product = new Product();

        return view('products.create', compact('product'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $product = Product::create($data);

        if ($product->stock_quantity > 0) {
            \App\Models\StockMovement::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $product->stock_quantity,
                'balance_after' => $product->stock_quantity,
                'notes' => 'Initial opening stock',
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('status', 'Product added successfully.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validatedData($request, $product);

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('status', 'Product deleted successfully.');
    }

    public function approve(Request $request, Product $product): RedirectResponse
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can approve products.');
        }

        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:100'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
        ]);

        $product->update([
            'category' => $validated['category'] ?? $product->category,
            'cost_price' => $validated['cost_price'],
            'unit_price' => $validated['unit_price'],
            'unit' => $validated['unit'] ?? ($product->unit ?: 'Nos'),
            'status' => 'approved',
            'is_active' => true,
        ]);

        return redirect()
            ->back()
            ->with('status', "Product '{$product->name}' approved with updated pricing and category.");
    }

    public function merge(Request $request, Product $product): RedirectResponse
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can merge duplicate products.');
        }

        $request->validate([
            'target_product_id' => ['required', 'exists:products,id', 'different:' . $product->id],
        ]);

        $targetProduct = Product::findOrFail($request->target_product_id);
        $deletedName = $product->name;

        DB::transaction(function () use ($product, $targetProduct) {
            // Reassign project materials
            \App\Models\ProjectMaterial::where('product_id', $product->id)
                ->update(['product_id' => $targetProduct->id]);

            // Reassign quotation items
            \App\Models\QuotationItem::where('product_id', $product->id)
                ->update(['product_id' => $targetProduct->id]);

            // Reassign stock movements
            \App\Models\StockMovement::where('product_id', $product->id)
                ->update(['product_id' => $targetProduct->id]);

            // Reassign installed equipment
            \App\Models\InstalledEquipment::where('product_id', $product->id)
                ->update(['product_id' => $targetProduct->id]);

            // Reassign task material consumptions
            \App\Models\TaskMaterialConsumption::where('product_id', $product->id)
                ->update(['product_id' => $targetProduct->id]);

            // Delete the duplicate product
            $product->delete();
        });

        return redirect()
            ->back()
            ->with('status', "Product '{$deletedName}' merged into '{$targetProduct->name}' and duplicate removed.");
    }

    public function search(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('q', ''));

        if ($search === '') {
            return response()->json([]);
        }

        $products = Product::query()
            ->where('is_active', true)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model_no', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get([
                'id',
                'sku',
                'name',
                'category',
                'brand',
                'model_no',
                'description',
                'unit',
                'unit_price',
                'stock_quantity',
                'default_warranty_months',
            ]);

        return response()->json($products);
    }

    private function validatedData(Request $request, ?Product $product = null): array
    {
        $skuRule = Rule::unique('products', 'sku');

        if ($product) {
            $skuRule->ignore($product);
        }

        $data = $request->validate([
            'sku' => ['nullable', 'string', 'max:50', $skuRule],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model_no' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', Rule::in(['Nos', 'Mtr', 'Box', 'Set', 'Job', 'Pkt', 'Roll'])],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'min_stock_alert' => ['nullable', 'integer', 'min:0'],
            'default_warranty_months' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['stock_quantity'] = (int) ($data['stock_quantity'] ?? 0);
        $data['min_stock_alert'] = (int) ($data['min_stock_alert'] ?? 5);
        $data['default_warranty_months'] = (int) ($data['default_warranty_months'] ?? 24);

        return $data;
    }
}
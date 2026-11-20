<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    use \App\Http\Controllers\Concerns\ListsRecords;

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

        // Sort menu; by default items waiting for review first, then by name
        match ($request->get('sort')) {
            'name'       => $query->orderBy('name'),
            'price_desc' => $query->orderByDesc('unit_price'),
            'price_asc'  => $query->orderBy('unit_price'),
            'newest'     => $query->latest(),
            default      => $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")->orderBy('name'),
        };
        $products = $query->paginate($this->listPerPage($request))->withQueryString();

        $user = $request->user() ?: Auth::user();
        if ($user?->isTechnician() || $user?->isWorker()) {
            $products->getCollection()->makeHidden(['cost_price']);
            $products->getCollection()->each(function ($p) {
                $p->cost_price = 0;
            });
        }

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
            app(\App\Services\StockService::class)->recordOpening(
                $product,
                $product->stock_quantity,
                (float) $product->cost_price,
                auth()->id()
            );
        }

        return redirect()
            ->route('products.index')
            ->with('status', 'Product added successfully.');
    }

    /**
     * Instant On-the-Fly Product Creation (AJAX modal across all CRM pages).
     */
    public function quickStore(Request $request): JsonResponse
    {
        if (!Auth::user()->isOffice()) {
            return response()->json(['error' => 'Unauthorized. Only management can add products.'], 403);
        }

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'sku'            => ['nullable', 'string', 'max:50', Rule::unique('products', 'sku')],
            'category'       => ['nullable', 'string', 'max:100'],
            'brand'          => ['nullable', 'string', 'max:100'],
            'model_no'       => ['nullable', 'string', 'max:100'],
            'unit'           => ['nullable', 'string', 'max:50'],
            'cost_price'     => ['nullable', 'numeric', 'min:0'],
            'unit_price'     => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'description'    => ['nullable', 'string'],
        ]);

        $name = Product::cleanName($validated['name']);

        // Check if product with same normalized name already exists
        $existing = Product::where('name_key', Product::normalizeNameKey($name))->first();
        if ($existing) {
            return response()->json([
                'success'     => true,
                'is_existing' => true,
                'message'     => "Product '{$existing->name}' already exists in Catalog.",
                'product'     => [
                    'id'             => $existing->id,
                    'name'           => $existing->name,
                    'sku'            => $existing->sku,
                    'category'       => $existing->category,
                    'unit'           => $existing->unit,
                    'cost_price'     => (float) $existing->cost_price,
                    'unit_price'     => (float) $existing->unit_price,
                    'stock_quantity' => (float) $existing->stock_quantity,
                ]
            ]);
        }

        // Auto-generate SKU if omitted
        $sku = !empty($validated['sku']) ? trim($validated['sku']) : null;
        if (empty($sku)) {
            do {
                $sku = 'PRD-' . strtoupper(Str::random(6));
            } while (Product::where('sku', $sku)->exists());
        }

        $costPrice = isset($validated['cost_price']) ? (float) $validated['cost_price'] : 0.0;
        $unitPrice = isset($validated['unit_price']) ? (float) $validated['unit_price'] : ($costPrice > 0 ? round($costPrice * 1.25, 2) : 0.0);
        $unit      = !empty($validated['unit']) ? $validated['unit'] : 'Nos';
        $stockQty  = isset($validated['stock_quantity']) ? (float) $validated['stock_quantity'] : 0.0;

        $product = Product::create([
            'sku'                     => $sku,
            'name'                    => $name,
            'category'                => $validated['category'] ?? 'accessory',
            'brand'                   => $validated['brand'] ?? null,
            'model_no'                => $validated['model_no'] ?? null,
            'unit'                    => $unit,
            'cost_price'              => $costPrice,
            'unit_price'              => $unitPrice,
            'stock_quantity'          => $stockQty,
            'min_stock_alert'         => 5,
            'default_warranty_months' => 24,
            'description'             => $validated['description'] ?? null,
            'status'                  => 'approved',
            'is_active'               => true,
            'created_by'              => Auth::id(),
        ]);

        if ($stockQty > 0) {
            app(\App\Services\StockService::class)->recordOpening(
                $product,
                (int) $stockQty,
                (float) $costPrice,
                Auth::id()
            );
        }

        return response()->json([
            'success'     => true,
            'is_existing' => false,
            'message'     => "Product '{$product->name}' [{$product->sku}] added to Catalog & Pricing!",
            'product'     => [
                'id'             => $product->id,
                'name'           => $product->name,
                'sku'            => $product->sku,
                'category'       => $product->category,
                'unit'           => $product->unit,
                'cost_price'     => (float) $product->cost_price,
                'unit_price'     => (float) $product->unit_price,
                'stock_quantity' => (float) $product->stock_quantity,
            ]
        ]);
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
        if (!Auth::user()->isOffice()) {
            abort(403, 'Only office users can approve products.');
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

        $user = $request->user() ?: Auth::user();
        if ($user?->isTechnician() || $user?->isWorker()) {
            $products->makeHidden(['unit_price', 'cost_price']);
            $products->each(function ($p) {
                $p->unit_price = 0;
                $p->cost_price = 0;
            });
        }

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
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductAlias;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductAliasController extends Controller
{
    public function index(): View
    {
        $aliases = ProductAlias::with('product')
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(30);

        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'model_no', 'brand']);

        return view('admin.product_aliases.index', compact('aliases', 'products'));
    }

    public function approve(ProductAlias $alias): RedirectResponse
    {
        $alias->update(['status' => 'approved']);

        return back()->with('status', "Alias '{$alias->value}' approved for {$alias->product?->name}.");
    }

    public function merge(Request $request, ProductAlias $alias): RedirectResponse
    {
        $validated = $request->validate([
            'target_product_id' => ['required', 'exists:products,id'],
        ]);

        $alias->update([
            'product_id' => $validated['target_product_id'],
            'status'     => 'approved',
        ]);

        $target = Product::find($validated['target_product_id']);

        return back()->with('status', "Alias '{$alias->value}' merged into {$target?->name} and approved.");
    }

    public function destroy(ProductAlias $alias): RedirectResponse
    {
        $alias->delete();

        return back()->with('status', 'Alias removed.');
    }
}

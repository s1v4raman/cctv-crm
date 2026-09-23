<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        if (auth()->user()?->role === 'technician') {
            abort(403, 'Technicians are not authorized to view supplier records.');
        }
        $search = trim((string) $request->input('search', ''));
        $query = Supplier::query()->withCount('purchaseOrders')->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('gst_number', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate(15)->withQueryString();

        $totalSuppliers = Supplier::count();
        $activeSuppliers = Supplier::where('is_active', true)->count();

        return view('suppliers.index', compact('suppliers', 'totalSuppliers', 'activeSuppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $supplier = Supplier::create($validated);

        return redirect()->route('suppliers.show', $supplier)->with('status', "Supplier '{$supplier->name}' registered successfully.");
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['purchaseOrders.items', 'purchaseOrders.createdBy']);
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(StoreSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $supplier->update($validated);

        return redirect()->route('suppliers.show', $supplier)->with('status', "Supplier '{$supplier->name}' updated successfully.");
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('status', 'Supplier record deleted.');
    }
}

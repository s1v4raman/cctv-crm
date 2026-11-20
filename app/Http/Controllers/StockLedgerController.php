<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Project;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockLedgerController extends Controller
{
    /**
     * Display the stock ledger audit trail.
     * Technician and worker see only on-hand and movement quantities, never cost prices.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $canViewCosts = $user && $user->isOffice();

        $query = StockMovement::with(['product', 'project', 'creator', 'user'])
            ->latest('id');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('movement_type')) {
            $type = $request->movement_type;
            $query->where(function ($q) use ($type) {
                $q->where('movement_type', $type)
                  ->orWhere('type', $type);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->paginate(25)->withQueryString();

        $products = Product::where('is_active', true)->orderBy('name')->get();
        $projects = Project::orderBy('project_code')->get(['id', 'project_code', 'title']);

        return view('inventory.ledger', compact('movements', 'products', 'projects', 'canViewCosts'));
    }
}

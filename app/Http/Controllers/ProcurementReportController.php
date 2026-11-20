<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\AccountsPayableService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProcurementReportController extends Controller
{
    public function __construct(
        protected AccountsPayableService $apService
    ) {}

    /**
     * Unified Procurement & Inventory Reports Dashboard.
     */
    public function index(Request $request): View
    {
        $this->authorizeOffice();

        $tab = $request->query('tab', 'stock');

        $stockData = null;
        $purchasesData = null;
        $payablesData = null;
        $projectsData = null;

        if ($tab === 'stock') {
            $stockData = $this->getStockOnHandReport($request);
        } elseif ($tab === 'purchases') {
            $purchasesData = $this->getPurchasesReport($request);
        } elseif ($tab === 'payables') {
            $payablesData = $this->getPayablesReport();
        } elseif ($tab === 'projects') {
            $projectsData = $this->getProjectMaterialCostReport($request);
        }

        return view('reports.procurement.index', compact('tab', 'stockData', 'purchasesData', 'payablesData', 'projectsData'));
    }

    /**
     * Stock on hand and low stock data.
     */
    protected function getStockOnHandReport(Request $request): array
    {
        $query = Product::query()->orderBy('name');

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ilike', "%{$term}%")
                    ->orWhere('sku', 'ilike', "%{$term}%")
                    ->orWhere('brand', 'ilike', "%{$term}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->boolean('low_stock_only')) {
            $query->whereRaw('stock_quantity <= COALESCE(min_stock_alert, 5)');
        }

        $allProducts = (clone $query)->get();
        $totalValuation = $allProducts->sum(fn ($p) => (float) $p->stock_quantity * (float) $p->cost_price);
        $totalUnits = $allProducts->sum('stock_quantity');
        $lowStockCount = Product::whereRaw('stock_quantity <= COALESCE(min_stock_alert, 5)')->count();

        $products = $query->paginate(25)->withQueryString();
        $categories = Product::select('category')->whereNotNull('category')->distinct()->pluck('category');

        return compact('products', 'categories', 'totalValuation', 'totalUnits', 'lowStockCount');
    }

    /**
     * Purchases by Dimension (Supplier, Product, Project).
     */
    protected function getPurchasesReport(Request $request): array
    {
        $dimension = $request->query('dimension', 'supplier');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $rows = collect();
        $totalSpend = 0.0;

        if ($dimension === 'supplier') {
            $query = PurchaseOrder::with('supplier')
                ->whereNotIn('status', ['draft', 'cancelled', 'rejected']);

            if ($dateFrom) {
                $query->where('order_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->where('order_date', '<=', $dateTo);
            }

            $orders = $query->get();
            $grouped = $orders->groupBy('supplier_id');

            foreach ($grouped as $supplierId => $pos) {
                $supplier = $pos->first()->supplier;
                $val = (float) $pos->sum('total');
                $totalSpend += $val;
                $rows->push([
                    'id'          => $supplierId,
                    'name'        => $supplier?->company_name ?: ($supplier?->name ?? 'Unknown Supplier'),
                    'secondary'   => $supplier?->gst_number ?? '—',
                    'po_count'    => $pos->count(),
                    'total_spend' => $val,
                    'status_breakdown' => $pos->groupBy('status')->map->count()->all(),
                ]);
            }
        } elseif ($dimension === 'product') {
            $query = PurchaseOrderItem::with(['product', 'purchaseOrder'])
                ->whereHas('purchaseOrder', function ($poQ) use ($dateFrom, $dateTo) {
                    $poQ->whereNotIn('status', ['draft', 'cancelled', 'rejected']);
                    if ($dateFrom) {
                        $poQ->where('order_date', '>=', $dateFrom);
                    }
                    if ($dateTo) {
                        $poQ->where('order_date', '<=', $dateTo);
                    }
                });

            $items = $query->get();
            $grouped = $items->groupBy('product_id');

            foreach ($grouped as $productId => $pItems) {
                $product = $pItems->first()->product;
                $val = (float) $pItems->sum('total_cost');
                $totalSpend += $val;
                $rows->push([
                    'id'          => $productId,
                    'name'        => $product?->name ?? $pItems->first()->item_name,
                    'secondary'   => 'SKU: ' . ($product?->sku ?? '—'),
                    'ordered_qty' => $pItems->sum('quantity_ordered'),
                    'received_qty'=> $pItems->sum('quantity_received'),
                    'total_spend' => $val,
                ]);
            }
        } elseif ($dimension === 'project') {
            $query = PurchaseOrder::with(['project.client', 'supplier'])
                ->whereNotNull('project_id')
                ->whereNotIn('status', ['draft', 'cancelled', 'rejected']);

            if ($dateFrom) {
                $query->where('order_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->where('order_date', '<=', $dateTo);
            }

            $orders = $query->get();
            $grouped = $orders->groupBy('project_id');

            foreach ($grouped as $projectId => $pos) {
                $project = $pos->first()->project;
                $val = (float) $pos->sum('total');
                $totalSpend += $val;
                $rows->push([
                    'id'          => $projectId,
                    'name'        => $project?->title ?: ($project?->project_code ?? 'Project #' . $projectId),
                    'secondary'   => 'Client: ' . ($project?->company_name ?: ($project?->contact_person ?? '—')),
                    'po_count'    => $pos->count(),
                    'total_spend' => $val,
                ]);
            }
        }

        $rows = $rows->sortByDesc('total_spend')->values();

        return compact('dimension', 'rows', 'totalSpend', 'dateFrom', 'dateTo');
    }

    /**
     * Vendor Outstanding & Aging Report.
     */
    protected function getPayablesReport(): array
    {
        $summary = $this->apService->getAccountsPayableSummary();
        $suppliers = $this->apService->getSupplierPayablesList();

        return compact('summary', 'suppliers');
    }

    /**
     * Project Material Cost & Budget Report.
     */
    protected function getProjectMaterialCostReport(Request $request): array
    {
        $query = Project::with(['purchaseOrders'])->latest('id');

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'ilike', "%{$term}%")
                    ->orWhere('company_name', 'ilike', "%{$term}%")
                    ->orWhere('project_code', 'ilike', "%{$term}%");
            });
        }

        $projects = $query->paginate(20)->withQueryString();

        $rows = $projects->map(function (Project $p) {
            $materialBudget = (float) ($p->budget ?? 0.0);

            // Calculate actual issued materials from StockMovement
            $stockIssuedCost = (float) StockMovement::where('project_id', $p->id)
                ->where('movement_type', StockMovement::TYPE_ISSUE)
                ->sum(DB::raw('abs(qty) * unit_cost'));

            // Calculate PO Procurement Spend
            $poSpend = (float) $p->purchaseOrders
                ->whereNotIn('status', ['draft', 'cancelled', 'rejected'])
                ->sum('total');

            $actualCost = $stockIssuedCost > 0 ? $stockIssuedCost : $poSpend;
            $variance = $materialBudget > 0 ? ($materialBudget - $actualCost) : 0;

            return [
                'project'         => $p,
                'name'            => $p->title ?: ($p->project_code ?? 'Project #' . $p->id),
                'client_name'     => $p->company_name ?: ($p->contact_person ?? '—'),
                'budget'          => $materialBudget,
                'issued_cost'     => $stockIssuedCost,
                'po_spend'        => $poSpend,
                'actual_cost'     => $actualCost,
                'variance'        => $variance,
                'is_over_budget'  => $materialBudget > 0 && $actualCost > $materialBudget,
            ];
        });

        return compact('projects', 'rows');
    }

    /* ================= CSV EXPORTS (ADMIN ONLY) ================= */

    public function exportStockOnHandCsv(Request $request): StreamedResponse
    {
        $this->authorizeAdmin();

        $query = Product::query()->orderBy('name');
        if ($request->boolean('low_stock_only')) {
            $query->whereRaw('stock_quantity <= COALESCE(min_stock_alert, 5)');
        }
        $products = $query->get();

        $filename = 'stock_on_hand_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Product ID', 'Name', 'SKU', 'Category', 'Unit', 'Stock Qty', 'Min Level', 'Cost Price (INR)', 'Stock Valuation (INR)', 'Status']);

            foreach ($products as $p) {
                $isLow = $p->stock_quantity <= ($p->min_stock_alert ?: 5);
                fputcsv($handle, [
                    $p->id,
                    $p->name,
                    $p->sku,
                    $p->category,
                    $p->unit ?? 'Nos',
                    $p->stock_quantity,
                    $p->min_stock_alert ?? 5,
                    number_format((float) $p->cost_price, 2, '.', ''),
                    number_format((float) $p->stock_quantity * (float) $p->cost_price, 2, '.', ''),
                    $isLow ? 'LOW STOCK' : 'IN STOCK',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportPurchasesCsv(Request $request): StreamedResponse
    {
        $this->authorizeAdmin();

        $report = $this->getPurchasesReport($request);
        $dimension = $report['dimension'];
        $filename = 'purchases_by_' . $dimension . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($report, $dimension) {
            $handle = fopen('php://output', 'w');

            if ($dimension === 'supplier') {
                fputcsv($handle, ['Supplier Name', 'GSTIN', 'Total PO Count', 'Total Spend (INR)']);
                foreach ($report['rows'] as $r) {
                    fputcsv($handle, [$r['name'], $r['secondary'], $r['po_count'], number_format($r['total_spend'], 2, '.', '')]);
                }
            } elseif ($dimension === 'product') {
                fputcsv($handle, ['Product Name', 'SKU', 'Ordered Qty', 'Received Qty', 'Total Spend (INR)']);
                foreach ($report['rows'] as $r) {
                    fputcsv($handle, [$r['name'], $r['secondary'], $r['ordered_qty'], $r['received_qty'], number_format($r['total_spend'], 2, '.', '')]);
                }
            } else {
                fputcsv($handle, ['Project Name', 'Client', 'PO Count', 'Total Spend (INR)']);
                foreach ($report['rows'] as $r) {
                    fputcsv($handle, [$r['name'], $r['secondary'], $r['po_count'], number_format($r['total_spend'], 2, '.', '')]);
                }
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportVendorOutstandingCsv(Request $request): StreamedResponse
    {
        $this->authorizeAdmin();

        $suppliers = $this->apService->getSupplierPayablesList();
        $filename = 'vendor_outstanding_aging_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($suppliers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Supplier ID',
                'Supplier Name',
                'Total Invoiced (INR)',
                'Total Paid (INR)',
                'Gross Balance (INR)',
                'Debit Notes (INR)',
                'Net Payable (INR)',
                'Aging 0-30 Days',
                'Aging 31-60 Days',
                'Aging 61+ Days',
                'Aging Status',
            ]);

            foreach ($suppliers as $s) {
                fputcsv($handle, [
                    $s['supplier_id'],
                    $s['supplier_name'],
                    number_format($s['total_ordered_volume'], 2, '.', ''),
                    number_format($s['total_paid_volume'], 2, '.', ''),
                    number_format($s['gross_payable_balance'] ?? $s['balance_payable'], 2, '.', ''),
                    number_format($s['debit_notes_total'] ?? 0.0, 2, '.', ''),
                    number_format($s['balance_payable'], 2, '.', ''),
                    number_format($s['aging_0_30'] ?? 0.0, 2, '.', ''),
                    number_format($s['aging_31_60'] ?? 0.0, 2, '.', ''),
                    number_format(($s['aging_61_90'] ?? 0.0) + ($s['aging_90_plus'] ?? 0.0), 2, '.', ''),
                    $s['aging_status'] ?? 'Current',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportProjectMaterialCostCsv(Request $request): StreamedResponse
    {
        $this->authorizeAdmin();

        $data = $this->getProjectMaterialCostReport($request);
        $filename = 'project_material_cost_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Project ID', 'Project Name', 'Client', 'Status', 'Estimated Material Budget (INR)', 'Warehouse Issued Cost (INR)', 'PO Procurement Spend (INR)', 'Actual Cost (INR)', 'Variance (INR)', 'Budget Health']);

            foreach ($data['rows'] as $r) {
                fputcsv($handle, [
                    $r['project']->id,
                    $r['name'],
                    $r['client_name'],
                    $r['project']->status,
                    number_format($r['budget'], 2, '.', ''),
                    number_format($r['issued_cost'], 2, '.', ''),
                    number_format($r['po_spend'], 2, '.', ''),
                    number_format($r['actual_cost'], 2, '.', ''),
                    number_format($r['variance'], 2, '.', ''),
                    $r['is_over_budget'] ? 'OVER BUDGET' : 'WITHIN BUDGET',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function authorizeOffice(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isOffice()) {
            abort(403, 'Unauthorized access to procurement reports.');
        }
    }

    protected function authorizeAdmin(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'CSV exports are strictly restricted to Administrators.');
        }
    }
}

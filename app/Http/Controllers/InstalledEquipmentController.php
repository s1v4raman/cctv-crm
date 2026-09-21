<?php

namespace App\Http\Controllers;

use App\Models\InstalledEquipment;
use App\Models\InstallationJob;
use App\Models\Lead;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InstalledEquipmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeInternalOrTechnician();

        $search = trim((string) $request->input('search', ''));
        $leadId = $request->input('lead_id');
        $status = $request->input('status'); // active, under_repair, replaced, decommissioned
        $warrantyFilter = $request->input('warranty'); // all, active, expiring_soon, expired

        $query = InstalledEquipment::with(['lead', 'installationJob', 'product'])->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('mac_address', 'like', "%{$search}%")
                    ->orWhere('equipment_name', 'like', "%{$search}%")
                    ->orWhere('location_tag', 'like', "%{$search}%")
                    ->orWhereHas('lead', function ($leadQ) use ($search) {
                        $leadQ->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($prodQ) use ($search) {
                        $prodQ->where('name', 'like', "%{$search}%")
                            ->orWhere('model_no', 'like', "%{$search}%");
                    });
            });
        }

        if ($leadId) {
            $query->where('lead_id', $leadId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $now = Carbon::now()->toDateString();
        $thirtyDaysAhead = Carbon::now()->addDays(30)->toDateString();

        if ($warrantyFilter === 'active') {
            $query->where(function ($q) use ($thirtyDaysAhead) {
                $q->where('manufacturer_warranty_expiry', '>', $thirtyDaysAhead)
                    ->orWhere('service_warranty_expiry', '>', $thirtyDaysAhead);
            });
        } elseif ($warrantyFilter === 'expiring_soon') {
            $query->where(function ($q) use ($now, $thirtyDaysAhead) {
                $q->whereBetween('manufacturer_warranty_expiry', [$now, $thirtyDaysAhead])
                    ->orWhereBetween('service_warranty_expiry', [$now, $thirtyDaysAhead]);
            });
        } elseif ($warrantyFilter === 'expired') {
            $query->where(function ($q) use ($now) {
                $q->where(function ($sub) use ($now) {
                    $sub->where('manufacturer_warranty_expiry', '<', $now)
                        ->orWhereNull('manufacturer_warranty_expiry');
                })->where(function ($sub) use ($now) {
                    $sub->where('service_warranty_expiry', '<', $now)
                        ->orWhereNull('service_warranty_expiry');
                });
            });
        }

        $equipments = $query->paginate(20)->withQueryString();

        // Metrics
        $totalDevices = InstalledEquipment::count();
        $activeDevices = InstalledEquipment::where('status', 'active')->count();
        
        $expiringSoonCount = InstalledEquipment::where(function ($q) use ($now, $thirtyDaysAhead) {
            $q->whereBetween('manufacturer_warranty_expiry', [$now, $thirtyDaysAhead])
                ->orWhereBetween('service_warranty_expiry', [$now, $thirtyDaysAhead]);
        })->count();

        $expiredCount = InstalledEquipment::where(function ($q) use ($now) {
            $q->where('manufacturer_warranty_expiry', '<', $now)
                ->where('service_warranty_expiry', '<', $now);
        })->count();

        $leads = Lead::orderBy('customer_name')->get(['id', 'customer_name']);

        return view('equipment.index', compact(
            'equipments',
            'totalDevices',
            'activeDevices',
            'expiringSoonCount',
            'expiredCount',
            'leads'
        ));
    }

    public function create(Request $request): View
    {
        $this->authorizeInternalOrTechnician();

        $selectedLeadId = $request->input('lead_id');
        $selectedJobId = $request->input('job_id');

        $leads = Lead::orderBy('customer_name')->get();
        $jobs = InstallationJob::with('quotation.lead')->latest()->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('equipment.create', compact('leads', 'jobs', 'products', 'selectedLeadId', 'selectedJobId'));
    }

    public function store(Request $request, \App\Services\StockAlertService $stockAlertService): RedirectResponse
    {
        $this->authorizeInternalOrTechnician();

        $validated = $this->validateEquipment($request);

        $deductStock = $request->boolean('deduct_stock');
        $deductedProduct = null;

        DB::transaction(function () use ($validated, $deductStock, &$deductedProduct) {
            $equipment = InstalledEquipment::create($validated);

            if ($deductStock && !empty($validated['product_id'])) {
                $product = Product::find($validated['product_id']);
                if ($product && $product->stock_quantity > 0) {
                    $product->decrement('stock_quantity', 1);
                    $product->refresh();
                    $deductedProduct = $product;

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'job_installation',
                        'quantity' => -1,
                        'balance_after' => $product->stock_quantity,
                        'reference_type' => InstalledEquipment::class,
                        'reference_id' => $equipment->id,
                        'notes' => "Deployed at {$equipment->lead->customer_name} (S/N: {$equipment->serial_number})",
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        });

        if ($deductedProduct) {
            $stockAlertService->checkAndTriggerAlert($deductedProduct);
        }

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('status', 'Hardware/Equipment logged successfully.');
        }

        return redirect()->route('equipment.index')->with('status', 'Installed equipment registered successfully.');
    }

    public function show(InstalledEquipment $equipment): View
    {
        $user = auth()->user();
        if ($user && $user->isCustomer()) {
            $customerLead = $user->getCustomerLead();
            if (!$customerLead || $customerLead->id !== $equipment->lead_id) {
                abort(403, 'Unauthorized access to this equipment record.');
            }
        }

        $equipment->load(['lead', 'installationJob', 'product']);
        return view('equipment.show', compact('equipment'));
    }

    public function edit(InstalledEquipment $equipment): View
    {
        $this->authorizeInternalOrTechnician();

        $leads = Lead::orderBy('customer_name')->get();
        $jobs = InstallationJob::with('quotation.lead')->latest()->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('equipment.edit', compact('equipment', 'leads', 'jobs', 'products'));
    }

    public function update(Request $request, InstalledEquipment $equipment): RedirectResponse
    {
        $this->authorizeInternalOrTechnician();

        $validated = $this->validateEquipment($request, $equipment);

        $equipment->update($validated);

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('status', 'Equipment details updated successfully.');
        }

        return redirect()->route('equipment.index')->with('status', 'Equipment details updated successfully.');
    }

    public function destroy(InstalledEquipment $equipment): RedirectResponse
    {
        $equipment->delete();

        return back()->with('status', 'Equipment record deleted.');
    }

    private function authorizeInternalOrTechnician(): void
    {
        $user = auth()->user();
        if (!$user || $user->isCustomer()) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function validateEquipment(Request $request, ?InstalledEquipment $equipment = null): array
    {
        $validated = $request->validate([
            'lead_id' => ['required', 'exists:leads,id'],
            'installation_job_id' => ['nullable', 'exists:installation_jobs,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'equipment_name' => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'max:100'],
            'mac_address' => ['nullable', 'string', 'max:50'],
            'location_tag' => ['nullable', 'string', 'max:100'],
            'installation_date' => ['nullable', 'date'],
            'manufacturer_warranty_expiry' => ['nullable', 'date'],
            'service_warranty_expiry' => ['nullable', 'date'],
            'status' => ['required', 'in:active,under_repair,replaced,decommissioned'],
            'notes' => ['nullable', 'string'],
        ]);

        // Auto-calculate warranty dates if installation_date is provided and warranties are blank
        if (!empty($validated['installation_date'])) {
            $installDate = Carbon::parse($validated['installation_date']);

            if (empty($validated['manufacturer_warranty_expiry']) && !empty($validated['product_id'])) {
                $product = Product::find($validated['product_id']);
                $months = $product?->default_warranty_months ?? 24;
                $validated['manufacturer_warranty_expiry'] = $installDate->copy()->addMonths($months)->toDateString();
            }

            if (empty($validated['service_warranty_expiry'])) {
                // Default 12 months service warranty
                $validated['service_warranty_expiry'] = $installDate->copy()->addMonths(12)->toDateString();
            }
        }

        return $validated;
    }
}

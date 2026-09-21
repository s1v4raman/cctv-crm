<?php

namespace App\Http\Controllers;

use App\Models\InstalledEquipment;
use App\Models\Lead;
use App\Models\Product;
use App\Models\RmaClaim;
use App\Models\RmaStatusLog;
use App\Models\ServiceTicket;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RmaClaimController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status');
        $supplierId = $request->input('supplier_id');
        $warrantyFilter = $request->input('warranty');

        $query = RmaClaim::with(['supplier', 'product', 'installedEquipment', 'lead', 'creator'])
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('rma_no', 'like', "%{$search}%")
                    ->orWhere('faulty_serial_number', 'like', "%{$search}%")
                    ->orWhere('vendor_rma_ref', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%")
                    ->orWhereHas('lead', fn($l) => $l->where('customer_name', 'like', "%{$search}%"))
                    ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($warrantyFilter) {
            $query->where('warranty_status_at_claim', $warrantyFilter);
        }

        $claims = $query->paginate(15)->withQueryString();

        // Metrics Summary
        $stats = [
            'total'             => RmaClaim::count(),
            'active_in_process' => RmaClaim::whereIn('status', ['draft', 'shipped_to_vendor', 'in_vendor_repair'])->count(),
            'shipped'           => RmaClaim::where('status', 'shipped_to_vendor')->count(),
            'with_vendor'       => RmaClaim::where('status', 'in_vendor_repair')->count(),
            'resolved'          => RmaClaim::whereIn('status', ['replaced', 'repaired', 'credit_note', 'closed'])->count(),
        ];

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('rma.index', compact('claims', 'stats', 'suppliers'));
    }

    public function create(Request $request): View
    {
        $equipmentId = $request->input('equipment_id');
        $ticketId = $request->input('ticket_id');

        $prefilledEquipment = null;
        $prefilledTicket = null;
        $prefilledLead = null;
        $prefilledProduct = null;
        $warrantyStatus = 'under_warranty';

        if ($equipmentId) {
            $prefilledEquipment = InstalledEquipment::with(['product', 'lead'])->find($equipmentId);
            if ($prefilledEquipment) {
                $prefilledLead = $prefilledEquipment->lead;
                $prefilledProduct = $prefilledEquipment->product;
                if ($prefilledEquipment->mfg_warranty_status === 'expired') {
                    $warrantyStatus = 'out_of_warranty';
                }
            }
        }

        if ($ticketId) {
            $prefilledTicket = ServiceTicket::with(['lead'])->find($ticketId);
            if ($prefilledTicket && !$prefilledLead) {
                $prefilledLead = $prefilledTicket->lead;
            }
        }

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $equipmentList = InstalledEquipment::with(['lead', 'product'])->where('status', '!=', 'decommissioned')->get();
        $tickets = ServiceTicket::whereIn('status', ['open', 'in_progress', 'assigned'])->get();
        $leads = Lead::orderBy('customer_name')->get();

        return view('rma.create', compact(
            'suppliers',
            'products',
            'equipmentList',
            'tickets',
            'leads',
            'prefilledEquipment',
            'prefilledTicket',
            'prefilledLead',
            'prefilledProduct',
            'warrantyStatus'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id'              => ['required', 'exists:suppliers,id'],
            'product_id'               => ['nullable', 'exists:products,id'],
            'installed_equipment_id'   => ['nullable', 'exists:installed_equipment,id'],
            'service_ticket_id'        => ['nullable', 'exists:service_tickets,id'],
            'lead_id'                  => ['nullable', 'exists:leads,id'],
            'faulty_serial_number'     => ['required', 'string', 'max:100'],
            'faulty_mac_address'       => ['nullable', 'string', 'max:50'],
            'fault_category'           => ['required', 'string', 'max:50'],
            'warranty_status_at_claim' => ['required', 'in:under_warranty,out_of_warranty,extended_warranty'],
            'issue_description'        => ['required', 'string', 'max:2000'],
            'vendor_rma_ref'           => ['nullable', 'string', 'max:100'],
            'expected_return_date'     => ['nullable', 'date'],
        ]);

        $claim = DB::transaction(function () use ($validated) {
            $rmaNo = RmaClaim::generateRmaNo();

            $claim = RmaClaim::create([
                'rma_no'                   => $rmaNo,
                'supplier_id'              => $validated['supplier_id'],
                'product_id'               => $validated['product_id'] ?? null,
                'installed_equipment_id'   => $validated['installed_equipment_id'] ?? null,
                'service_ticket_id'        => $validated['service_ticket_id'] ?? null,
                'lead_id'                  => $validated['lead_id'] ?? null,
                'faulty_serial_number'     => $validated['faulty_serial_number'],
                'faulty_mac_address'       => $validated['faulty_mac_address'] ?? null,
                'fault_category'           => $validated['fault_category'],
                'warranty_status_at_claim' => $validated['warranty_status_at_claim'],
                'issue_description'        => $validated['issue_description'],
                'vendor_rma_ref'           => $validated['vendor_rma_ref'] ?? null,
                'expected_return_date'     => $validated['expected_return_date'] ?? null,
                'status'                   => 'draft',
                'created_by'               => Auth::id(),
            ]);

            // If linked to InstalledEquipment, set status to under_repair
            if (!empty($validated['installed_equipment_id'])) {
                $equipment = InstalledEquipment::find($validated['installed_equipment_id']);
                if ($equipment) {
                    $equipment->update(['status' => 'under_repair']);
                }
            }

            // Record initial audit log
            $claim->statusLogs()->create([
                'from_status' => null,
                'to_status'   => 'draft',
                'notes'       => 'RMA claim registered in system.',
                'changed_by'  => Auth::id(),
            ]);

            return $claim;
        });

        return redirect()->route('rma.show', $claim)
            ->with('status', "✅ RMA Claim {$claim->rma_no} created successfully.");
    }

    public function show(RmaClaim $rma): View
    {
        $rma->load([
            'supplier',
            'product',
            'installedEquipment.lead',
            'serviceTicket',
            'lead',
            'creator',
            'statusLogs.changedBy',
        ]);

        return view('rma.show', compact('rma'));
    }

    public function edit(RmaClaim $rma): View
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $leads = Lead::orderBy('customer_name')->get();

        return view('rma.edit', compact('rma', 'suppliers', 'products', 'leads'));
    }

    public function update(Request $request, RmaClaim $rma): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id'              => ['required', 'exists:suppliers,id'],
            'product_id'               => ['nullable', 'exists:products,id'],
            'faulty_serial_number'     => ['required', 'string', 'max:100'],
            'faulty_mac_address'       => ['nullable', 'string', 'max:50'],
            'fault_category'           => ['required', 'string', 'max:50'],
            'warranty_status_at_claim' => ['required', 'in:under_warranty,out_of_warranty,extended_warranty'],
            'issue_description'        => ['required', 'string', 'max:2000'],
            'vendor_rma_ref'           => ['nullable', 'string', 'max:100'],
            'expected_return_date'     => ['nullable', 'date'],
        ]);

        $rma->update($validated);

        return redirect()->route('rma.show', $rma)
            ->with('status', "RMA Claim {$rma->rma_no} updated successfully.");
    }

    public function dispatchToVendor(Request $request, RmaClaim $rma): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_courier'     => ['required', 'string', 'max:100'],
            'tracking_number'      => ['nullable', 'string', 'max:100'],
            'dispatched_date'      => ['required', 'date'],
            'vendor_rma_ref'       => ['nullable', 'string', 'max:100'],
            'expected_return_date' => ['nullable', 'date'],
            'notes'                => ['nullable', 'string', 'max:500'],
        ]);

        $oldStatus = $rma->status;

        $rma->update([
            'status'               => 'shipped_to_vendor',
            'shipping_courier'     => $validated['shipping_courier'],
            'tracking_number'      => $validated['tracking_number'] ?? $rma->tracking_number,
            'dispatched_date'      => $validated['dispatched_date'],
            'vendor_rma_ref'       => $validated['vendor_rma_ref'] ?? $rma->vendor_rma_ref,
            'expected_return_date' => $validated['expected_return_date'] ?? $rma->expected_return_date,
        ]);

        $rma->statusLogs()->create([
            'from_status' => $oldStatus,
            'to_status'   => 'shipped_to_vendor',
            'notes'       => "Dispatched via {$validated['shipping_courier']}" . ($validated['tracking_number'] ? " (AWB: {$validated['tracking_number']})" : '') . ($validated['notes'] ? " - {$validated['notes']}" : ''),
            'changed_by'  => Auth::id(),
        ]);

        return redirect()->route('rma.show', $rma)
            ->with('status', "✅ RMA {$rma->rma_no} marked as Shipped to Vendor.");
    }

    public function updateVendorStatus(Request $request, RmaClaim $rma): RedirectResponse
    {
        $validated = $request->validate([
            'status'         => ['required', 'in:in_vendor_repair,credit_note,rejected,closed'],
            'vendor_rma_ref' => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $rma->status;

        $rma->update([
            'status'         => $validated['status'],
            'vendor_rma_ref' => $validated['vendor_rma_ref'] ?? $rma->vendor_rma_ref,
        ]);

        $rma->statusLogs()->create([
            'from_status' => $oldStatus,
            'to_status'   => $validated['status'],
            'notes'       => $validated['notes'] ?? "Status updated to " . RmaClaim::statusLabels()[$validated['status']],
            'changed_by'  => Auth::id(),
        ]);

        return redirect()->route('rma.show', $rma)
            ->with('status', "RMA status updated to " . RmaClaim::statusLabels()[$validated['status']]);
    }

    public function recordResolution(Request $request, RmaClaim $rma): RedirectResponse
    {
        $validated = $request->validate([
            'resolution_type'             => ['required', 'in:replacement,repaired_unit,credit_note,rejected_damage'],
            'replacement_serial_number'   => ['nullable', 'required_if:resolution_type,replacement', 'string', 'max:100'],
            'replacement_mac_address'     => ['nullable', 'string', 'max:50'],
            'replacement_warranty_expiry' => ['nullable', 'date'],
            'vendor_repair_notes'         => ['nullable', 'string', 'max:1000'],
            'service_cost'                => ['nullable', 'numeric', 'min:0'],
            'received_from_vendor_date'   => ['required', 'date'],
            'auto_update_equipment'       => ['nullable', 'boolean'],
        ]);

        $oldStatus = $rma->status;

        DB::transaction(function () use ($validated, $rma, $oldStatus) {
            $newStatus = match ($validated['resolution_type']) {
                'replacement'     => 'replaced',
                'repaired_unit'   => 'repaired',
                'credit_note'     => 'credit_note',
                'rejected_damage' => 'rejected',
            };

            $rma->update([
                'status'                      => $newStatus,
                'resolution_type'             => $validated['resolution_type'],
                'replacement_serial_number'   => $validated['replacement_serial_number'] ?? null,
                'replacement_mac_address'     => $validated['replacement_mac_address'] ?? null,
                'replacement_warranty_expiry' => $validated['replacement_warranty_expiry'] ?? null,
                'vendor_repair_notes'         => $validated['vendor_repair_notes'] ?? null,
                'service_cost'                => (float) ($validated['service_cost'] ?? 0),
                'received_from_vendor_date'   => $validated['received_from_vendor_date'],
            ]);

            // Auto-update Installed Equipment if applicable
            if ($rma->installed_equipment_id && $rma->installedEquipment) {
                $equipment = $rma->installedEquipment;

                if ($validated['resolution_type'] === 'replacement') {
                    // Update equipment with the replacement unit's serial number and warranty
                    $equipment->update([
                        'serial_number'                => $validated['replacement_serial_number'],
                        'mac_address'                  => $validated['replacement_mac_address'] ?? $equipment->mac_address,
                        'manufacturer_warranty_expiry' => $validated['replacement_warranty_expiry'] ?? $equipment->manufacturer_warranty_expiry,
                        'status'                       => 'active',
                        'notes'                        => ($equipment->notes ? $equipment->notes . "\n" : '') . "Replaced under RMA {$rma->rma_no} (Original S/N: {$rma->faulty_serial_number}) on " . now()->format('d M Y'),
                    ]);
                } elseif ($validated['resolution_type'] === 'repaired_unit') {
                    $equipment->update([
                        'status' => 'active',
                        'notes'  => ($equipment->notes ? $equipment->notes . "\n" : '') . "Repaired by vendor under RMA {$rma->rma_no} on " . now()->format('d M Y'),
                    ]);
                }
            }

            // Audit log
            $noteParts = ["Vendor resolution recorded: " . ucfirst(str_replace('_', ' ', $validated['resolution_type']))];
            if (!empty($validated['replacement_serial_number'])) {
                $noteParts[] = "(New S/N: {$validated['replacement_serial_number']})";
            }
            if (!empty($validated['vendor_repair_notes'])) {
                $noteParts[] = "- {$validated['vendor_repair_notes']}";
            }

            $rma->statusLogs()->create([
                'from_status' => $oldStatus,
                'to_status'   => $newStatus,
                'notes'       => implode(' ', $noteParts),
                'changed_by'  => Auth::id(),
            ]);
        });

        return redirect()->route('rma.show', $rma)
            ->with('status', "✅ Vendor Resolution successfully recorded for RMA {$rma->rma_no}.");
    }

    public function downloadDispatchChallan(RmaClaim $rma)
    {
        $rma->load(['supplier', 'product', 'installedEquipment.lead', 'lead', 'creator']);

        $pdf = Pdf::loadView('rma.dispatch_pdf', compact('rma'));

        return $pdf->download("{$rma->rma_no}_Vendor_Delivery_Challan.pdf");
    }

    public function destroy(RmaClaim $rma): RedirectResponse
    {
        $rmaNo = $rma->rma_no;
        $rma->delete();

        return redirect()->route('rma.index')
            ->with('status', "RMA Claim {$rmaNo} removed.");
    }
}

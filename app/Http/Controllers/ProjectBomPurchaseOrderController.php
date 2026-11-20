<?php

namespace App\Http\Controllers;

use App\Helpers\GstHelper;
use App\Models\GatewaySetting;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Services\DocumentSequenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectBomPurchaseOrderController extends Controller
{
    /**
     * Preview eligible BOM items for Purchase Order creation.
     */
    public function preview(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $materials = $project->materials()
            ->with(['product', 'supplier'])
            ->get();

        $eligibleItems = [];
        $suppliers = Supplier::where('is_active', true)
            ->select('id', 'name', 'company_name', 'type', 'status', 'gst_number')
            ->orderBy('name')
            ->get();

        foreach ($materials as $mat) {
            $currentStock = $mat->product ? (int) $mat->product->stock_quantity : 0;
            $requiredQty = (float) $mat->quantity;
            $isLocalPurchase = in_array($mat->source, ['local_purchase', 'bought_from_shop']);
            $isShortage = $mat->product && ($currentStock < $requiredQty);

            // Item is eligible if it is local_purchase OR warehouse stock is below required quantity
            if ($isLocalPurchase || $isShortage) {
                // Determine recommended order quantity: for shortage, order the difference; for local purchase, order full requirement
                $shortageQty = max(0, $requiredQty - $currentStock);
                $recommendedQty = $isLocalPurchase ? $requiredQty : $shortageQty;
                if ($recommendedQty <= 0) {
                    $recommendedQty = $requiredQty;
                }

                $unitCost = (float) ($mat->unit_cost ?: ($mat->product?->cost_price ?: 0.0));
                $taxRate = (float) ($mat->product?->gst_rate ?: 18.0);

                $eligibleItems[] = [
                    'id'               => $mat->id,
                    'item_name'        => $mat->item_name,
                    'product_id'       => $mat->product_id,
                    'product_sku'      => $mat->product?->sku,
                    'source'           => $mat->source,
                    'source_label'     => $mat->source_label,
                    'required_quantity'=> $requiredQty,
                    'stock_on_hand'    => $currentStock,
                    'order_quantity'   => $recommendedQty,
                    'unit'             => $mat->unit ?: 'Nos',
                    'unit_cost'        => $unitCost,
                    'tax_rate'         => $taxRate,
                    'supplier_id'      => $mat->supplier_id,
                    'supplier_name'    => $mat->supplier?->company_name ?: ($mat->supplier?->name ?: $mat->shop_name),
                    'shop_name'        => $mat->shop_name,
                    'is_local_purchase'=> $isLocalPurchase,
                    'is_shortage'      => $isShortage,
                ];
            }
        }

        return response()->json([
            'success'        => true,
            'project_id'     => $project->id,
            'project_code'   => $project->project_code,
            'project_title'  => $project->title,
            'eligible_count' => count($eligibleItems),
            'items'          => $eligibleItems,
            'suppliers'      => $suppliers,
        ]);
    }

    /**
     * Create Draft Purchase Orders from selected BOM line items grouped by supplier.
     */
    public function generate(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.bom_item_id'       => ['required', 'exists:project_materials,id'],
            'items.*.supplier_id'       => ['required', 'exists:suppliers,id'],
            'items.*.quantity'          => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_cost'         => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate'          => ['nullable', 'numeric', 'min:0'],
            'expected_delivery_date'    => ['nullable', 'date', 'after_or_equal:today'],
            'notes'                     => ['nullable', 'string', 'max:500'],
        ]);

        $settings = GatewaySetting::first();
        $companyStateCode = $settings?->company_state_code ?: '33';
        $seqService = app(DocumentSequenceService::class);

        // Group selected items by supplier
        $grouped = [];
        foreach ($validated['items'] as $itemData) {
            $grouped[$itemData['supplier_id']][] = $itemData;
        }

        $createdPOs = [];

        DB::transaction(function () use ($grouped, $project, $validated, $companyStateCode, $seqService, &$createdPOs) {
            foreach ($grouped as $supplierId => $items) {
                $supplier = Supplier::findOrFail($supplierId);
                $vendorStateCode = $supplier->gst_number ? GstHelper::extractStateCode($supplier->gst_number) : null;

                $poNumber = $seqService->nextPurchaseOrderNumber();

                $subtotal = 0.0;
                $cgstTotal = 0.0;
                $sgstTotal = 0.0;
                $igstTotal = 0.0;

                // Precompute totals
                foreach ($items as $it) {
                    $qty = (float) $it['quantity'];
                    $cost = (float) $it['unit_cost'];
                    $taxRate = (float) ($it['tax_rate'] ?? 18.0);

                    $lineSubtotal = round($qty * $cost, 2);
                    $subtotal += $lineSubtotal;

                    $taxSplit = GstHelper::calculateTaxSplit($lineSubtotal, $taxRate, $vendorStateCode, $companyStateCode);
                    $cgstTotal += $taxSplit['cgst_amount'];
                    $sgstTotal += $taxSplit['sgst_amount'];
                    $igstTotal += $taxSplit['igst_amount'];
                }

                $taxAmount = round($cgstTotal + $sgstTotal + $igstTotal, 2);
                $grandTotal = round($subtotal + $taxAmount, 2);

                $po = PurchaseOrder::create([
                    'supplier_id'           => $supplierId,
                    'project_id'            => $project->id,
                    'po_number'             => $poNumber,
                    'order_date'            => now()->toDateString(),
                    'expected_delivery_date'=> $validated['expected_delivery_date'] ?? now()->addDays(7)->toDateString(),
                    'status'                => PurchaseOrder::STATUS_DRAFT,
                    'subtotal'              => $subtotal,
                    'tax_percent'           => 18.00,
                    'tax_amount'            => $taxAmount,
                    'cgst_amount'           => $cgstTotal,
                    'sgst_amount'           => $sgstTotal,
                    'igst_amount'           => $igstTotal,
                    'total'                 => $grandTotal,
                    'payment_status'        => 'unpaid',
                    'amount_paid'           => 0.00,
                    'notes'                 => "Auto-generated from Project BOM #{$project->project_code} ({$project->title}). " . ($validated['notes'] ?? ''),
                    'created_by'            => Auth::id(),
                ]);

                // Create line items
                foreach ($items as $it) {
                    $bomMaterial = ProjectMaterial::with('product')->findOrFail($it['bom_item_id']);
                    $qty = (float) $it['quantity'];
                    $cost = (float) $it['unit_cost'];
                    $taxRate = (float) ($it['tax_rate'] ?? 18.0);
                    $lineSubtotal = round($qty * $cost, 2);

                    $taxSplit = GstHelper::calculateTaxSplit($lineSubtotal, $taxRate, $vendorStateCode, $companyStateCode);

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id'        => $bomMaterial->product_id,
                        'bom_item_id'       => $bomMaterial->id,
                        'item_name'         => $bomMaterial->item_name,
                        'sku'               => $bomMaterial->product?->sku,
                        'hsn_code'          => $bomMaterial->product?->hsn_code ?? '85258900',
                        'quantity_ordered'  => (int) ceil($qty),
                        'quantity_received' => 0,
                        'unit_cost'         => $cost,
                        'total_cost'        => $lineSubtotal + $taxSplit['total_tax'],
                        'tax_rate'          => $taxRate,
                        'cgst_rate'         => $taxSplit['cgst_rate'],
                        'cgst_amount'       => $taxSplit['cgst_amount'],
                        'sgst_rate'         => $taxSplit['sgst_rate'],
                        'sgst_amount'       => $taxSplit['sgst_amount'],
                        'igst_rate'         => $taxSplit['igst_rate'],
                        'igst_amount'       => $taxSplit['igst_amount'],
                    ]);

                    // Update BOM material assigned supplier if not set
                    if (empty($bomMaterial->supplier_id)) {
                        $bomMaterial->update(['supplier_id' => $supplierId]);
                    }
                }

                $createdPOs[] = $po;
            }
        });

        $poCount = count($createdPOs);
        $poNumbers = implode(', ', array_map(fn($p) => $p->po_number, $createdPOs));
        $msg = "Generated {$poCount} draft Purchase Order(s) from BOM: {$poNumbers}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'purchase_orders' => $createdPOs,
            ]);
        }

        return redirect()->route('projects.show', ['project' => $project, 'tab' => 'materials'])
            ->with('status', $msg);
    }
}

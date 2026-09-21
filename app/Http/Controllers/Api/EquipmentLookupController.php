<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstalledEquipment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentLookupController extends Controller
{
    /**
     * Search and lookup installed equipment or product catalog by barcode / serial number.
     */
    public function lookup(Request $request): JsonResponse
    {
        $serial = trim((string) $request->input('serial', ''));
        $mac = trim((string) $request->input('mac', ''));

        if ($serial === '' && $mac === '') {
            return response()->json([
                'found'   => false,
                'message' => 'Please provide a serial number or MAC address to search.',
            ], 400);
        }

        $query = InstalledEquipment::with(['lead', 'product', 'installationJob']);

        if ($serial !== '') {
            $query->where('serial_number', $serial);
        } elseif ($mac !== '') {
            $query->where('mac_address', $mac);
        }

        $equipment = $query->first();

        if ($equipment) {
            $now = Carbon::now();
            $mfgWarranty = $equipment->manufacturer_warranty_expiry ? Carbon::parse($equipment->manufacturer_warranty_expiry) : null;
            $svcWarranty = $equipment->service_warranty_expiry ? Carbon::parse($equipment->service_warranty_expiry) : null;

            $isUnderWarranty = ($mfgWarranty && $mfgWarranty->isFuture()) || ($svcWarranty && $svcWarranty->isFuture());

            return response()->json([
                'found'               => true,
                'type'                => 'installed_equipment',
                'id'                  => $equipment->id,
                'equipment_name'      => $equipment->equipment_name,
                'serial_number'       => $equipment->serial_number,
                'mac_address'         => $equipment->mac_address,
                'status'              => $equipment->status,
                'location_tag'        => $equipment->location_tag,
                'lead_id'             => $equipment->lead_id,
                'customer_name'       => $equipment->lead?->customer_name ?? 'N/A',
                'customer_phone'      => $equipment->lead?->phone ?? 'N/A',
                'customer_address'    => $equipment->lead?->site_address ?? 'N/A',
                'product_name'        => $equipment->product?->name ?? 'N/A',
                'product_model'       => $equipment->product?->model_no ?? 'N/A',
                'installation_date'   => $equipment->installation_date ? Carbon::parse($equipment->installation_date)->format('d M Y') : null,
                'mfg_warranty_expiry' => $mfgWarranty ? $mfgWarranty->format('d M Y') : 'N/A',
                'svc_warranty_expiry' => $svcWarranty ? $svcWarranty->format('d M Y') : 'N/A',
                'is_under_warranty'   => $isUnderWarranty,
                'rma_url'             => route('rma.create', ['equipment_id' => $equipment->id]),
                'show_url'            => route('equipment.show', $equipment),
            ]);
        }

        // If not found in installed equipment, search if this barcode matches a product SKU or Model No
        $product = Product::where('sku', $serial)
            ->orWhere('model_no', $serial)
            ->orWhere('sku', 'like', "%{$serial}%")
            ->first();

        if ($product) {
            return response()->json([
                'found'           => true,
                'type'            => 'catalog_product',
                'id'              => $product->id,
                'product_name'    => $product->name,
                'sku'             => $product->sku,
                'model_no'        => $product->model_no,
                'category'        => $product->category,
                'stock_quantity'  => $product->stock_quantity,
                'unit_price'      => $product->unit_price,
                'warranty_months' => $product->default_warranty_months,
            ]);
        }

        return response()->json([
            'found'   => false,
            'message' => "No installed asset or catalog product found for barcode: '{$serial}'",
            'serial'  => $serial,
        ]);
    }
}

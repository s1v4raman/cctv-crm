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
     * Search and lookup installed equipment or product catalog by barcode / serial number / MAC address.
     */
    public function lookup(Request $request): JsonResponse
    {
        $serial = trim((string) $request->input('serial', ''));
        $mac = trim((string) $request->input('mac', ''));
        $model = trim((string) $request->input('model', ''));

        // Handle composite or prefixed barcodes (e.g. "SN:12345,MAC:AA:BB:CC:DD:EE:FF")
        if ($serial !== '') {
            if (preg_match('/(?:S\/N|SN|SERIAL)[:\s=_-]*([A-Za-z0-9_-]+)/i', $serial, $snMatch)) {
                $cleanedSerial = $snMatch[1];
            } else {
                $cleanedSerial = preg_replace('/^(?:S\/N|SN|SERIAL)[:\s=_-]*/i', '', $serial);
            }

            if ($mac === '' && preg_match('/(?:MAC[:\s=_-]*)?([0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2})/i', $serial, $macMatch)) {
                $mac = $macMatch[1];
            }

            // If the serial input is actually a standalone MAC address, shift it to MAC
            if (preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $cleanedSerial)) {
                $mac = strtoupper(str_replace('-', ':', $cleanedSerial));
                $cleanedSerial = '';
            } elseif (preg_match('/^[0-9A-Fa-f]{12}$/', $cleanedSerial) && preg_match('/[A-Fa-f]/', $cleanedSerial)) {
                $mac = strtoupper(implode(':', str_split($cleanedSerial, 2)));
                $cleanedSerial = '';
            }

            $serial = $cleanedSerial;
        }

        if ($mac !== '') {
            $mac = preg_replace('/^MAC[:\s=_-]*/i', '', $mac);
            $mac = strtoupper(str_replace('-', ':', $mac));
        }

        if ($serial === '' && $mac === '' && $model === '') {
            return response()->json([
                'found'   => false,
                'message' => 'Please provide a serial number, MAC address, or model to search.',
            ], 400);
        }

        $query = InstalledEquipment::with(['lead', 'product', 'installationJob']);

        if ($serial !== '' && $mac !== '') {
            $query->where(function ($q) use ($serial, $mac) {
                $q->where('serial_number', $serial)
                  ->orWhere('mac_address', $mac);
            });
        } elseif ($serial !== '') {
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
                'product_id'          => $equipment->product_id,
                'equipment_name'      => $equipment->equipment_name,
                'serial_number'       => $equipment->serial_number,
                'mac_address'         => $equipment->mac_address,
                'status'              => $equipment->status,
                'location_tag'        => $equipment->location_tag,
                'lead_id'             => $equipment->lead_id,
                'customer_name'       => $equipment->lead?->customer_name ?? 'N/A',
                'customer_phone'      => $equipment->lead?->phone ?? 'N/A',
                'customer_address'    => $equipment->lead?->site_address ?? 'N/A',
                'product_name'        => $equipment->product?->name ?? $equipment->equipment_name,
                'product_model'       => $equipment->product?->model_no ?? '',
                'installation_date'   => $equipment->installation_date ? Carbon::parse($equipment->installation_date)->format('d M Y') : null,
                'mfg_warranty_expiry' => $mfgWarranty ? $mfgWarranty->format('d M Y') : 'N/A',
                'svc_warranty_expiry' => $svcWarranty ? $svcWarranty->format('d M Y') : 'N/A',
                'is_under_warranty'   => $isUnderWarranty,
                'show_url'            => route('equipment.show', $equipment),
            ]);
        }

        // If not found in installed equipment, search if this barcode matches a product SKU, Model No, or Name
        $searchKey = $serial ?: $mac;
        $product = null;

        if ($model !== '') {
            $product = Product::where('model_no', $model)
                ->orWhere('model_no', 'like', "%{$model}%")
                ->orWhere('name', 'like', "%{$model}%")
                ->first();
        }

        if (!$product && $searchKey !== '') {
            $product = Product::where('sku', $searchKey)
                ->orWhere('model_no', $searchKey)
                ->orWhere('name', $searchKey)
                ->orWhere('sku', 'like', "%{$searchKey}%")
                ->orWhere('model_no', 'like', "%{$searchKey}%")
                ->first();
        }

        if ($product) {
            $displayName = $product->name . ($product->model_no ? " ({$product->model_no})" : '');
            return response()->json([
                'found'           => true,
                'type'            => 'catalog_product',
                'id'              => $product->id,
                'product_id'      => $product->id,
                'product_name'    => $product->name,
                'equipment_name'  => $displayName,
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
            'message' => "No installed asset or catalog product found for barcode: '{$searchKey}'",
            'serial'  => $serial,
            'mac'     => $mac,
        ]);
    }
}

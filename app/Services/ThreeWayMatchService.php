<?php

namespace App\Services;

use App\Models\GatewaySetting;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\VendorBill;
use App\Models\VendorBillItem;

class ThreeWayMatchService
{
    /**
     * Verify a vendor bill against its purchase order and goods receipts.
     * Enforces the 3-Way Match rule:
     *  1. Billed quantity must not exceed received/accepted quantity from Goods Receipts.
     *  2. Billed price must equal PO price within price tolerance.
     *
     * @param VendorBill $bill
     * @return array{matched: bool, match_status: string, status: string, notes: string, differences: array}
     */
    public function verifyBill(VendorBill $bill): array
    {
        if (!$bill->purchase_order_id) {
            $notes = 'Direct non-PO bill: 3-way match not required.';
            $bill->update([
                'match_status' => VendorBill::MATCH_NOT_CHECKED,
                'status'       => VendorBill::STATUS_APPROVED,
                'match_notes'  => $notes,
            ]);

            return [
                'matched'      => true,
                'match_status' => VendorBill::MATCH_NOT_CHECKED,
                'status'       => VendorBill::STATUS_APPROVED,
                'notes'        => $notes,
                'differences'  => [],
            ];
        }

        $po = PurchaseOrder::with(['items', 'goodsReceipts.items'])->find($bill->purchase_order_id);
        if (!$po) {
            $notes = 'Linked Purchase Order not found.';
            $bill->update([
                'match_status' => VendorBill::MATCH_MISMATCH,
                'status'       => VendorBill::STATUS_ON_HOLD,
                'match_notes'  => $notes,
            ]);

            return [
                'matched'      => false,
                'match_status' => VendorBill::MATCH_MISMATCH,
                'status'       => VendorBill::STATUS_ON_HOLD,
                'notes'        => $notes,
                'differences'  => [$notes],
            ];
        }

        $settings = GatewaySetting::first();
        $priceTolerance = (float) ($settings?->po_receiving_tolerance_percent ?? 0.0);

        $bill->loadMissing('items');
        $differences = [];

        foreach ($bill->items as $billItem) {
            // Locate corresponding PO line item
            /** @var PurchaseOrderItem|null $poItem */
            $poItem = null;
            if ($billItem->purchase_order_item_id) {
                $poItem = $po->items->firstWhere('id', $billItem->purchase_order_item_id);
            } elseif ($billItem->product_id) {
                $poItem = $po->items->firstWhere('product_id', $billItem->product_id);
            } else {
                $poItem = $po->items->first(fn($i) => strcasecmp($i->item_name, $billItem->item_name) === 0);
            }

            if (!$poItem) {
                $differences[] = "Line item '{$billItem->item_name}' was not found on PO #{$po->po_number}.";
                continue;
            }

            // 1. Quantity Verification: Billed qty must not exceed accepted quantity from Goods Receipts
            $totalReceived = (float) GoodsReceiptItem::where('purchase_order_item_id', $poItem->id)->sum('qty_received');
            if ($totalReceived <= 0 && $poItem->quantity_received > 0) {
                $totalReceived = (float) $poItem->quantity_received;
            }

            $billedQty = (float) $billItem->quantity;
            if ($billedQty > ($totalReceived + 0.001)) {
                $differences[] = "Quantity mismatch on '{$billItem->item_name}': Billed {$billedQty} unit(s) exceeds accepted goods receipt quantity of {$totalReceived} unit(s) (PO ordered: {$poItem->quantity_ordered}).";
            }

            // 2. Price Verification: Billed rate must equal PO unit cost within tolerance
            $poPrice = (float) $poItem->unit_cost;
            $billedPrice = (float) $billItem->unit_cost;
            $maxAllowedPrice = $poPrice * (1.0 + ($priceTolerance / 100.0)) + 0.001;

            if ($billedPrice > $maxAllowedPrice) {
                $toleranceNote = $priceTolerance > 0 ? " (tolerance: {$priceTolerance}%)" : "";
                $differences[] = "Price mismatch on '{$billItem->item_name}': Billed rate ₹" . number_format($billedPrice, 2) . " exceeds PO agreed rate ₹" . number_format($poPrice, 2) . "{$toleranceNote}.";
            }
        }

        if (empty($bill->items) || $bill->items->isEmpty()) {
            $differences[] = 'Bill contains no line items to match against PO.';
        }

        if (count($differences) > 0) {
            $matchStatus = VendorBill::MATCH_MISMATCH;
            $billStatus  = VendorBill::STATUS_ON_HOLD;
            $notes       = implode("\n", $differences);
            $matched     = false;
        } else {
            $matchStatus = VendorBill::MATCH_MATCHED;
            $billStatus  = VendorBill::STATUS_APPROVED;
            $notes       = "3-Way Match Verified (PO #{$po->po_number}, Goods Receipts, and Vendor Invoice matched).";
            $matched     = true;
        }

        $bill->update([
            'match_status' => $matchStatus,
            'status'       => $billStatus,
            'match_notes'  => $notes,
        ]);

        return [
            'matched'      => $matched,
            'match_status' => $matchStatus,
            'status'       => $billStatus,
            'notes'        => $notes,
            'differences'  => $differences,
        ];
    }
}

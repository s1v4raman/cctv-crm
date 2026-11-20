<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\SupplierDebitNote;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseReturnService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Create a purchase return draft with items.
     */
    public function createReturn(array $data, int $userId): PurchaseReturn
    {
        return DB::transaction(function () use ($data, $userId) {
            /** @var GoodsReceipt $receipt */
            $receipt = GoodsReceipt::with(['purchaseOrder', 'supplier'])->findOrFail($data['goods_receipt_id']);

            $returnDate = $data['return_date'] ?? now()->format('Y-m-d');
            $returnNumber = DocumentSequenceService::nextPurchaseReturnNumber($returnDate);

            $purchaseReturn = PurchaseReturn::create([
                'return_number'     => $returnNumber,
                'goods_receipt_id'  => $receipt->id,
                'purchase_order_id' => $receipt->purchase_order_id,
                'supplier_id'       => $receipt->supplier_id,
                'return_date'       => $returnDate,
                'reason'            => $data['reason'] ?? null,
                'notes'             => $data['notes'] ?? null,
                'status'            => PurchaseReturn::STATUS_DRAFT,
                'subtotal'          => 0,
                'tax_amount'        => 0,
                'total_amount'      => 0,
                'created_by'        => $userId,
            ]);

            $subtotal = 0;
            $taxAmount = 0;

            foreach ($data['items'] as $itemData) {
                $qty = (int) ($itemData['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $grnItem = null;
                if (!empty($itemData['goods_receipt_item_id'])) {
                    $grnItem = GoodsReceiptItem::find($itemData['goods_receipt_item_id']);
                }

                $productId = $itemData['product_id'] ?? $grnItem?->product_id;
                $unitCost = (float) ($itemData['unit_cost'] ?? ($grnItem?->unit_cost ?? 0.0));
                $taxRate = (float) ($itemData['tax_rate'] ?? 18.0);

                $lineSubtotal = round($qty * $unitCost, 2);
                $lineTax = round($lineSubtotal * ($taxRate / 100), 2);
                $lineTotal = round($lineSubtotal + $lineTax, 2);

                $purchaseReturn->items()->create([
                    'goods_receipt_item_id' => $grnItem?->id,
                    'product_id'            => $productId,
                    'quantity'              => $qty,
                    'unit_cost'             => $unitCost,
                    'tax_rate'              => $taxRate,
                    'tax_amount'            => $lineTax,
                    'total_amount'          => $lineTotal,
                    'reason'                => $itemData['reason'] ?? $data['reason'] ?? null,
                ]);

                $subtotal += $lineSubtotal;
                $taxAmount += $lineTax;
            }

            $purchaseReturn->subtotal = $subtotal;
            $purchaseReturn->tax_amount = $taxAmount;
            $purchaseReturn->total_amount = round($subtotal + $taxAmount, 2);
            $purchaseReturn->save();

            return $purchaseReturn;
        });
    }

    /**
     * Confirm a return: writes stock_movements (return_to_vendor), creates SupplierDebitNote, updates return status.
     */
    public function confirmReturn(PurchaseReturn $return, int $userId): PurchaseReturn
    {
        return DB::transaction(function () use ($return, $userId) {
            $lockedReturn = PurchaseReturn::where('id', $return->id)->lockForUpdate()->firstOrFail();

            if ($lockedReturn->status === PurchaseReturn::STATUS_CONFIRMED) {
                return $lockedReturn;
            }

            if ($lockedReturn->status === PurchaseReturn::STATUS_CANCELLED) {
                throw new InvalidArgumentException('Cannot confirm a cancelled purchase return.');
            }

            $lockedReturn->loadMissing(['items.product', 'supplier', 'goodsReceipt']);

            if ($lockedReturn->items->isEmpty()) {
                throw new InvalidArgumentException('Cannot confirm a return with no items.');
            }

            // 1. Record stock deductions via StockService
            foreach ($lockedReturn->items as $item) {
                $this->stockService->recordReturnToVendor($item->product, $item->quantity, [
                    'reference_type' => PurchaseReturn::class,
                    'reference_id'   => $lockedReturn->id,
                    'unit_cost'      => (float) $item->unit_cost,
                    'note'           => "Purchase Return {$lockedReturn->return_number}: " . ($item->reason ?: 'Returned to vendor'),
                    'created_by'     => $userId,
                ]);
            }

            // 2. Generate Debit Note
            $debitNoteNumber = DocumentSequenceService::nextDebitNoteNumber($lockedReturn->return_date->format('Y-m-d'));

            SupplierDebitNote::create([
                'debit_note_number'  => $debitNoteNumber,
                'supplier_id'        => $lockedReturn->supplier_id,
                'purchase_return_id' => $lockedReturn->id,
                'vendor_bill_id'     => null,
                'note_date'          => $lockedReturn->return_date,
                'amount'             => $lockedReturn->subtotal,
                'tax_amount'         => $lockedReturn->tax_amount,
                'total_amount'       => $lockedReturn->total_amount,
                'status'             => SupplierDebitNote::STATUS_ACTIVE,
                'reason'             => $lockedReturn->reason ?: "Debit note for Return {$lockedReturn->return_number}",
                'created_by'         => $userId,
            ]);

            // 3. Mark return confirmed
            $lockedReturn->status = PurchaseReturn::STATUS_CONFIRMED;
            $lockedReturn->debit_note_number = $debitNoteNumber;
            $lockedReturn->approved_by = $userId;
            $lockedReturn->save();

            return $lockedReturn;
        });
    }
}

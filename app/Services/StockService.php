<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Record a goods receipt inwarding, updating stock and recalculating weighted average cost.
     */
    public function recordReceipt(Product $product, int $qty, float $unitCost, array $options = []): StockMovement
    {
        return DB::transaction(function () use ($product, $qty, $unitCost, $options) {
            $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();

            $currentStock = (int) $lockedProduct->stock_quantity;
            $currentCost = (float) $lockedProduct->cost_price;
            $acceptedQty = max(0, $qty);

            // Calculate weighted average cost if stock is added
            if ($acceptedQty > 0) {
                if ($currentStock > 0 && ($currentStock + $acceptedQty) > 0) {
                    $newCost = (($currentStock * $currentCost) + ($acceptedQty * $unitCost)) / ($currentStock + $acceptedQty);
                } else {
                    $newCost = $unitCost > 0 ? $unitCost : $currentCost;
                }
                $lockedProduct->cost_price = round($newCost, 2);
            }

            $newStock = $currentStock + $acceptedQty;
            $lockedProduct->stock_quantity = $newStock;
            $lockedProduct->save();

            $movement = StockMovement::create([
                'product_id'     => $lockedProduct->id,
                'movement_type'  => StockMovement::TYPE_RECEIPT,
                'type'           => 'in',
                'qty'            => $acceptedQty,
                'quantity'       => $acceptedQty,
                'balance_after'  => $newStock,
                'unit_cost'      => $unitCost,
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id'   => $options['reference_id'] ?? null,
                'project_id'     => $options['project_id'] ?? null,
                'note'           => $options['note'] ?? 'Goods Receipt Inwarding',
                'notes'          => $options['note'] ?? 'Goods Receipt Inwarding',
                'created_by'     => $options['created_by'] ?? auth()->id(),
                'user_id'        => $options['created_by'] ?? auth()->id(),
            ]);

            // Sync original model instance in memory
            $product->stock_quantity = $newStock;
            $product->cost_price = $lockedProduct->cost_price;

            return $movement;
        });
    }

    /**
     * Record a stock issue (deduction from stock, e.g. for projects, jobs or installations).
     */
    public function recordIssue(Product $product, int $qty, array $options = []): StockMovement
    {
        return DB::transaction(function () use ($product, $qty, $options) {
            $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();

            $deductQty = abs($qty);
            $newStock = max(0, (int) $lockedProduct->stock_quantity - $deductQty);
            $lockedProduct->stock_quantity = $newStock;
            $lockedProduct->save();

            $movement = StockMovement::create([
                'product_id'     => $lockedProduct->id,
                'movement_type'  => StockMovement::TYPE_ISSUE,
                'type'           => 'out',
                'qty'            => -$deductQty,
                'quantity'       => $deductQty,
                'balance_after'  => $newStock,
                'unit_cost'      => $options['unit_cost'] ?? $lockedProduct->cost_price,
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id'   => $options['reference_id'] ?? null,
                'project_id'     => $options['project_id'] ?? null,
                'note'           => $options['note'] ?? 'Material Stock Issue',
                'notes'          => $options['note'] ?? 'Material Stock Issue',
                'created_by'     => $options['created_by'] ?? auth()->id(),
                'user_id'        => $options['created_by'] ?? auth()->id(),
            ]);

            $product->stock_quantity = $newStock;

            return $movement;
        });
    }

    /**
     * Record a physical audit adjustment, bringing stock directly to the specified target.
     */
    public function recordAdjustment(Product $product, int $newStock, ?string $reason = null, ?int $userId = null): StockMovement
    {
        return DB::transaction(function () use ($product, $newStock, $reason, $userId) {
            $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();

            $oldStock = (int) $lockedProduct->stock_quantity;
            $diff = $newStock - $oldStock;

            $lockedProduct->stock_quantity = $newStock;
            $lockedProduct->save();

            $movement = StockMovement::create([
                'product_id'     => $lockedProduct->id,
                'movement_type'  => StockMovement::TYPE_ADJUSTMENT,
                'type'           => 'adjustment',
                'qty'            => $diff,
                'quantity'       => abs($diff),
                'balance_after'  => $newStock,
                'unit_cost'      => $lockedProduct->cost_price,
                'reference_type' => null,
                'reference_id'   => null,
                'project_id'     => null,
                'note'           => $reason ?? 'Inventory Stock Adjustment',
                'notes'          => $reason ?? 'Inventory Stock Adjustment',
                'created_by'     => $userId ?? auth()->id(),
                'user_id'        => $userId ?? auth()->id(),
            ]);

            $product->stock_quantity = $newStock;

            return $movement;
        });
    }

    /**
     * Record an opening stock entry when introducing a product or setting opening inventory.
     */
    public function recordOpening(Product $product, int $qty, ?float $unitCost = null, ?int $userId = null): StockMovement
    {
        return DB::transaction(function () use ($product, $qty, $unitCost, $userId) {
            $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();

            $lockedProduct->stock_quantity = $qty;
            if ($unitCost !== null && $unitCost >= 0) {
                $lockedProduct->cost_price = round($unitCost, 2);
            }
            $lockedProduct->save();

            $movement = StockMovement::create([
                'product_id'     => $lockedProduct->id,
                'movement_type'  => StockMovement::TYPE_OPENING,
                'type'           => 'in',
                'qty'            => $qty,
                'quantity'       => abs($qty),
                'balance_after'  => $qty,
                'unit_cost'      => $unitCost ?? $lockedProduct->cost_price,
                'reference_type' => null,
                'reference_id'   => null,
                'project_id'     => null,
                'note'           => 'Opening stock balance',
                'notes'          => 'Opening stock balance',
                'created_by'     => $userId ?? auth()->id(),
                'user_id'        => $userId ?? auth()->id(),
            ]);

            $product->stock_quantity = $qty;
            if ($unitCost !== null) {
                $product->cost_price = $lockedProduct->cost_price;
            }

            return $movement;
        });
    }

    /**
     * Record a return to vendor (reducing warehouse stock).
     */
    public function recordReturnToVendor(Product $product, int $qty, array $options = []): StockMovement
    {
        return DB::transaction(function () use ($product, $qty, $options) {
            $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();

            $returnQty = abs($qty);
            $newStock = max(0, (int) $lockedProduct->stock_quantity - $returnQty);
            $lockedProduct->stock_quantity = $newStock;
            $lockedProduct->save();

            $movement = StockMovement::create([
                'product_id'     => $lockedProduct->id,
                'movement_type'  => StockMovement::TYPE_RETURN_TO_VENDOR,
                'type'           => 'return',
                'qty'            => -$returnQty,
                'quantity'       => $returnQty,
                'balance_after'  => $newStock,
                'unit_cost'      => $options['unit_cost'] ?? $lockedProduct->cost_price,
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id'   => $options['reference_id'] ?? null,
                'project_id'     => $options['project_id'] ?? null,
                'note'           => $options['note'] ?? 'Return to Vendor',
                'notes'          => $options['note'] ?? 'Return to Vendor',
                'created_by'     => $options['created_by'] ?? auth()->id(),
                'user_id'        => $options['created_by'] ?? auth()->id(),
            ]);

            $product->stock_quantity = $newStock;

            return $movement;
        });
    }
}

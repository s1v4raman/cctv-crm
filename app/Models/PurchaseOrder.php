<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'supplier_id',
        'po_number',
        'order_date',
        'expected_delivery_date',
        'status', // draft, ordered, partially_received, received, cancelled
        'subtotal',
        'tax_percent',
        'tax_amount',
        'shipping_cost',
        'total',
        'payment_status', // unpaid, partially_paid, paid
        'amount_paid',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'expected_delivery_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_percent' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('total_cost');
        $taxAmount = $subtotal * ((float) $this->tax_percent / 100.0);
        $total = $subtotal + $taxAmount + (float) $this->shipping_cost;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }

    /**
     * Inward received items into central warehouse stock ledger
     * 
     * @param array<int, int> $receivedQuantities [item_id => qty_to_inward_now]
     */
    public function inwardStock(array $receivedQuantities, ?int $userId = null): void
    {
        DB::transaction(function () use ($receivedQuantities, $userId) {
            foreach ($this->items as $item) {
                if (!isset($receivedQuantities[$item->id])) {
                    continue;
                }

                $newInwardQty = (int) $receivedQuantities[$item->id];
                if ($newInwardQty <= 0) {
                    continue;
                }

                // Update item received count
                $item->increment('quantity_received', $newInwardQty);

                // If item is linked to catalog product, increment stock & write movement
                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock_quantity', $newInwardQty);

                        StockMovement::create([
                            'product_id' => $product->id,
                            'type' => 'in',
                            'quantity' => $newInwardQty,
                            'balance_after' => $product->stock_quantity,
                            'reference_type' => PurchaseOrder::class,
                            'reference_id' => $this->id,
                            'notes' => "Procurement Inwarding via {$this->po_number} ({$this->supplier->name})",
                            'user_id' => $userId ?: auth()->id(),
                        ]);
                    }
                }
            }

            // Recalculate PO overall received status
            $this->refresh();
            $allItems = $this->items;
            $totalOrdered = $allItems->sum('quantity_ordered');
            $totalReceived = $allItems->sum('quantity_received');

            if ($totalReceived >= $totalOrdered) {
                $this->update(['status' => 'received']);
            } elseif ($totalReceived > 0) {
                $this->update(['status' => 'partially_received']);
            }
        });
    }

    public function balanceDue(): float
    {
        return max((float) ($this->total - $this->amount_paid), 0.0);
    }
}

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
        'supplier_invoice_no',
        'supplier_invoice_date',
        'order_date',
        'expected_delivery_date',
        'due_date',
        'status', // draft, ordered, partially_received, received, cancelled
        'subtotal',
        'tax_percent',
        'tax_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'is_itc_eligible',
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
            'due_date' => 'date',
            'supplier_invoice_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_percent' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'cgst_amount' => 'decimal:2',
            'sgst_amount' => 'decimal:2',
            'igst_amount' => 'decimal:2',
            'is_itc_eligible' => 'boolean',
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

    public function vendorPayments(): HasMany
    {
        return $this->hasMany(VendorPayment::class)->latest('payment_date');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Compute 3-Way Matching Verification Status (PO vs GRN Inward vs Vendor Bill)
     */
    public function getThreeWayMatchStatus(): array
    {
        $items = $this->items;
        $totalOrdered = (int) $items->sum('quantity_ordered');
        $totalReceived = (int) $items->sum('quantity_received');
        $hasBill = !empty(trim((string) $this->supplier_invoice_no));

        $itemBreakdowns = [];
        $hasQtyMismatch = false;

        foreach ($items as $item) {
            $ordered = (int) $item->quantity_ordered;
            $received = (int) $item->quantity_received;
            $qtyDiff = $received - $ordered;

            if ($received > 0 && $received !== $ordered) {
                $hasQtyMismatch = true;
            }

            $itemBreakdowns[] = [
                'item_name'         => $item->item_name,
                'sku'               => $item->sku,
                'unit_cost'         => (float) $item->unit_cost,
                'quantity_ordered'  => $ordered,
                'quantity_received' => $received,
                'variance_qty'      => $qtyDiff,
                'is_fully_received' => $received >= $ordered,
            ];
        }

        if ($totalReceived === 0) {
            $status = 'pending_inward';
            $label = 'Pending Inwarding (GRN)';
            $badgeClass = 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700';
            $icon = 'hourglass';
            $description = 'Goods not yet received at warehouse.';
        } elseif (!$hasBill) {
            $status = 'unbilled';
            $label = 'Inwarded / Bill Pending';
            $badgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300 dark:border-amber-800';
            $icon = 'document';
            $description = 'Stock received, but vendor tax invoice not yet recorded.';
        } elseif ($totalReceived >= $totalOrdered && !$hasQtyMismatch) {
            $status = 'matched';
            $label = '3-Way Verified (Passed)';
            $badgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800';
            $icon = 'check-circle';
            $description = '100% matched: PO quantity, GRN received units, and Vendor invoice align.';
        } elseif ($totalReceived < $totalOrdered) {
            $status = 'partial_receipt';
            $label = 'Partial Receipt (In Progress)';
            $badgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-300 dark:border-blue-800';
            $icon = 'truck';
            $description = "Received {$totalReceived} of {$totalOrdered} units. Remaining shipment expected.";
        } else {
            $status = 'quantity_mismatch';
            $label = 'Quantity Discrepancy';
            $badgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-300 dark:border-rose-800 animate-pulse';
            $icon = 'exclamation-circle';
            $description = 'Variance between ordered purchase quantities and warehouse inward records.';
        }

        return [
            'status'         => $status,
            'label'          => $label,
            'badge_class'    => $badgeClass,
            'icon'           => $icon,
            'description'    => $description,
            'total_ordered'  => $totalOrdered,
            'total_received' => $totalReceived,
            'has_bill'       => $hasBill,
            'bill_number'    => $this->supplier_invoice_no,
            'bill_date'      => $this->supplier_invoice_date?->format('d M Y'),
            'items'          => $itemBreakdowns,
        ];
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

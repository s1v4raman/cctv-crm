<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'bom_item_id',
        'item_name',
        'sku',
        'hsn_code',
        'unit_cost',
        'quantity_ordered',
        'quantity_received',
        'total_cost',
        'tax_rate',
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'igst_rate',
        'igst_amount',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'quantity_ordered' => 'integer',
            'quantity_received' => 'integer',
            'total_cost' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'cgst_rate' => 'decimal:2',
            'cgst_amount' => 'decimal:2',
            'sgst_rate' => 'decimal:2',
            'sgst_amount' => 'decimal:2',
            'igst_rate' => 'decimal:2',
            'igst_amount' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function bomItem(): BelongsTo
    {
        return $this->belongsTo(ProjectMaterial::class, 'bom_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function goodsReceiptItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function pendingQuantity(): int
    {
        return max($this->quantity_ordered - $this->quantity_received, 0);
    }
}

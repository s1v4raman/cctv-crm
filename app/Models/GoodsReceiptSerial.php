<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptSerial extends Model
{
    protected $fillable = [
        'goods_receipt_item_id',
        'product_id',
        'serial_no',
        'mac_address',
        'warranty_until',
    ];

    protected function casts(): array
    {
        return [
            'warranty_until' => 'date',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptItem::class, 'goods_receipt_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

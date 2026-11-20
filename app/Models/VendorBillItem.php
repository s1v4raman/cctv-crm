<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBillItem extends Model
{
    protected $fillable = [
        'vendor_bill_id',
        'purchase_order_item_id',
        'product_id',
        'item_name',
        'hsn_code',
        'quantity',
        'unit_cost',
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
            'quantity'    => 'decimal:2',
            'unit_cost'   => 'decimal:2',
            'total_cost'  => 'decimal:2',
            'tax_rate'    => 'decimal:2',
            'cgst_rate'   => 'decimal:2',
            'cgst_amount' => 'decimal:2',
            'sgst_rate'   => 'decimal:2',
            'sgst_amount' => 'decimal:2',
            'igst_rate'   => 'decimal:2',
            'igst_amount' => 'decimal:2',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class, 'vendor_bill_id');
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

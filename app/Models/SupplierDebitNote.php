<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierDebitNote extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ADJUSTED = 'adjusted';
    public const STATUS_VOIDED = 'voided';

    protected $fillable = [
        'debit_note_number',
        'supplier_id',
        'purchase_return_id',
        'vendor_bill_id',
        'note_date',
        'amount',
        'tax_amount',
        'total_amount',
        'status',
        'reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'note_date'    => 'date',
            'amount'       => 'decimal:2',
            'tax_amount'   => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function vendorBill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}

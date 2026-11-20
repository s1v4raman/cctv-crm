<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorBill extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PARTIALLY_PAID = 'partially_paid';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    public const MATCH_NOT_CHECKED = 'not_checked';
    public const MATCH_MATCHED = 'matched';
    public const MATCH_MISMATCH = 'mismatch';

    protected $fillable = [
        'bill_number',
        'vendor_invoice_no',
        'supplier_id',
        'purchase_order_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'cgst',
        'sgst',
        'igst',
        'total',
        'amount_paid',
        'status',
        'match_status',
        'match_notes',
        'approved_by',
        'approved_at',
        'approval_reason',
        'invoice_file_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date'     => 'date',
            'approved_at'  => 'datetime',
            'subtotal'     => 'decimal:2',
            'cgst'         => 'decimal:2',
            'sgst'         => 'decimal:2',
            'igst'         => 'decimal:2',
            'total'        => 'decimal:2',
            'amount_paid'  => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorBillItem::class);
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(VendorPaymentAllocation::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(VendorPayment::class);
    }

    public function balanceDue(): float
    {
        return max(0.0, (float) ($this->total - $this->amount_paid));
    }

    public function isOverdue(?Carbon $asOf = null): bool
    {
        if ($this->balanceDue() <= 0.001) {
            return false;
        }

        $ref = $asOf ?: now();
        return $this->due_date && $ref->startOfDay()->gt($this->due_date->startOfDay());
    }

    public function daysOverdue(?Carbon $asOf = null): int
    {
        if (!$this->isOverdue($asOf)) {
            return 0;
        }

        $ref = $asOf ?: now();
        return (int) $ref->startOfDay()->diffInDays($this->due_date->startOfDay());
    }

    public function recalculateTotals(): void
    {
        $this->loadMissing('items');

        $subtotal = 0.0;
        $cgst = 0.0;
        $sgst = 0.0;
        $igst = 0.0;

        foreach ($this->items as $item) {
            $subtotal += (float) $item->total_cost;
            $cgst += (float) $item->cgst_amount;
            $sgst += (float) $item->sgst_amount;
            $igst += (float) $item->igst_amount;
        }

        $total = $subtotal + $cgst + $sgst + $igst;

        $this->update([
            'subtotal' => round($subtotal, 2),
            'cgst'     => round($cgst, 2),
            'sgst'     => round($sgst, 2),
            'igst'     => round($igst, 2),
            'total'    => round($total, 2),
        ]);
    }
}

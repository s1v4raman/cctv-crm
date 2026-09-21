<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_no',
        'invoice_id',
        'quotation_id',
        'amount',
        'paid_on',
        'method',
        'reference_no',
        'gateway_order_id',
        'gateway_payment_id',
        'gateway_signature',
        'payment_status',
        'receipt_pdf_path',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'paid_on' => 'date',
        'amount'  => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Generate unique sequential receipt number: REC-YYYYMMDD-XXXX.
     */
    public static function generateReceiptNumber(): string
    {
        $prefix = 'REC-' . date('Ymd');
        $countToday = self::where('receipt_no', 'like', "{$prefix}-%")->count() + 1;
        return sprintf('%s-%04d', $prefix, $countToday);
    }

    /**
     * Human-friendly payment method label.
     */
    public function getFormattedMethodAttribute(): string
    {
        return match ($this->method) {
            'razorpay'      => 'Razorpay Online',
            'upi'           => 'UPI Transfer (GPay/PhonePe)',
            'bank_transfer' => 'NEFT / RTGS Bank Transfer',
            'card'          => 'Credit / Debit Card',
            'netbanking'    => 'Net Banking',
            'cash'          => 'Cash Payment',
            'cheque'        => 'Cheque Clearance',
            default         => ucfirst(str_replace('_', ' ', $this->method ?? 'Online')),
        };
    }

    /**
     * Get the associated Lead/Customer for this payment.
     */
    public function getCustomerLead(): ?Lead
    {
        if ($this->invoice && $this->invoice->quotation) {
            return $this->invoice->quotation->lead;
        }

        if ($this->quotation) {
            return $this->quotation->lead;
        }

        return null;
    }
}

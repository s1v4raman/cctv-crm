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

    /**
     * How the customer paid. Staff pick one when recording a payment on an invoice;
     * customers never pay on the site itself.
     */
    public const METHODS = [
        'cash'          => 'Cash',
        'gpay'          => 'Google Pay (GPay)',
        'phonepe'       => 'PhonePe',
        'paytm'         => 'Paytm',
        'upi'           => 'UPI (other app)',
        'credit_card'   => 'Credit card',
        'debit_card'    => 'Debit card',
        'bank_transfer' => 'Bank transfer (NEFT / IMPS / RTGS)',
        'cheque'        => 'Cheque',
        'other'         => 'Other',
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
        return self::METHODS[$this->method] ?? match ($this->method) {
            // Older records from before online payment was removed
            'razorpay'   => 'Razorpay (online)',
            'card'       => 'Card',
            'netbanking' => 'Net banking',
            default      => ucfirst(str_replace('_', ' ', $this->method ?? 'Other')),
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

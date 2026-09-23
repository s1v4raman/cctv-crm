<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PettyCashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no',
        'petty_cash_account_id',
        'destination_account_id',
        'user_id',
        'transaction_type', // float_advance, field_collection, direct_expense, cash_deposit_handover, adjustment
        'amount',
        'transaction_date',
        'category',
        'related_job_id',
        'related_ticket_id',
        'related_invoice_id',
        'related_payment_id',
        'related_expense_claim_id',
        'vendor_payee_name',
        'bill_receipt_no',
        'receipt_photo_path',
        'notes',
        'status', // pending, approved, rejected
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'transaction_date' => 'date',
            'approved_at'      => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(PettyCashAccount::class, 'petty_cash_account_id');
    }

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(PettyCashAccount::class, 'destination_account_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(InstallationJob::class, 'related_job_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(ServiceTicket::class, 'related_ticket_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'related_invoice_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'related_payment_id');
    }

    public function expenseClaim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class, 'related_expense_claim_id');
    }

    /**
     * Generate unique sequential petty cash voucher number: PCV-YYYYMMDD-XXXX
     */
    public static function generateVoucherNumber(?Carbon $date = null): string
    {
        $d = $date ?: now();
        $prefix = 'PCV-' . $d->format('Ymd');
        $count = self::where('voucher_no', 'like', "{$prefix}-%")->count() + 1;
        return sprintf('%s-%04d', $prefix, $count);
    }
}

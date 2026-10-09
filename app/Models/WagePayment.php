<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WagePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'worker_id',
        'payment_type',
        'week_start_date',
        'week_end_date',
        'week_start',
        'week_end',
        'amount',
        'payment_date',
        'payment_mode',
        'paid_by',
        'settled_by',
        'notes',
        'reference_notes',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date'   => 'date',
        'payment_date'    => 'date',
        'amount'          => 'decimal:2',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function settledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    public function getPaymentTypeBadgeClassesAttribute(): string
    {
        return match ($this->payment_type) {
            'advance' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300',
            'wage'    => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-300',
            default   => 'bg-slate-100 text-slate-700',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectWorkerAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_day_id',
        'worker_id',
        'attendance_type',
        'daily_rate_snapshot',
        'rate_applied',
        'cabling_metres',
        'cabling_rate',
        'cabling_amount',
        'extra_type',
        'metres',
        'extra_amount',
        'extra_description',
        'extra_reason',
        'total_amount',
        'is_locked',
        'is_paid',
        'paid_week_start',
        'wage_payment_id',
        'unlocked_by',
        'unlocked_at',
        'notes',
    ];

    protected $casts = [
        'daily_rate_snapshot' => 'decimal:2',
        'rate_applied' => 'decimal:2',
        'cabling_metres' => 'decimal:2',
        'cabling_rate' => 'decimal:2',
        'cabling_amount' => 'decimal:2',
        'metres' => 'decimal:2',
        'extra_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_locked' => 'boolean',
        'is_paid' => 'boolean',
        'paid_week_start' => 'date',
        'unlocked_at' => 'datetime',
    ];

    public function workDay(): BelongsTo
    {
        return $this->belongsTo(WorkDay::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function unlocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }

    /**
     * Compute total daily wage payout for this record.
     */
    public static function calculateTotal(
        float $rateApplied,
        string $attendanceType,
        string $extraType = 'none',
        float $metres = 0.0,
        float $extraAmount = 0.0
    ): float {
        $multiplier = ($attendanceType === 'half') ? 0.5 : 1.0;
        $basePay = $rateApplied * $multiplier;

        $extra = 0.0;
        if ($extraType === 'per_metre') {
            $extra = $metres * $extraAmount;
        } elseif ($extraType === 'fixed') {
            $extra = $extraAmount;
        }

        return round($basePay + $extra, 2);
    }
}

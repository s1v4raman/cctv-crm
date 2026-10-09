<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Worker extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'daily_rate',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'daily_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(ProjectWorkerAttendance::class);
    }

    public function wagePayments(): HasMany
    {
        return $this->hasMany(WagePayment::class)->orderBy('payment_date', 'desc');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Total earned strictly before a given week start date.
     */
    public function getEarnedBefore($weekStart): float
    {
        $start = is_string($weekStart) ? Carbon::parse($weekStart) : $weekStart;
        return (float) $this->attendances()
            ->whereHas('workDay', function ($q) use ($start) {
                $q->where('work_date', '<', $start->toDateString());
            })
            ->sum('total_amount');
    }

    /**
     * Total payments made strictly before a given week start date.
     */
    public function getPaidBefore($weekStart): float
    {
        $start = is_string($weekStart) ? Carbon::parse($weekStart) : $weekStart;
        return (float) $this->wagePayments()
            ->where('payment_date', '<', $start->toDateString())
            ->sum('amount');
    }

    /**
     * Brought forward balance = all earned prior to this week minus all paid prior to this week.
     */
    public function getBroughtForward($weekStart): float
    {
        $start = is_string($weekStart) ? Carbon::parse($weekStart) : $weekStart;
        $earnedPrior = $this->getEarnedBefore($start);
        $paidPrior = $this->getPaidBefore($start);
        return round($earnedPrior - $paidPrior, 2);
    }

    /**
     * Total earned in the given week (Monday to Saturday).
     */
    public function getEarnedInWeek($weekStart, $weekEnd): float
    {
        $start = is_string($weekStart) ? Carbon::parse($weekStart) : $weekStart;
        $end = is_string($weekEnd) ? Carbon::parse($weekEnd) : $weekEnd;
        return (float) $this->attendances()
            ->whereHas('workDay', function ($q) use ($start, $end) {
                $q->whereBetween('work_date', [$start->toDateString(), $end->toDateString()]);
            })
            ->sum('total_amount');
    }

    /**
     * Advances taken between Monday and Saturday.
     */
    public function getAdvancesInWeek($weekStart, $weekEnd): float
    {
        $start = is_string($weekStart) ? Carbon::parse($weekStart) : $weekStart;
        $end = is_string($weekEnd) ? Carbon::parse($weekEnd) : $weekEnd;
        return (float) $this->wagePayments()
            ->where('payment_type', 'advance')
            ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');
    }

    /**
     * Sunday wages paid for this specific week.
     */
    public function getWagesPaidForWeek($weekStart, $weekEnd): float
    {
        $start = is_string($weekStart) ? Carbon::parse($weekStart) : $weekStart;
        $end = is_string($weekEnd) ? Carbon::parse($weekEnd) : $weekEnd;
        return (float) $this->wagePayments()
            ->where('payment_type', 'wage')
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($sq) use ($start, $end) {
                    $sq->where('week_start', $start->toDateString())
                       ->where('week_end', $end->toDateString());
                })->orWhereBetween('payment_date', [$start->toDateString(), (clone $end)->addDay()->toDateString()]);
            })
            ->sum('amount');
    }

    /**
     * Lifetime total wages earned by worker across all projects.
     */
    public function getTotalEarnedLifetime(): float
    {
        return round((float) $this->attendances()->sum('total_amount'), 2);
    }

    /**
     * Lifetime total wages paid to worker (advances and wage settlements).
     */
    public function getTotalPaidLifetime(): float
    {
        return round((float) $this->wagePayments()->sum('amount'), 2);
    }

    /**
     * Complete running ledger balance (lifetime earned minus lifetime paid).
     */
    public function getRunningBalance(): float
    {
        return round($this->getTotalEarnedLifetime() - $this->getTotalPaidLifetime(), 2);
    }
}

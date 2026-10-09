<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $table = 'employee_salaries';

    protected $fillable = [
        'user_id',
        'base_salary_monthly',
        'daily_rate',
        'weekly_rate',
        'hourly_rate',
        'overtime_hourly_rate',
        'travel_allowance',
        'special_allowance',
        'deductions',
        'payment_method',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'upi_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_salary_monthly'  => 'decimal:2',
            'daily_rate'           => 'decimal:2',
            'weekly_rate'          => 'decimal:2',
            'hourly_rate'          => 'decimal:2',
            'overtime_hourly_rate' => 'decimal:2',
            'travel_allowance'     => 'decimal:2',
            'special_allowance'    => 'decimal:2',
            'deductions'           => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Auto-calculate daily, weekly, and hourly rates from monthly base salary if not explicitly set.
     */
    public function autoDeriveRates(int $monthlyWorkingDays = 26, float $dailyHours = 8.0): void
    {
        $monthly = (float) $this->base_salary_monthly;

        if ($monthly > 0) {
            if ((float) $this->daily_rate <= 0) {
                $this->attributes['daily_rate'] = round($monthly / $monthlyWorkingDays, 2);
            }
            if ((float) $this->weekly_rate <= 0) {
                $this->attributes['weekly_rate'] = round((float) $this->daily_rate * 6, 2);
            }
            if ((float) $this->hourly_rate <= 0) {
                $this->attributes['hourly_rate'] = round((float) $this->daily_rate / $dailyHours, 2);
            }
            if ((float) $this->overtime_hourly_rate <= 0) {
                $this->attributes['overtime_hourly_rate'] = round((float) $this->hourly_rate * 1.25, 2);
            }
        }
    }

    public function getTotalAllowancesAttribute(): float
    {
        return round((float) $this->travel_allowance + (float) $this->special_allowance, 2);
    }

    public function getNetMonthlySalaryAttribute(): float
    {
        return round((float) $this->base_salary_monthly + $this->total_allowances - (float) $this->deductions, 2);
    }

    public function getNetMonthlyAttribute(): float
    {
        return $this->net_monthly_salary;
    }

    public function getNetWeeklySalaryAttribute(): float
    {
        $weeklyAllowances = round($this->total_allowances / 4.33, 2);
        $weeklyDeductions = round((float) $this->deductions / 4.33, 2);
        return round((float) $this->weekly_rate + $weeklyAllowances - $weeklyDeductions, 2);
    }

    public function getNetDailyRateAttribute(): float
    {
        $dailyAllowances = round($this->total_allowances / 26, 2);
        $dailyDeductions = round((float) $this->deductions / 26, 2);
        return round((float) $this->daily_rate + $dailyAllowances - $dailyDeductions, 2);
    }
}

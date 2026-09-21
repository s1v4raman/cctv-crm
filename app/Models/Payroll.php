<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payrolls';

    protected $fillable = [
        'payroll_number',
        'user_id',
        'period_type',
        'period_start',
        'period_end',
        'working_days',
        'present_days',
        'half_days',
        'leave_days',
        'absent_days',
        'overtime_hours',
        'basic_pay',
        'overtime_pay',
        'allowances',
        'deductions',
        'net_salary',
        'status',
        'payment_date',
        'payment_reference',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start'   => 'date',
            'period_end'     => 'date',
            'payment_date'   => 'date',
            'working_days'   => 'integer',
            'present_days'   => 'decimal:1',
            'half_days'      => 'integer',
            'leave_days'     => 'decimal:1',
            'absent_days'    => 'decimal:1',
            'overtime_hours' => 'decimal:2',
            'basic_pay'      => 'decimal:2',
            'overtime_pay'   => 'decimal:2',
            'allowances'     => 'decimal:2',
            'deductions'     => 'decimal:2',
            'net_salary'     => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'paid'     => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
            'approved' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-400 border-blue-200 dark:border-blue-800',
            default    => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200 dark:border-amber-800',
        };
    }

    public function getFormattedPeriodAttribute(): string
    {
        if ($this->period_type === 'monthly') {
            return Carbon::parse($this->period_start)->format('F Y');
        }

        return Carbon::parse($this->period_start)->format('d M') . ' — ' . Carbon::parse($this->period_end)->format('d M Y');
    }

    /**
     * Generate a unique formal payroll slip number, e.g. PAY-202609-001
     */
    public static function generatePayrollNumber(string $date = null): string
    {
        $d = $date ? Carbon::parse($date) : now();
        $prefix = 'PAY-' . $d->format('Ym') . '-';
        $last = static::where('payroll_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('payroll_number');

        if ($last) {
            $num = (int) substr($last, strlen($prefix)) + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}

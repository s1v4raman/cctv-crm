<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property \Carbon\Carbon|string|null $date
 * @property string|null $clock_in
 * @property string|null $clock_out
 * @property string $status
 * @property float|string|null $total_hours
 * @property float|string|null $overtime_hours
 * @property string|null $location_type
 * @property string|null $notes
 * @property int|null $marked_by
 * @property-read float $working_hours
 * @property-read string $status_badge_class
 * @property-read string $status_label
 * @property-read string $formatted_clock_in
 * @property-read string $formatted_clock_out
 * @method static \Illuminate\Database\Eloquent\Builder whereDate(string $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 */
class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $table = 'employee_attendances';

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'total_hours',
        'overtime_hours',
        'location_type',
        'notes',
        'marked_by',
    ];

    protected function casts(): array
    {
        return [
            'date'           => 'date',
            'total_hours'    => 'decimal:2',
            'overtime_hours' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'present'  => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
            'late'     => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200 dark:border-amber-800',
            'half_day' => 'bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-400 border-sky-200 dark:border-sky-800',
            'absent'   => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400 border-rose-200 dark:border-rose-800',
            'on_leave' => 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-400 border-purple-200 dark:border-purple-800',
            default    => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present'  => 'Present (Full Day)',
            'late'     => 'Late Arrival',
            'half_day' => 'Half Day',
            'absent'   => 'Absent',
            'on_leave' => 'On Leave',
            default    => ucfirst($this->status),
        };
    }

    public function getFormattedClockInAttribute(): string
    {
        return $this->clock_in ? Carbon::parse($this->clock_in)->format('h:i A') : '—';
    }

    public function getFormattedClockOutAttribute(): string
    {
        return $this->clock_out ? Carbon::parse($this->clock_out)->format('h:i A') : '—';
    }

    public function getWorkingHoursAttribute(): float
    {
        return (float) $this->total_hours;
    }

    /**
     * Compute total working hours from clock_in and clock_out.
     */
    public function calculateHours(float $standardShiftHours = 8.0): void
    {
        if ($this->clock_in && $this->clock_out) {
            $in = Carbon::parse($this->clock_in);
            $out = Carbon::parse($this->clock_out);
            $diffMinutes = abs($out->diffInMinutes($in));
            $hours = round($diffMinutes / 60, 2);

            $this->attributes['total_hours'] = $hours;
            $this->attributes['overtime_hours'] = max(0.00, round($hours - $standardShiftHours, 2));

            if ($this->status === 'present' && $hours < 5.0 && $hours > 0) {
                $this->status = 'half_day';
            }
        }
    }
}

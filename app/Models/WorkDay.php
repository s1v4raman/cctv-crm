<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkDay extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'work_date',
        'logged_by',
        'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(ProjectWorkerAttendance::class);
    }

    public function getTotalLaborCostAttribute(): float
    {
        return (float) $this->attendances()->sum('total_amount');
    }

    public function getTotalWorkerDaysAttribute(): float
    {
        $full = $this->attendances()->where('attendance_type', 'full')->count();
        $half = $this->attendances()->where('attendance_type', 'half')->count();
        return $full + ($half * 0.5);
    }
}

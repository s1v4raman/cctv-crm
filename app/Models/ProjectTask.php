<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'phase_name',
        'title',
        'description',
        'assigned_to',
        'priority',
        'status',
        'due_date',
        'estimated_hours',
        'actual_hours',
        'order_column',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'order_column' => 'integer',
    ];

    public static function priorityOptions(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'blocked' => 'Blocked',
        ];
    }

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(ProjectTaskChecklist::class, 'project_task_id')->orderBy('order_column')->orderBy('id');
    }

    // Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeBlocked(Builder $query): Builder
    {
        return $query->where('status', 'blocked');
    }

    // Helpers & Accessors
    public function getPriorityBadgeClassesAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-300 dark:border-rose-800',
            'high' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300 dark:border-amber-800',
            'medium' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-300 dark:border-blue-800',
            'low' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700',
            default => 'bg-slate-100 text-slate-700 border-slate-300',
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800',
            'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-300 dark:border-blue-800',
            'blocked' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-300 dark:border-rose-800 animate-pulse',
            'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300 dark:border-amber-800',
            default => 'bg-slate-100 text-slate-700 border-slate-300',
        };
    }

    public function getChecklistProgressAttribute(): array
    {
        $total = $this->checklists->count();
        if ($total === 0) {
            return ['total' => 0, 'completed' => 0, 'percentage' => 100];
        }
        $completed = $this->checklists->where('is_completed', true)->count();
        $percentage = (int) round(($completed / $total) * 100);

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $percentage,
        ];
    }
}

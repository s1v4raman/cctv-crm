<?php

namespace App\Models;

use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $project_id
 * @property string $title
 * @property string|null $description
 * @property string|null $phase_name
 * @property string|null $shift
 * @property string|null $location
 * @property Carbon|null $start_date
 * @property Carbon|null $due_date
 * @property float|null $estimated_hours
 * @property float|null $actual_hours
 * @property string $status
 * @property string $priority
 * @property int|null $assigned_to
 * @property Carbon|null $accepted_at
 * @property Carbon|null $rejected_at
 * @property int|null $rejected_by
 * @property string|null $rejection_reason
 * @property-read Project|null $project
 * @property-read User|null $assignedUser
 * @property-read User|null $rejectedByUser
 */
class ProjectTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'phase_name',
        'title',
        'description',
        'blocker_reason',
        'assigned_to',
        'priority',
        'status',
        'shift',
        'location',
        'start_date',
        'due_date',
        'estimated_hours',
        'actual_hours',
        'order_column',
        'completed_at',
        'completed_by',
        'blocked_at',
        'blocked_by',
        'accepted_at',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'depends_on_task_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'blocked_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
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

    public static function shiftOptions(): array
    {
        return [
            'Morning Shift' => 'Morning Shift (08:00 AM - 02:00 PM)',
            'Day Shift'     => 'Day / General Shift (09:00 AM - 06:00 PM)',
            'Evening Shift' => 'Evening Shift (02:00 PM - 09:00 PM)',
            'Night Shift'   => 'Night Shift (09:00 PM - 06:00 AM)',
            'Full Day'      => 'Full Day Site Work',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'pending' => 'Backlog / Pending',
            'in_progress' => 'Assigned / In Progress',
            'under_review' => 'Under Review / QA',
            'completed' => 'Completed',
            'blocked' => 'Blocked',
            'rejected' => 'Rejected (Needs Reassignment)',
        ];
    }

    public static function kanbanColumns(): array
    {
        return [
            'pending' => [
                'title' => 'Backlog / Pending',
                'bg' => 'bg-slate-100/90 dark:bg-slate-800/80',
                'badge' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                'border' => 'border-slate-300 dark:border-slate-700',
                'accent' => 'border-t-slate-400',
            ],
            'in_progress' => [
                'title' => 'Assigned / In Progress',
                'bg' => 'bg-blue-50/70 dark:bg-blue-950/30',
                'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300',
                'border' => 'border-blue-200 dark:border-blue-800/50',
                'accent' => 'border-t-blue-500',
            ],
            'under_review' => [
                'title' => 'Under Review / QA',
                'bg' => 'bg-purple-50/70 dark:bg-purple-950/30',
                'badge' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300',
                'border' => 'border-purple-200 dark:border-purple-800/50',
                'accent' => 'border-t-purple-500',
            ],
            'completed' => [
                'title' => 'Completed',
                'bg' => 'bg-emerald-50/70 dark:bg-emerald-950/30',
                'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300',
                'border' => 'border-emerald-200 dark:border-emerald-800/50',
                'accent' => 'border-t-emerald-500',
            ],
            'blocked' => [
                'title' => 'Blocked',
                'bg' => 'bg-rose-50/70 dark:bg-rose-950/30',
                'badge' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300',
                'border' => 'border-rose-200 dark:border-rose-800/50',
                'accent' => 'border-t-rose-500',
            ],
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

    public function blockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function isAccepted(): bool
    {
        return !is_null($this->accepted_at);
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected' || !is_null($this->rejected_at);
    }

    public function dependsOnTask(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'depends_on_task_id');
    }

    public function dependentTasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'depends_on_task_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(ProjectTaskChecklist::class, 'project_task_id')->orderBy('order_column')->orderBy('id');
    }

    public function materialConsumptions(): HasMany
    {
        return $this->hasMany(TaskMaterialConsumption::class, 'project_task_id')->latest();
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

    public function scopeUnderReview(Builder $query): Builder
    {
        return $query->where('status', 'under_review');
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
    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

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
            'under_review' => 'bg-purple-100 text-purple-800 dark:bg-purple-950/70 dark:text-purple-300 border-purple-300 dark:border-purple-800',
            'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-300 dark:border-blue-800',
            'blocked' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-300 dark:border-rose-800',
            'pending' => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700',
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

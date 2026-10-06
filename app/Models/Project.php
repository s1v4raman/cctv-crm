<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code',
        'title',
        'company_name',
        'lead_id',
        'site_address',
        'contact_person',
        'contact_phone',
        'contact_email',
        'description',
        'status',
        'priority',
        'progress_percentage',
        'budget',
        'actual_cost',
        'start_date',
        'deadline',
        'completed_at',
        'assigned_to',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'completed_at' => 'date',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'progress_percentage' => 'integer',
    ];

    public static function statusOptions(): array
    {
        return [
            'in_progress' => 'In Progress',
            'completed' => 'Completed / Done',
            'incompleted' => 'Incompleted / Pending',
            'on_hold' => 'On Hold',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class)->latest();
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusOptions()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
            'in_progress' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
            'incompleted' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800',
            'on_hold' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
            'cancelled' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
            default => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
        };
    }

    public function getPriorityBadgeClassesAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-rose-100 dark:bg-rose-950/70 text-rose-800 dark:text-rose-300 border-rose-300 dark:border-rose-800',
            'high' => 'bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border-amber-300 dark:border-amber-800',
            'medium' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
            'low' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
            default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
        };
    }

    public function getProgressBarColorAttribute(): string
    {
        if ($this->status === 'completed' || $this->progress_percentage >= 100) {
            return 'bg-emerald-500';
        }
        if ($this->progress_percentage < 30) {
            return 'bg-amber-500';
        }
        return 'bg-blue-600';
    }

    // Scopes
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('company_name', 'like', "%{$term}%")
              ->orWhere('title', 'like', "%{$term}%")
              ->orWhere('project_code', 'like', "%{$term}%")
              ->orWhere('site_address', 'like', "%{$term}%")
              ->orWhere('contact_person', 'like', "%{$term}%")
              ->orWhere('contact_phone', 'like', "%{$term}%");
        });
    }

    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if (!$status || $status === 'all') {
            return $query;
        }

        if ($status === 'done' || $status === 'completed') {
            return $query->where('status', 'completed');
        }

        if ($status === 'in_progress') {
            return $query->where('status', 'in_progress');
        }

        if ($status === 'incompleted') {
            return $query->whereIn('status', ['incompleted', 'on_hold']);
        }

        return $query->where('status', $status);
    }
}

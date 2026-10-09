<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_code',
        'po_number',
        'title',
        'project_type',
        'company_id',
        'company_name',
        'site_id',
        'lead_id',
        'site_address',
        'contact_person',
        'contact_phone',
        'contact_email',
        'description',
        'requirements',
        'status',
        'priority',
        'progress_percentage',
        'budget',
        'approved_value',
        'actual_cost',
        'per_metre_rate',
        'start_date',
        'deadline',
        'completed_at',
        'assigned_to',
        'lead_technician_id',
        'created_by',
        'approved_by',
        'approved_on',
        'rejection_reason',
        'notes',
        'hardware_specs',
        'software_specs',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'completed_at' => 'date',
        'approved_on' => 'datetime',
        'budget' => 'decimal:2',
        'approved_value' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'per_metre_rate' => 'decimal:2',
        'progress_percentage' => 'integer',
        'hardware_specs' => 'array',
        'software_specs' => 'array',
        'requirements' => 'array',
    ];

    public static function generateProjectCode(): string
    {
        $year = date('Y');
        $prefix = "PRJ-{$year}-";
        $latest = static::where('project_code', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('project_code');

        if ($latest && preg_match('/PRJ-\d{4}-(\d+)/', $latest, $matches)) {
            $seq = (int) $matches[1] + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public static function projectTypeOptions(): array
    {
        return [
            'hardware_cctv'       => 'CCTV Surveillance & Security',
            'hardware_attendance' => 'Terminal Camera & Attendance',
            'networking'          => 'Network Cabling & Wi-Fi Systems',
            'access_control'      => 'Biometric Access Control & Intercom',
            'software_web'        => 'Webpage & Web Application',
            'hybrid'              => 'Hybrid (Hardware + Software)',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'draft'            => 'Draft',
            'pending_approval' => 'Pending Approval',
            'approved'         => 'Approved',
            'in_progress'      => 'In Progress',
            'on_hold'          => 'On Hold',
            'completed'        => 'Completed',
            'cancelled'        => 'Cancelled',
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

    public function scopeByType($query, $type)
    {
        return $query->where('project_type', $type);
    }

    // Relationships
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function leadTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_technician_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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

    public function materials(): HasMany
    {
        return $this->hasMany(ProjectMaterial::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class)->latest();
    }

    public function workDays(): HasMany
    {
        return $this->hasMany(WorkDay::class)->orderBy('work_date', 'desc');
    }

    public function ipDevices(): HasMany
    {
        return $this->hasMany(IpDevice::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(ProjectAuditLog::class, 'auditable');
    }

    // Status helpers
    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'in_progress', 'on_hold', 'completed']);
    }

    public function canBeEditedBy(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Before approval, creator can edit
        if (in_array($this->status, ['draft', 'pending_approval']) && $this->created_by === $user->id) {
            return true;
        }

        return false;
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return static::statusOptions()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            'pending_approval' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300 dark:border-amber-800 animate-pulse',
            'approved'         => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800',
            'in_progress'      => 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-300 dark:border-blue-800',
            'completed'        => 'bg-teal-100 text-teal-800 dark:bg-teal-950/70 dark:text-teal-300 border-teal-300 dark:border-teal-800',
            'on_hold'          => 'bg-purple-100 text-purple-800 dark:bg-purple-950/70 dark:text-purple-300 border-purple-300 dark:border-purple-800',
            'cancelled'        => 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-300 dark:border-rose-800',
            'draft'            => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700',
            default            => 'bg-slate-100 text-slate-700 border-slate-300',
        };
    }

    public function getPriorityBadgeClassesAttribute(): string
    {
        return match ($this->priority) {
            'urgent'  => 'bg-rose-100 dark:bg-rose-950/70 text-rose-800 dark:text-rose-300 border-rose-300 dark:border-rose-800',
            'high'    => 'bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border-amber-300 dark:border-amber-800',
            'medium'  => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
            'low'     => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
            default   => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    public function getCompanyBadgeClassesAttribute(): string
    {
        $compName = $this->company ? $this->company->name : $this->company_name;
        return match ($compName) {
            'Precision IT Systems' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/70 dark:text-indigo-300 border-indigo-300',
            'NP Solutions'         => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-300',
            'Linepix'              => 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-300',
            default                => 'bg-slate-100 text-slate-800 border-slate-300',
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

    public function getProjectTypeLabelAttribute(): string
    {
        return static::projectTypeOptions()[$this->project_type] ?? ucfirst(str_replace('_', ' ', $this->project_type ?? 'hardware_cctv'));
    }

    public function getProjectTypeBadgeClassesAttribute(): string
    {
        return match ($this->project_type) {
            'hardware_attendance' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800',
            'software_web'        => 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800',
            'networking'          => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
            'access_control'      => 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800',
            'hybrid'              => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
            default               => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
        };
    }

    // Scopes
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('project_code', 'like', "%{$term}%")
              ->orWhere('po_number', 'like', "%{$term}%")
              ->orWhere('site_address', 'like', "%{$term}%")
              ->orWhere('contact_person', 'like', "%{$term}%")
              ->orWhere('contact_phone', 'like', "%{$term}%")
              ->orWhereHas('site', fn($sq) => $sq->where('name', 'like', "%{$term}%")->orWhere('client_name', 'like', "%{$term}%"))
              ->orWhereHas('company', fn($cq) => $cq->where('name', 'like', "%{$term}%"));
        });
    }

    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if (!$status || $status === 'all') {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeByCompany(Builder $query, $companyId): Builder
    {
        if (!$companyId || $companyId === 'all') {
            return $query;
        }

        return $query->where('company_id', $companyId);
    }

    public function scopeBySite(Builder $query, $siteId): Builder
    {
        if (!$siteId || $siteId === 'all') {
            return $query;
        }

        return $query->where('site_id', $siteId);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProjectAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * What happened, in words, for the project history list.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created' => 'Created the project',
            'approved' => 'Approved the project',
            'rejected' => 'Rejected the project',
            'deleted' => 'Deleted the project',
            'status_changed' => 'Changed the status',
            'status_request' => 'Asked for a status change',
            'technician_assigned' => 'Assigned the lead technician',
            'material_added' => 'Added a material',
            'material_deleted' => 'Removed a material',
            'created_task' => 'Added a task',
            'updated_task' => 'Updated a task',
            'deleted_task' => 'Deleted a task',
            'flagged_blocker' => 'Flagged a blocker',
            'unblocked_task' => 'Unblocked a task',
            default => \Illuminate\Support\Str::ucfirst(str_replace('_', ' ', (string) $this->action)),
        };
    }

    public static function logChange($model, string $action, $oldValues = null, $newValues = null, ?string $reason = null): ?self
    {
        try {
            return self::create([
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'action' => $action,
                'old_values' => is_array($oldValues) ? $oldValues : ($oldValues !== null ? ['value' => $oldValues] : null),
                'new_values' => is_array($newValues) ? $newValues : ($newValues !== null ? ['value' => $newValues] : null),
                'reason' => $reason,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not write project audit log: ' . $e->getMessage());
            return null;
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTaskChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_task_id',
        'item_text',
        'is_completed',
        'order_column',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'order_column' => 'integer',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}

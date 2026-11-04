<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Material handed from the storeroom to a project site (type "issue"),
 * or brought back to the storeroom (type "return").
 */
class ProjectMaterialIssue extends Model
{
    protected $fillable = [
        'project_id',
        'product_id',
        'type',
        'quantity',
        'unit',
        'worker_id',
        'user_id',
        'issued_on',
        'drum_or_batch_no',
        'notes',
        'stock_movement_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'issued_on' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function isReturn(): bool
    {
        return $this->type === 'return';
    }

    /**
     * Who took the material, or who brought it back.
     */
    public function getHandledByNameAttribute(): ?string
    {
        return $this->worker?->name ?? $this->user?->name;
    }
}

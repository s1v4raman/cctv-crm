<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskMaterialConsumption extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_task_id',
        'project_material_id',
        'product_id',
        'item_name',
        'quantity_used',
        'unit',
        'wastage_quantity',
        'drum_or_batch_no',
        'consumed_by_user_id',
        'consumed_at',
        'deduct_from_stock',
        'stock_movement_id',
        'notes',
    ];

    protected $casts = [
        'quantity_used' => 'decimal:2',
        'wastage_quantity' => 'decimal:2',
        'consumed_at' => 'datetime',
        'deduct_from_stock' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function projectMaterial(): BelongsTo
    {
        return $this->belongsTo(ProjectMaterial::class, 'project_material_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function consumedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumed_by_user_id');
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }
}

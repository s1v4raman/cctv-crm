<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'product_id',
        'item_name',
        'quantity',
        'unit',
        'unit_cost',
        'source',
        'shop_name',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function consumptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaskMaterialConsumption::class);
    }

    public function getTotalConsumedAttribute(): float
    {
        if ($this->relationLoaded('consumptions')) {
            return (float) $this->consumptions->sum('quantity_used');
        }
        return (float) $this->consumptions()->sum('quantity_used');
    }

    public function getTotalWastageAttribute(): float
    {
        if ($this->relationLoaded('consumptions')) {
            return (float) $this->consumptions->sum('wastage_quantity');
        }
        return (float) $this->consumptions()->sum('wastage_quantity');
    }

    public function getRemainingQuantityAttribute(): float
    {
        return max(0.0, (float)$this->quantity - $this->total_consumed);
    }

    public function getVarianceAttribute(): float
    {
        return (float)$this->quantity - $this->total_consumed;
    }

    public function getConsumptionPercentageAttribute(): float
    {
        $allocated = (float) $this->quantity;
        if ($allocated <= 0) return 0.0;
        return (float) min(100.0, round(($this->total_consumed / $allocated) * 100, 1));
    }

    public function getIsOverConsumedAttribute(): bool
    {
        return $this->total_consumed > (float)$this->quantity;
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'company_stock' => 'Company Stock',
            'bought_from_shop' => 'Shop Purchase (' . ($this->shop_name ?: 'Local Shop') . ')',
            default => ucfirst(str_replace('_', ' ', $this->source)),
        };
    }
}

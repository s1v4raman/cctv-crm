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
        'item_name',
        'quantity',
        'unit',
        'source',
        'shop_name',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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

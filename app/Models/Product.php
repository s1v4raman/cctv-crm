<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'name_key',
        'status',
        'created_by',
        'category',
        'brand',
        'model_no',
        'description',
        'unit',
        'cost_price',
        'unit_price',
        'stock_quantity',
        'min_stock_alert',
        'hsn_code',
        'tax_rate',
        'default_warranty_months',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (!empty($product->name)) {
                $product->name = self::cleanName($product->name);
                if (empty($product->name_key)) {
                    $baseKey = self::normalizeNameKey($product->name);
                    if ($baseKey === '') {
                        $baseKey = 'product';
                    }
                    $candidate = $baseKey;
                    $counter = 1;
                    while (self::where('name_key', $candidate)->where('id', '!=', $product->id ?? 0)->exists()) {
                        $suffix = $product->sku ? strtolower($product->sku) : ($product->id ?: $counter);
                        $candidate = ($counter === 1) ? "{$baseKey}-{$suffix}" : "{$baseKey}-{$counter}";
                        $counter++;
                    }
                    $product->name_key = $candidate;
                }
            }
        });
    }

    public static function normalizeNameKey(?string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name ?? '')));
    }

    public static function cleanName(?string $name): string
    {
        return trim(preg_replace('/\s+/', ' ', $name ?? ''));
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projectMaterials(): HasMany
    {
        return $this->hasMany(ProjectMaterial::class);
    }

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'stock_quantity' => 'integer',
            'min_stock_alert' => 'integer',
            'default_warranty_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function quotationItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function installedEquipment(): HasMany
    {
        return $this->hasMany(InstalledEquipment::class);
    }

    public function taskConsumptions(): HasMany
    {
        return $this->hasMany(TaskMaterialConsumption::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->min_stock_alert;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }
}

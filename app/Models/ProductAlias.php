<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAlias extends Model
{
    protected $fillable = [
        'product_id',
        'alias_type',
        'value',
        'normalized_value',
        'source',
        'status',
    ];

    public static function normalizeValue(string $type, string $value): string
    {
        $trimmed = trim($value);
        if ($type === 'ean') {
            return preg_replace('/\D+/', '', $trimmed) ?? '';
        }

        return strtoupper(preg_replace('/\s+/', ' ', $trimmed) ?? '');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

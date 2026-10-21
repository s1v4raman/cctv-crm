<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_key',
        'status',
        'created_by',
        'phone',
        'address',
    ];

    protected static function booted(): void
    {
        static::saving(function (Vendor $vendor) {
            if (!empty($vendor->name)) {
                $vendor->name = self::cleanName($vendor->name);
                if (empty($vendor->name_key)) {
                    $vendor->name_key = self::normalizeNameKey($vendor->name);
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProjectMaterial::class);
    }
}

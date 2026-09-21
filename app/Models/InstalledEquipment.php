<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstalledEquipment extends Model
{
    protected $table = 'installed_equipment';

    protected $fillable = [
        'lead_id',
        'installation_job_id',
        'product_id',
        'equipment_name',
        'serial_number',
        'mac_address',
        'location_tag',
        'installation_date',
        'manufacturer_warranty_expiry',
        'service_warranty_expiry',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'installation_date' => 'date',
            'manufacturer_warranty_expiry' => 'date',
            'service_warranty_expiry' => 'date',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function installationJob(): BelongsTo
    {
        return $this->belongsTo(InstallationJob::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function rmaClaims(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RmaClaim::class);
    }

    /**
     * Get manufacturer warranty status: active, expiring_soon, expired, none
     */
    public function getMfgWarrantyStatusAttribute(): string
    {
        if (! $this->manufacturer_warranty_expiry) {
            return 'none';
        }

        $now = Carbon::now()->startOfDay();
        $expiry = $this->manufacturer_warranty_expiry->startOfDay();

        if ($expiry->isPast()) {
            return 'expired';
        }

        if ($now->diffInDays($expiry, false) <= 30) {
            return 'expiring_soon';
        }

        return 'active';
    }

    /**
     * Get service warranty status: active, expiring_soon, expired, none
     */
    public function getServiceWarrantyStatusAttribute(): string
    {
        if (! $this->service_warranty_expiry) {
            return 'none';
        }

        $now = Carbon::now()->startOfDay();
        $expiry = $this->service_warranty_expiry->startOfDay();

        if ($expiry->isPast()) {
            return 'expired';
        }

        if ($now->diffInDays($expiry, false) <= 30) {
            return 'expiring_soon';
        }

        return 'active';
    }

    public function getMfgDaysRemainingAttribute(): ?int
    {
        if (! $this->manufacturer_warranty_expiry) {
            return null;
        }

        return (int) Carbon::now()->startOfDay()->diffInDays($this->manufacturer_warranty_expiry->startOfDay(), false);
    }

    public function getServiceDaysRemainingAttribute(): ?int
    {
        if (! $this->service_warranty_expiry) {
            return null;
        }

        return (int) Carbon::now()->startOfDay()->diffInDays($this->service_warranty_expiry->startOfDay(), false);
    }
}

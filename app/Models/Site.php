<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'site_code',
        'name',
        'client_name',
        'contact_person',
        'phone',
        'alt_phone',
        'client_phone',
        'client_email',
        'contact_phone',
        'email',
        'address',
        'area',
        'city',
        'state',
        'pincode',
        'latitude',
        'longitude',
        'google_maps_link',
        'google_maps_url',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(function (Site $site) {
            if (empty($site->contact_person)) {
                $site->contact_person = $site->client_name ?: 'Site Manager';
            }
            if (empty($site->phone)) {
                $site->phone = $site->client_phone ?: ($site->contact_phone ?: '-');
            }
            if (empty($site->client_phone) && !empty($site->phone)) {
                $site->client_phone = $site->phone;
            }
            if (empty($site->google_maps_link) && !empty($site->google_maps_url)) {
                $site->google_maps_link = $site->google_maps_url;
            }
        });
    }

    public static function generateSiteCode(): string
    {
        $year = date('Y');
        $prefix = "SIT-{$year}-";
        $latest = static::where('site_code', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('site_code');

        if ($latest && preg_match('/SIT-\d{4}-(\d+)/', $latest, $matches)) {
            $seq = (int) $matches[1] + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getGoogleMapsUrlAttribute(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }
        if ($this->google_maps_link) {
            return $this->google_maps_link;
        }
        if ($this->address) {
            return "https://www.google.com/maps/search/?api=1&query=" . urlencode($this->address . ', ' . ($this->city ?: ''));
        }
        return null;
    }

    public function getNavigateUrlAttribute(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps/dir/?api=1&destination={$this->latitude},{$this->longitude}";
        }
        if ($this->address) {
            return "https://www.google.com/maps/dir/?api=1&destination=" . urlencode($this->address . ', ' . ($this->city ?: ''));
        }
        return null;
    }
}

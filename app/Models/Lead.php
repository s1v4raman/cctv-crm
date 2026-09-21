<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    protected $fillable = [
        'customer_name', 'phone', 'email', 'site_address', 'source', 'status', 'notes',
    ];

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function siteSurveys(): HasMany
    {
        return $this->hasMany(SiteSurvey::class);
    }

    public function serviceTickets(): HasMany
    {
        return $this->hasMany(ServiceTicket::class);
    }

    public function amcContracts(): HasMany
    {
        return $this->hasMany(AmcContract::class);
    }

    public function activeAmcContract()
    {
        return $this->hasOne(AmcContract::class)
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->latest('end_date');
    }

    public function installedEquipment(): HasMany
    {
        return $this->hasMany(InstalledEquipment::class)->latest();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpDeviceRevealLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_device_id',
        'user_id',
        'revealed_at',
    ];

    protected $casts = [
        'revealed_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(IpDevice::class, 'ip_device_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

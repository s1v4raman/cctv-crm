<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IpDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'device_name',
        'device_type',
        'ip_address',
        'subnet_mask',
        'gateway',
        'port',
        'web_port',
        'rtsp_port',
        'server_port',
        'mac_address',
        'make_model',
        'serial_number',
        'username',
        'password',
        'location',
        'fixed_location',
        'status',
        'nvr_channel',
        'notes',
    ];

    protected $casts = [
        'password' => 'encrypted',
    ];

    public static function deviceTypeOptions(): array
    {
        return [
            'ip_camera'    => 'IP Camera',
            'nvr'          => 'Network Video Recorder (NVR)',
            'dvr'          => 'Digital Video Recorder (DVR)',
            'switch'       => 'Network PoE Switch',
            'router'       => 'Router / Gateway',
            'access_point' => 'Wireless Access Point (AP)',
            'biometric'    => 'Biometric Attendance / Access',
            'intercom'     => 'IP Video Intercom / Door Station',
            'other'        => 'Other IP Device',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function revealLogs(): HasMany
    {
        return $this->hasMany(IpDeviceRevealLog::class);
    }

    public function getDeviceTypeLabelAttribute(): string
    {
        return static::deviceTypeOptions()[$this->device_type] ?? ucfirst(str_replace('_', ' ', $this->device_type));
    }
}

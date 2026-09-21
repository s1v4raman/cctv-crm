<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTicket extends Model
{
    protected $fillable = [
        'ticket_no',
        'lead_id',
        'amc_contract_id',
        'assigned_technician_id',
        'created_by_id',
        'title',
        'issue_type',
        'priority',
        'status',
        'description',
        'scheduled_date',
        'troubleshooting_notes',
        'parts_replaced',
        'resolution_notes',
        'billing_type',
        'cost',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'cost' => 'decimal:2',
    ];

    public static function issueTypeOptions(): array
    {
        return [
            'camera_offline' => 'Camera Offline / No Video Signal',
            'dvr_nvr_beep' => 'DVR / NVR Beeping or HDD Alert',
            'blurry_feed' => 'Blurry Feed / Lens Dirty / Out of Focus',
            'recording_failure' => 'Recording Failure / Playback Issue',
            'power_supply_issue' => 'Power Supply / SMPS / PoE Failure',
            'network_issue' => 'Network / Remote Mobile View Offline',
            'cable_damaged' => 'Cable Damaged / BNC Loose',
            'ptz_control_issue' => 'PTZ Control / Zoom Not Working',
            'other' => 'General / Other Breakdown',
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical / Emergency',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'open' => 'Open',
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            'cancelled' => 'Cancelled',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function amcContract(): BelongsTo
    {
        return $this->belongsTo(AmcContract::class);
    }

    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function getIssueTypeLabelAttribute(): string
    {
        return self::issueTypeOptions()[$this->issue_type] ?? ucfirst(str_replace('_', ' ', $this->issue_type));
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::priorityOptions()[$this->priority] ?? ucfirst($this->priority);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function completionReport()
    {
        return $this->hasOne(JobCompletionReport::class);
    }

    public function rmaClaims(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RmaClaim::class)->latest();
    }
}

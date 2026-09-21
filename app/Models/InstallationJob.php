<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallationJob extends Model
{
    protected $table = 'installation_jobs';

    protected $fillable = [
        'quotation_id', 'job_no', 'scheduled_date', 'assigned_technician_id', 'status', 'installation_notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function assignedTechnician()
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function installedEquipment()
    {
        return $this->hasMany(InstalledEquipment::class);
    }

    public function completionReport()
    {
        return $this->hasOne(JobCompletionReport::class);
    }
}

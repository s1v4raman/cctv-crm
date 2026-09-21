<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmcVisit extends Model
{
    protected $fillable = [
        'amc_contract_id', 'assigned_technician_id', 'scheduled_date', 'status', 'completion_notes', 'completed_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function amcContract()
    {
        return $this->belongsTo(AmcContract::class);
    }

    public function assignedTechnician()
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function completionReport()
    {
        return $this->hasOne(JobCompletionReport::class);
    }
}

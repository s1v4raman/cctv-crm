<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AmcContract extends Model
{
    protected $fillable = [
        'lead_id', 'contract_no', 'start_date', 'end_date', 'value', 'frequency', 'status', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function visits()
    {
        return $this->hasMany(AmcVisit::class);
    }

    public function serviceTickets()
    {
        return $this->hasMany(ServiceTicket::class);
    }

    /**
     * Generate periodic visits based on start/end date and frequency.
     */
    public function generateVisits(): void
    {
        if (!$this->start_date || !$this->end_date) {
            return;
        }

        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);

        $intervalMonths = match ($this->frequency) {
            'monthly' => 1,
            'quarterly' => 3,
            'semi_annually' => 6,
            'annually' => 12,
            default => 3,
        };

        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $this->visits()->create([
                'scheduled_date' => $currentDate->format('Y-m-d'),
                'status' => 'pending',
            ]);
            $currentDate->addMonths($intervalMonths);
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCompletionReport extends Model
{
    use HasFactory;

    protected $table = 'job_completion_reports';

    protected $fillable = [
        'report_no',
        'installation_job_id',
        'service_ticket_id',
        'amc_visit_id',
        'lead_id',
        'technician_id',
        'completion_date',
        'signer_name',
        'signer_designation',
        'signer_phone',
        'customer_rating',
        'customer_feedback',
        'customer_signature',
        'technician_signature',
        'all_cameras_positioned',
        'recording_configured',
        'remote_mobile_app_setup',
        'power_backup_tested',
        'cables_dressed_and_trunked',
        'client_training_completed',
        'work_area_cleaned',
        'warranty_card_handed',
        'work_summary',
        'equipment_tested_notes',
        'status',
    ];

    protected $casts = [
        'completion_date'            => 'datetime',
        'customer_rating'            => 'integer',
        'all_cameras_positioned'     => 'boolean',
        'recording_configured'       => 'boolean',
        'remote_mobile_app_setup'    => 'boolean',
        'power_backup_tested'        => 'boolean',
        'cables_dressed_and_trunked' => 'boolean',
        'client_training_completed'  => 'boolean',
        'work_area_cleaned'          => 'boolean',
        'warranty_card_handed'       => 'boolean',
    ];

    public static function generateReportNo(): string
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return sprintf('JCR-%s-%04d', $year, $count);
    }

    public function installationJob()
    {
        return $this->belongsTo(InstallationJob::class);
    }

    public function serviceTicket()
    {
        return $this->belongsTo(ServiceTicket::class);
    }

    public function amcVisit()
    {
        return $this->belongsTo(AmcVisit::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function photos()
    {
        return $this->hasMany(JobCompletionPhoto::class);
    }

    public function getPassedChecklistCountAttribute(): int
    {
        $items = [
            $this->all_cameras_positioned,
            $this->recording_configured,
            $this->remote_mobile_app_setup,
            $this->power_backup_tested,
            $this->cables_dressed_and_trunked,
            $this->client_training_completed,
            $this->work_area_cleaned,
            $this->warranty_card_handed,
        ];

        return count(array_filter($items));
    }

    public function getChecklistPercentageAttribute(): int
    {
        return (int) round(($this->passed_checklist_count / 8) * 100);
    }

    public function getJobTypeLabelAttribute(): string
    {
        if ($this->installation_job_id) {
            return 'New CCTV Installation';
        }
        if ($this->service_ticket_id) {
            return 'Service & Breakdown Repair';
        }
        if ($this->amc_visit_id) {
            return 'AMC Periodic Servicing Visit';
        }
        return 'General On-Site Service';
    }

    public function getReferenceNoAttribute(): string
    {
        if ($this->installationJob) {
            return $this->installationJob->job_no;
        }
        if ($this->serviceTicket) {
            return $this->serviceTicket->ticket_no;
        }
        if ($this->amcVisit) {
            return $this->amcVisit->amcContract?->contract_no ?? ('VISIT-#' . $this->amcVisit->id);
        }
        return 'N/A';
    }
}

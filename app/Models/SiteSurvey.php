<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteSurvey extends Model
{
    protected $fillable = [
        'lead_id',
        'surveyed_by',
        'survey_date',
        'site_address',
        'contact_person',
        'contact_phone',
        'camera_count_recommended',
        'dvr_location',
        'cable_length_estimate',
        'power_availability',
        'visit_notes',
        'challenges',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'survey_date' => 'date',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function surveyedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surveyed_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(SiteSurveyPhoto::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteSurveyPhoto extends Model
{
    protected $fillable = [
        'site_survey_id',
        'filename',
        'original_name',
        'caption',
        'image_data',
        'mime_type',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(SiteSurvey::class, 'site_survey_id');
    }

    public function url(): string
    {
        return route('site-surveys.photos.show', $this);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCompletionPhoto extends Model
{
    use HasFactory;

    protected $table = 'job_completion_photos';

    protected $fillable = [
        'job_completion_report_id',
        'photo_type',
        'filename',
        'caption',
    ];

    public function jobCompletionReport()
    {
        return $this->belongsTo(JobCompletionReport::class);
    }
}

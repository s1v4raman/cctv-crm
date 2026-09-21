<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RmaStatusLog extends Model
{
    use HasFactory;

    protected $table = 'rma_status_logs';

    protected $fillable = [
        'rma_claim_id',
        'from_status',
        'to_status',
        'notes',
        'changed_by',
    ];

    public function rmaClaim(): BelongsTo
    {
        return $this->belongsTo(RmaClaim::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

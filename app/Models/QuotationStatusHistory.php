<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationStatusHistory extends Model
{
     protected $fillable = [
        'quotation_id','changed_by', 'from_status', 'to_status', 'notes',
    ];

    public function getOldStatusAttribute(): ?string
    {
        return $this->attributes['from_status'] ?? null;
    }

    public function getNewStatusAttribute(): ?string
    {
        return $this->attributes['to_status'] ?? null;
    }

    public function getRemarksAttribute(): ?string
    {
        return $this->attributes['notes'] ?? null;
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

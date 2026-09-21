<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationStatusHistory extends Model
{
     protected $fillable = [
        'quotation_id','changed_by', 'from_status', 'to_status', 'notes',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

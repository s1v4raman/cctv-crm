<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'installation_job_id', 'quotation_id', 'invoice_no', 'invoice_date', 'due_date',
        'subtotal', 'discount', 'tax_percent', 'tax_amount', 'total', 'amount_paid',
        'status', 'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
    ];

    public function installationJob()
    {
        return $this->belongsTo(InstallationJob::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function balanceDue(): float
    {
        return max((float) ($this->total - $this->amount_paid), 0.0);
    }

    public function recalculatePaymentStatus(): void
    {
        $paid = (float) $this->payments()->sum('amount');

        $status = 'unpaid';
        if ($paid >= (float) $this->total) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partially_paid';
        } elseif ($this->due_date && Carbon::parse($this->due_date)->isPast()) {
            $status = 'overdue';
        }

        $this->update(['amount_paid' => $paid, 'status' => $status]);
    }
}

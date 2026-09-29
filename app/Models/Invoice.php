<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'installation_job_id', 'quotation_id', 'invoice_no', 'invoice_date', 'due_date',
        'subtotal', 'discount', 'tax_percent', 'tax_amount', 'place_of_supply',
        'place_of_supply_code', 'is_b2b', 'is_reverse_charge', 'cgst_amount',
        'sgst_amount', 'igst_amount', 'total', 'amount_paid', 'status', 'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'is_b2b' => 'boolean',
        'is_reverse_charge' => 'boolean',
    ];

    public function taxableAmount(): float
    {
        return max((float) ($this->subtotal - $this->discount), 0.0);
    }

    public function isIntraState(?string $companyStateCode = '33'): bool
    {
        $posCode = $this->place_of_supply_code ?: '33';
        return $posCode === ($companyStateCode ?: '33');
    }

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

    public function getBalanceDueAttribute(): float
    {
        return $this->balanceDue();
    }

    public function getLeadAttribute()
    {
        return $this->installationJob?->quotation?->lead ?? $this->quotation?->lead;
    }

    public function getCustomerNameAttribute(): string
    {
        return $this->lead?->customer_name 
            ?? $this->lead?->name 
            ?? 'Customer #' . $this->id;
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

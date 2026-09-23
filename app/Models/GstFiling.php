<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GstFiling extends Model
{
    use HasFactory;

    protected $table = 'gst_filings';

    protected $fillable = [
        'period',
        'gstr1_status',
        'gstr3b_status',
        'total_turnover',
        'taxable_turnover',
        'output_tax',
        'input_tax_credit',
        'net_tax_payable',
        'tax_paid',
        'challan_no',
        'cin_number',
        'filing_date',
        'payment_mode',
        'notes',
        'filed_by',
    ];

    protected $casts = [
        'total_turnover'   => 'decimal:2',
        'taxable_turnover' => 'decimal:2',
        'output_tax'       => 'decimal:2',
        'input_tax_credit' => 'decimal:2',
        'net_tax_payable'  => 'decimal:2',
        'tax_paid'         => 'decimal:2',
        'filing_date'      => 'date',
    ];

    public function filer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filed_by');
    }

    public function getFormattedPeriodAttribute(): string
    {
        try {
            return Carbon::parse($this->period . '-01')->format('F Y');
        } catch (\Throwable $e) {
            return $this->period;
        }
    }
}

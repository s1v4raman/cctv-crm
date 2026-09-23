<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyCashReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reconciliation_no',
        'petty_cash_account_id',
        'custodian_id',
        'reconciliation_date',
        'opening_balance',
        'total_inflow',
        'total_outflow',
        'system_expected_balance',
        'physical_counted_balance',
        'variance_amount',
        'variance_status', // matched, shortage, excess
        'denominations',
        'reconciliation_notes',
        'verified_by',
        'verified_at',
        'status', // draft, submitted, verified, disputed
    ];

    protected function casts(): array
    {
        return [
            'reconciliation_date'      => 'date',
            'opening_balance'          => 'decimal:2',
            'total_inflow'             => 'decimal:2',
            'total_outflow'            => 'decimal:2',
            'system_expected_balance'  => 'decimal:2',
            'physical_counted_balance' => 'decimal:2',
            'variance_amount'          => 'decimal:2',
            'denominations'            => 'array',
            'verified_at'              => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(PettyCashAccount::class, 'petty_cash_account_id');
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'custodian_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Generate unique sequential cash reconciliation number: REC-CASH-YYYYMMDD-XXXX
     */
    public static function generateReconciliationNumber(?Carbon $date = null): string
    {
        $d = $date ?: now();
        $prefix = 'REC-CASH-' . $d->format('Ymd');
        $count = self::where('reconciliation_no', 'like', "{$prefix}-%")->count() + 1;
        return sprintf('%s-%04d', $prefix, $count);
    }

    /**
     * Compute total cash amount from denomination count breakdown
     */
    public static function calculateDenominationTotal(array $denoms): float
    {
        $multiplier = [
            '2000'  => 2000,
            '500'   => 500,
            '200'   => 200,
            '100'   => 100,
            '50'    => 50,
            '20'    => 20,
            '10'    => 10,
            '5'     => 5,
            '2'     => 2,
            '1'     => 1,
            'coins' => 1,
        ];

        $total = 0.0;
        foreach ($multiplier as $key => $val) {
            $count = isset($denoms[$key]) ? (float) $denoms[$key] : 0.0;
            $total += ($count * $val);
        }

        return round($total, 2);
    }
}

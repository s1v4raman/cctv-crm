<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PettyCashAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'account_type', // main_vault, field_wallet, bank_transit
        'custodian_id',
        'current_balance',
        'warning_limit',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'current_balance' => 'decimal:2',
            'warning_limit'   => 'decimal:2',
            'is_active'       => 'boolean',
        ];
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'custodian_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PettyCashTransaction::class, 'petty_cash_account_id')->latest('transaction_date');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(PettyCashTransaction::class, 'destination_account_id')->latest('transaction_date');
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(DailyCashReconciliation::class, 'petty_cash_account_id')->latest('reconciliation_date');
    }

    /**
     * Get or create central Office Safe Vault Account
     */
    public static function getMainVault(): self
    {
        return self::firstOrCreate(
            ['account_type' => 'main_vault'],
            [
                'name'            => 'Head Office Cash Vault / Main Safe',
                'custodian_id'    => null,
                'current_balance' => 0.00,
                'warning_limit'   => 50000.00,
                'is_active'       => true,
                'notes'           => 'Central petty cash holding safe at company headquarters.',
            ]
        );
    }

    /**
     * Get or create field wallet for a specific technician / staff member
     */
    public static function getOrCreateWalletForUser(User $user): self
    {
        return self::firstOrCreate(
            ['custodian_id' => $user->id, 'account_type' => 'field_wallet'],
            [
                'name'            => "Field Wallet - {$user->name}",
                'current_balance' => 0.00,
                'warning_limit'   => 10000.00,
                'is_active'       => true,
                'notes'           => "Active field float wallet assigned to {$user->name} ({$user->role}).",
            ]
        );
    }

    /**
     * Check if custodian field wallet is exceeding recommended float warning limit
     */
    public function isHighFloatRisk(): bool
    {
        return $this->account_type === 'field_wallet' && (float) $this->current_balance > (float) $this->warning_limit;
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseClaim extends Model
{
    use HasFactory;

    protected $table = 'expense_claims';

    protected $fillable = [
        'claim_no',
        'user_id',
        'installation_job_id',
        'service_ticket_id',
        'expense_category',
        'expense_date',
        'amount',
        'travel_distance_km',
        'travel_from',
        'travel_to',
        'rate_per_km',
        'description',
        'receipt_path',
        'status',
        'actioned_by',
        'actioned_at',
        'rejection_reason',
        'payment_method',
        'payment_reference',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'expense_date'       => 'date',
            'amount'             => 'decimal:2',
            'travel_distance_km' => 'decimal:2',
            'rate_per_km'        => 'decimal:2',
            'actioned_at'        => 'datetime',
            'paid_at'            => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function installationJob(): BelongsTo
    {
        return $this->belongsTo(InstallationJob::class, 'installation_job_id');
    }

    public function serviceTicket(): BelongsTo
    {
        return $this->belongsTo(ServiceTicket::class, 'service_ticket_id');
    }

    public function actioner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'paid'      => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
            'approved'  => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-400 border-blue-200 dark:border-blue-800',
            'pending'   => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200 dark:border-amber-800 animate-pulse',
            'rejected'  => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400 border-rose-200 dark:border-rose-800',
            'cancelled' => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-400 border-slate-200 dark:border-slate-700',
            default     => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'paid'      => 'Disbursed / Paid',
            'approved'  => 'Approved (Pending Payout)',
            'pending'   => 'Pending Approval',
            'rejected'  => 'Rejected',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->expense_category) {
            'fuel_travel'         => 'Fuel & Travel (KM)',
            'hardware_tools'      => 'Hardware Tools & Cables',
            'food_lodging'        => 'Meals & On-Site Allowance',
            'toll_parking'        => 'Toll & Parking Charges',
            'emergency_materials' => 'Emergency Site Supplies',
            default               => ucfirst(str_replace('_', ' ', $this->expense_category)),
        };
    }

    public function getCategoryBadgeClassAttribute(): string
    {
        return match ($this->expense_category) {
            'fuel_travel'         => 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 border-blue-200 dark:border-blue-800',
            'hardware_tools'      => 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-400 border-purple-200 dark:border-purple-800',
            'food_lodging'        => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200 dark:border-amber-800',
            'toll_parking'        => 'bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-400 border-teal-200 dark:border-teal-800',
            'emergency_materials' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 border-rose-200 dark:border-rose-800',
            default               => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    /**
     * Generate unique sequential Expense Claim reference, e.g. EXP-202609-001
     */
    public static function generateClaimNumber(?string $date = null): string
    {
        $d = $date ? Carbon::parse($date) : now();
        $prefix = 'EXP-' . $d->format('Ym') . '-';
        $last = static::where('claim_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('claim_no');

        if ($last) {
            $num = (int) substr($last, strlen($prefix)) + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}

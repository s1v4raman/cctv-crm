<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected $fillable = [
        'name', 'email', 'password', 'role', 'lead_id',
    ];

    public function lead(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the associated Lead record for this user, matching direct lead_id or email, or auto-provision for customers.
     */
    public function getCustomerLead(): ?Lead
    {
        if ($this->lead_id && $this->lead) {
            return $this->lead;
        }

        $lead = Lead::where('email', $this->email)->first();
        if ($lead) {
            $this->update(['lead_id' => $lead->id]);
            return $lead;
        }

        if ($this->isCustomer()) {
            $lead = Lead::create([
                'customer_name' => $this->name,
                'email'         => $this->email,
                'phone'         => 'Pending update',
                'source'        => 'customer_portal',
                'status'        => 'contacted',
                'notes'         => 'Registered customer account via Customer Client Portal.',
            ]);

            $this->update(['lead_id' => $lead->id]);
            return $lead;
        }

        return null;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isTechnician(): bool
    {
        return $this->role === 'technician';
    }

    public function isInternal(): bool
    {
        return in_array($this->role, ['admin', 'staff', 'technician']);
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function salaryStructure(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(EmployeeSalary::class);
    }

    public function payrolls(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function todayAttendance(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(EmployeeAttendance::class)->whereDate('date', now()->toDateString());
    }

    public function leaveRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function expenseClaims(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExpenseClaim::class);
    }

    /**
     * Compute annual leave quota and consumed days for an employee.
     */
    public function getLeaveSummary(?int $year = null): array
    {
        $year = $year ?? (int) now()->format('Y');

        $approvedLeaves = $this->leaveRequests()
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->get();

        $casualUsed = (float) $approvedLeaves->where('leave_type', 'casual')->sum('days_count');
        $sickUsed   = (float) $approvedLeaves->where('leave_type', 'sick')->sum('days_count');
        $earnedUsed = (float) $approvedLeaves->where('leave_type', 'earned')->sum('days_count');
        $lwpUsed    = (float) $approvedLeaves->where('leave_type', 'unpaid_lwp')->sum('days_count');
        $emergencyUsed = (float) $approvedLeaves->where('leave_type', 'emergency')->sum('days_count');

        $casualQuota = 12.0;
        $sickQuota   = 6.0;
        $earnedQuota = 15.0;

        return [
            'year'            => $year,
            'casual'          => ['quota' => $casualQuota, 'used' => $casualUsed, 'remaining' => max(0, $casualQuota - $casualUsed)],
            'sick'            => ['quota' => $sickQuota,   'used' => $sickUsed,   'remaining' => max(0, $sickQuota - $sickUsed)],
            'earned'          => ['quota' => $earnedQuota, 'used' => $earnedUsed, 'remaining' => max(0, $earnedQuota - $earnedUsed)],
            'unpaid_lwp'      => ['used' => $lwpUsed],
            'emergency'       => ['used' => $emergencyUsed],
            'total_used_paid' => $casualUsed + $sickUsed + $earnedUsed,
            'total_remaining_paid' => max(0, ($casualQuota + $sickQuota + $earnedQuota) - ($casualUsed + $sickUsed + $earnedUsed)),
        ];
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payrolls';

    protected $fillable = [
        'payroll_number',
        'user_id',
        'period_type',
        'period_start',
        'period_end',
        'working_days',
        'present_days',
        'half_days',
        'leave_days',
        'absent_days',
        'overtime_hours',
        'basic_pay',
        'overtime_pay',
        'allowances',
        'deductions',
        'net_salary',
        'status',
        'payment_date',
        'payment_reference',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start'   => 'date',
            'period_end'     => 'date',
            'payment_date'   => 'date',
            'working_days'   => 'integer',
            'present_days'   => 'decimal:1',
            'half_days'      => 'integer',
            'leave_days'     => 'decimal:1',
            'absent_days'    => 'decimal:1',
            'overtime_hours' => 'decimal:2',
            'basic_pay'      => 'decimal:2',
            'overtime_pay'   => 'decimal:2',
            'allowances'     => 'decimal:2',
            'deductions'     => 'decimal:2',
            'net_salary'     => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'paid'     => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
            'approved' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-400 border-blue-200 dark:border-blue-800',
            default    => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200 dark:border-amber-800',
        };
    }

    public function getFormattedPeriodAttribute(): string
    {
        if ($this->period_type === 'monthly') {
            return Carbon::parse($this->period_start)->format('F Y');
        }

        return Carbon::parse($this->period_start)->format('d M') . ' — ' . Carbon::parse($this->period_end)->format('d M Y');
    }

    /**
     * Generate a unique formal payroll slip number, e.g. PAY-202609-001
     */
    public static function generatePayrollNumber(?string $date = null): string
    {
        $d = $date ? Carbon::parse($date) : now();
        $prefix = 'PAY-' . $d->format('Ym') . '-';
        $last = static::where('payroll_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('payroll_number');

        if ($last) {
            $num = (int) substr($last, strlen($prefix)) + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get employee mobile phone number for SMS/WhatsApp dispatch.
     */
    public function getEmployeePhone(?string $overridePhone = null): string
    {
        if (!empty($overridePhone)) {
            return preg_replace('/[^0-9]/', '', $overridePhone);
        }

        $phone = '';
        if ($this->user) {
            if ($this->user->lead && !empty($this->user->lead->phone)) {
                $phone = $this->user->lead->phone;
            } elseif ($this->user->salaryStructure && !empty($this->user->salaryStructure->notes)) {
                if (preg_match('/(?:Phone|Mobile|Contact):\s*([0-9+ ]+)/i', $this->user->salaryStructure->notes, $matches)) {
                    $phone = $matches[1];
                }
            }
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 10) {
            return '91' . $digits;
        }

        return $digits;
    }

    /**
     * Generate WhatsApp formatted message for official salary payslip.
     */
    public function getWhatsAppFormattedMessage(): string
    {
        $employeeName = $this->user?->name ?? 'Employee';
        $period = $this->formatted_period;
        $netSalary = number_format((float) $this->net_salary, 2);
        $basicPay = number_format((float) $this->basic_pay, 2);
        $allowances = number_format((float) $this->allowances, 2);
        $deductions = number_format((float) $this->deductions, 2);
        $overtime = number_format((float) $this->overtime_pay, 2);
        $statusUpper = strtoupper($this->status);
        $companyName = config('app.name', 'CCTV Security CRM');
        $pdfUrl = route('finance.payroll.pdf', $this);

        $msg = "🧾 *SALARY PAYSLIP • {$companyName}*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "👤 *Employee:* {$employeeName}\n";
        $msg .= "🔖 *Payslip No:* `{$this->payroll_number}`\n";
        $msg .= "📅 *Pay Period:* {$period}\n";
        $msg .= "📌 *Status:* *{$statusUpper}*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📊 *EARNINGS & BREAKDOWN:*\n";
        $msg .= "  • Present Days: *{$this->present_days} / {$this->working_days} Days*\n";
        $msg .= "  • Basic Earnings: ₹{$basicPay}\n";
        if ((float) $this->overtime_pay > 0) {
            $msg .= "  • Overtime ({$this->overtime_hours} hrs): ₹{$overtime}\n";
        }
        if ((float) $this->allowances > 0) {
            $msg .= "  • Allowances: ₹{$allowances}\n";
        }
        if ((float) $this->deductions > 0) {
            $msg .= "  • Deductions / LWP: -₹{$deductions}\n";
        }
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "💰 *NET TAKE-HOME PAY: ₹{$netSalary}*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        if ($this->payment_date) {
            $msg .= "✅ *Disbursed On:* " . Carbon::parse($this->payment_date)->format('d M Y') . "\n";
        }
        if ($this->payment_reference) {
            $msg .= "🏦 *Bank Ref / UTR:* `{$this->payment_reference}`\n";
        }
        $msg .= "📄 *Download Full PDF Slip:* \n{$pdfUrl}\n\n";
        $msg .= "_For queries, contact the HR & Accounts department._";

        return $msg;
    }

    public function getWhatsAppUrl(?string $customPhone = null, ?string $customMsg = null): string
    {
        $phone = $this->getEmployeePhone($customPhone);
        $msg = $customMsg ?: $this->getWhatsAppFormattedMessage();
        return 'https://api.whatsapp.com/send?phone=' . urlencode($phone) . '&text=' . rawurlencode($msg);
    }

    public function getWhatsAppWebUrl(?string $customPhone = null, ?string $customMsg = null): string
    {
        $phone = $this->getEmployeePhone($customPhone);
        $msg = $customMsg ?: $this->getWhatsAppFormattedMessage();
        return 'https://web.whatsapp.com/send?phone=' . urlencode($phone) . '&text=' . rawurlencode($msg);
    }

    public function getWhatsAppAppUrl(?string $customPhone = null, ?string $customMsg = null): string
    {
        $phone = $this->getEmployeePhone($customPhone);
        $msg = $customMsg ?: $this->getWhatsAppFormattedMessage();
        return 'whatsapp://send?phone=' . urlencode($phone) . '&text=' . rawurlencode($msg);
    }
}

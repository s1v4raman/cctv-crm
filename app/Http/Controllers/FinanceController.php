<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalary;
use App\Models\NotificationLog;
use App\Models\Payroll;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class FinanceController extends Controller
{
    /**
     * Employee Salary Master Configuration.
     */
    public function salaries(Request $request): View
    {
        $roleFilter = $request->input('role');
        $search = trim((string) $request->input('search', ''));

        $query = User::whereIn('role', ['admin', 'staff', 'technician'])
            ->with('salaryStructure')
            ->orderBy('name');

        if ($roleFilter && in_array($roleFilter, ['admin', 'staff', 'technician'])) {
            $query->where('role', $roleFilter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate(15)->withQueryString();

        // Macro financial KPIs
        $allInternal = User::whereIn('role', ['admin', 'staff', 'technician'])->with('salaryStructure')->get();
        $totalMonthlyPayroll = $allInternal->sum(fn($e) => (float) ($e->salaryStructure?->base_salary_monthly ?? 0));
        $totalWeeklyPayroll  = $allInternal->sum(fn($e) => (float) ($e->salaryStructure?->weekly_rate ?? ($e->salaryStructure?->base_salary_monthly ? $e->salaryStructure->base_salary_monthly / 4.33 : 0)));
        $totalDailyPayroll   = $allInternal->sum(fn($e) => (float) ($e->salaryStructure?->daily_rate ?? ($e->salaryStructure?->base_salary_monthly ? $e->salaryStructure->base_salary_monthly / 26 : 0)));
        $configuredCount     = $allInternal->filter(fn($e) => $e->salaryStructure && $e->salaryStructure->base_salary_monthly > 0)->count();
        $avgMonthlySalary    = $configuredCount > 0 ? round($totalMonthlyPayroll / $configuredCount, 2) : 0;

        return view('finance.salaries', compact(
            'employees',
            'roleFilter',
            'search',
            'totalMonthlyPayroll',
            'totalWeeklyPayroll',
            'totalDailyPayroll',
            'configuredCount',
            'avgMonthlySalary'
        ));
    }

    /**
     * Save or update employee salary structure.
     */
    public function updateSalary(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'base_salary_monthly'  => ['required', 'numeric', 'min:0'],
            'daily_rate'           => ['nullable', 'numeric', 'min:0'],
            'weekly_rate'          => ['nullable', 'numeric', 'min:0'],
            'hourly_rate'          => ['nullable', 'numeric', 'min:0'],
            'overtime_hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'travel_allowance'     => ['nullable', 'numeric', 'min:0'],
            'special_allowance'    => ['nullable', 'numeric', 'min:0'],
            'deductions'           => ['nullable', 'numeric', 'min:0'],
            'payment_method'       => ['required', 'in:bank_transfer,upi,cash,cheque'],
            'bank_name'            => ['nullable', 'string', 'max:100'],
            'bank_account_number'  => ['nullable', 'string', 'max:50'],
            'bank_ifsc'            => ['nullable', 'string', 'max:30'],
            'upi_id'               => ['nullable', 'string', 'max:100'],
            'notes'                => ['nullable', 'string', 'max:500'],
        ]);

        $salary = EmployeeSalary::firstOrNew(['user_id' => $user->id]);
        $salary->fill([
            'base_salary_monthly'  => $validated['base_salary_monthly'],
            'daily_rate'           => $validated['daily_rate'] ?? 0,
            'weekly_rate'          => $validated['weekly_rate'] ?? 0,
            'hourly_rate'          => $validated['hourly_rate'] ?? 0,
            'overtime_hourly_rate' => $validated['overtime_hourly_rate'] ?? 0,
            'travel_allowance'     => $validated['travel_allowance'] ?? 0,
            'special_allowance'    => $validated['special_allowance'] ?? 0,
            'deductions'           => $validated['deductions'] ?? 0,
            'payment_method'       => $validated['payment_method'],
            'bank_name'            => $validated['bank_name'] ?? null,
            'bank_account_number'  => $validated['bank_account_number'] ?? null,
            'bank_ifsc'            => $validated['bank_ifsc'] ?? null,
            'upi_id'               => $validated['upi_id'] ?? null,
            'notes'                => $validated['notes'] ?? null,
        ]);

        // Auto derive missing rates (daily = monthly/26, weekly = daily*6, etc.)
        $salary->autoDeriveRates();
        $salary->save();

        return back()->with('status', "✅ Salary structure configured for {$user->name}. Daily: ₹{$salary->daily_rate}, Weekly: ₹{$salary->weekly_rate}.");
    }

    /**
     * Payroll Listing & Disbursal Hub.
     */
    public function payrollIndex(Request $request): View
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $statusFilter  = $request->input('status');
        $employeeId    = $request->input('user_id');

        $query = Payroll::with(['user', 'creator'])->latest();

        if ($selectedMonth) {
            $monthStart = Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString();
            $monthEnd   = Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString();
            $query->whereBetween('period_start', [$monthStart, $monthEnd]);
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($employeeId) {
            $query->where('user_id', $employeeId);
        }

        $payrolls = $query->paginate(20)->withQueryString();

        // Metrics for the selected period
        $periodStart = Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString();
        $periodEnd   = Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString();
        $monthPayrolls = Payroll::whereBetween('period_start', [$periodStart, $periodEnd])->get();

        $totalNetPayroll     = $monthPayrolls->sum('net_salary');
        $totalPaidPayroll    = $monthPayrolls->where('status', 'paid')->sum('net_salary');
        $totalPendingPayroll = $monthPayrolls->where('status', '!=', 'paid')->sum('net_salary');
        $totalOvertimePaid   = $monthPayrolls->sum('overtime_pay');
        $totalAllowancesPaid = $monthPayrolls->sum('allowances');

        $internalUsers = User::whereIn('role', ['admin', 'staff', 'technician'])->orderBy('name')->get();

        return view('finance.payroll', compact(
            'payrolls',
            'selectedMonth',
            'statusFilter',
            'employeeId',
            'totalNetPayroll',
            'totalPaidPayroll',
            'totalPendingPayroll',
            'totalOvertimePaid',
            'totalAllowancesPaid',
            'internalUsers'
        ));
    }

    /**
     * Attendance-Synced Payroll Generator.
     */
    public function generatePayroll(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_type'  => ['required', 'in:monthly,weekly,daily'],
            'period_start' => ['required', 'date'],
            'period_end'   => ['required', 'date', 'after_or_equal:period_start'],
            'user_id'      => ['nullable', 'exists:users,id'],
        ]);

        $periodStart = Carbon::parse($validated['period_start'])->toDateString();
        $periodEnd   = Carbon::parse($validated['period_end'])->toDateString();
        $periodType  = $validated['period_type'];

        $employeeQuery = User::whereIn('role', ['admin', 'staff', 'technician'])->with('salaryStructure');
        if (!empty($validated['user_id'])) {
            $employeeQuery->where('id', $validated['user_id']);
        }
        $employees = $employeeQuery->get();

        $generatedCount = 0;

        foreach ($employees as $emp) {
            $salary = $emp->salaryStructure;
            if (!$salary || $salary->base_salary_monthly <= 0) {
                continue; // Skip employees without configured salary
            }

            // Sync with actual EmployeeAttendance records for this date range
            $attendances = EmployeeAttendance::where('user_id', $emp->id)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->get();

            $presentDays = (float) $attendances->whereIn('status', ['present', 'late'])->count();
            $halfDays    = (int) $attendances->where('status', 'half_day')->count();
            $leaveDays   = (float) $attendances->where('status', 'on_leave')->count();
            $absentDays  = (float) $attendances->where('status', 'absent')->count();
            $overtimeHrs = (float) $attendances->sum('overtime_hours');

            // Effective payable days
            $payableDays = $presentDays + ($halfDays * 0.5) + $leaveDays;

            $totalWorkingDays = max(1, Carbon::parse($periodStart)->diffInDays(Carbon::parse($periodEnd)) + 1);

            // Compute pay components
            if ($periodType === 'weekly') {
                $dailyRate = $salary->daily_rate > 0 ? (float) $salary->daily_rate : round((float) $salary->base_salary_monthly / 26, 2);
                $basicPay = round($payableDays * $dailyRate, 2);
                $allowances = round($salary->total_allowances / 4.33, 2);
                $deductions = round((float) $salary->deductions / 4.33, 2);
            } elseif ($periodType === 'daily') {
                $dailyRate = $salary->daily_rate > 0 ? (float) $salary->daily_rate : round((float) $salary->base_salary_monthly / 26, 2);
                $basicPay = round($payableDays * $dailyRate, 2);
                $allowances = round($salary->total_allowances / 26, 2);
                $deductions = round((float) $salary->deductions / 26, 2);
            } else {
                // Monthly default: 26 standard working days basis
                $standardDays = 26;
                $dailyRate = $salary->daily_rate > 0 ? (float) $salary->daily_rate : round((float) $salary->base_salary_monthly / $standardDays, 2);
                
                // If attendance was logged, pay based on attendance, otherwise full monthly base
                if ($attendances->count() > 0) {
                    $basicPay = min((float) $salary->base_salary_monthly, round($payableDays * $dailyRate, 2));
                } else {
                    $basicPay = (float) $salary->base_salary_monthly;
                    $payableDays = 26;
                }

                $allowances = (float) $salary->total_allowances;
                $deductions = (float) $salary->deductions;
            }

            $overtimeRate = $salary->overtime_hourly_rate > 0 ? (float) $salary->overtime_hourly_rate : (float) $salary->hourly_rate * 1.25;
            $overtimePay  = round($overtimeHrs * $overtimeRate, 2);
            $netSalary    = max(0, round($basicPay + $overtimePay + $allowances - $deductions, 2));

            // Upsert Payroll record
            $existing = Payroll::where('user_id', $emp->id)
                ->where('period_type', $periodType)
                ->whereDate('period_start', $periodStart)
                ->whereDate('period_end', $periodEnd)
                ->first();

            if ($existing) {
                $existing->update([
                    'working_days'   => $totalWorkingDays,
                    'present_days'   => $presentDays,
                    'half_days'      => $halfDays,
                    'leave_days'     => $leaveDays,
                    'absent_days'    => $absentDays,
                    'overtime_hours' => $overtimeHrs,
                    'basic_pay'      => $basicPay,
                    'overtime_pay'   => $overtimePay,
                    'allowances'     => $allowances,
                    'deductions'     => $deductions,
                    'net_salary'     => $netSalary,
                    'created_by'     => auth()->id(),
                ]);
            } else {
                Payroll::create([
                    'payroll_number' => Payroll::generatePayrollNumber($periodStart),
                    'user_id'        => $emp->id,
                    'period_type'    => $periodType,
                    'period_start'   => $periodStart,
                    'period_end'     => $periodEnd,
                    'working_days'   => $totalWorkingDays,
                    'present_days'   => $presentDays,
                    'half_days'      => $halfDays,
                    'leave_days'     => $leaveDays,
                    'absent_days'    => $absentDays,
                    'overtime_hours' => $overtimeHrs,
                    'basic_pay'      => $basicPay,
                    'overtime_pay'   => $overtimePay,
                    'allowances'     => $allowances,
                    'deductions'     => $deductions,
                    'net_salary'     => $netSalary,
                    'status'         => 'draft',
                    'created_by'     => auth()->id(),
                ]);
            }

            $generatedCount++;
        }

        return back()->with('status', "🎉 Successfully processed {$generatedCount} payroll slip(s) for the period ({$periodStart} to {$periodEnd}).");
    }

    /**
     * Show official printable salary slip.
     */
    public function showPayroll(Payroll $payroll): View
    {
        $payroll->load(['user.salaryStructure', 'creator']);
        return view('finance.payslip', compact('payroll'));
    }

    /**
     * Update payroll status (Approve or Mark as Paid).
     */
    public function updatePayrollStatus(Request $request, Payroll $payroll): RedirectResponse
    {
        $validated = $request->validate([
            'status'            => ['required', 'in:draft,approved,paid'],
            'payment_date'      => ['nullable', 'date'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'notes'             => ['nullable', 'string', 'max:255'],
        ]);

        $oldStatus = $payroll->status;

        $payroll->update([
            'status'            => $validated['status'],
            'payment_date'      => $validated['status'] === 'paid' ? ($validated['payment_date'] ?? now()->toDateString()) : null,
            'payment_reference' => $validated['payment_reference'] ?? $payroll->payment_reference,
            'notes'             => $validated['notes'] ?? $payroll->notes,
        ]);

        if ($validated['status'] === 'paid' && $oldStatus !== 'paid') {
            try {
                $payroll->load(['user.salaryStructure']);
                if ($payroll->user) {
                    $alertService = app(\App\Services\AlertNotificationService::class);
                    $paymentMethod = $payroll->user->salaryStructure?->payment_method ?? 'Bank Transfer';
                    $alertService->sendAlert('salary_disbursed', $payroll->user, [
                        'employee_name'     => $payroll->user->name,
                        'payroll_number'    => $payroll->payroll_number,
                        'period_month'      => \Carbon\Carbon::parse($payroll->period_start)->format('F Y'),
                        'net_salary'        => '₹' . number_format((float) $payroll->net_salary, 2),
                        'payment_method'    => ucfirst(str_replace('_', ' ', $paymentMethod)),
                        'payment_reference' => $payroll->payment_reference ?: 'Direct Credit',
                    ], $payroll);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Salary disbursal alert failed: " . $e->getMessage());
            }
        }

        return back()->with('status', "✅ Payroll slip #{$payroll->payroll_number} marked as " . ucfirst($payroll->status) . ".");
    }

    /**
     * Download Official Salary Payslip PDF.
     */
    public function downloadPayslipPdf(Payroll $payroll)
    {
        $payroll->load(['user.salaryStructure', 'creator']);
        $pdf = Pdf::loadView('finance.payslip_pdf', compact('payroll'));
        $filename = "Payslip_{$payroll->payroll_number}_{$payroll->user?->name}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Stream Official Salary Payslip PDF in browser.
     */
    public function streamPayslipPdf(Payroll $payroll)
    {
        $payroll->load(['user.salaryStructure', 'creator']);
        $pdf = Pdf::loadView('finance.payslip_pdf', compact('payroll'));
        $filename = "Payslip_{$payroll->payroll_number}.pdf";

        return $pdf->stream($filename);
    }

    /**
     * Dispatch Salary Payslip via WhatsApp.
     */
    public function sendWhatsApp(Request $request, Payroll $payroll)
    {
        $payroll->load(['user.salaryStructure', 'user.lead']);

        $customPhone = $request->input('phone');
        $customMsg   = $request->input('custom_message');

        $phone = $payroll->getEmployeePhone($customPhone);
        $message = $customMsg ?: $payroll->getWhatsAppFormattedMessage();

        // Audit notification log
        try {
            NotificationLog::create([
                'channel'         => 'whatsapp',
                'event_type'      => 'payslip_sent',
                'recipient_type'  => 'employee',
                'recipient_name'  => $payroll->user?->name ?? 'Employee',
                'recipient_phone' => $phone,
                'recipient_email' => $payroll->user?->email,
                'subject'         => "Salary Payslip #{$payroll->payroll_number} ({$payroll->formatted_period})",
                'message_body'    => $message,
                'action_url'      => route('finance.payroll.pdf', $payroll),
                'status'          => 'sent',
                'reference_type'  => get_class($payroll),
                'reference_id'    => $payroll->id,
                'sent_at'         => now(),
                'created_by'      => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Payslip WhatsApp NotificationLog failed: " . $e->getMessage());
        }

        $whatsappUrl = $payroll->getWhatsAppUrl($customPhone, $customMsg);
        $webUrl      = $payroll->getWhatsAppWebUrl($customPhone, $customMsg);
        $appUrl      = $payroll->getWhatsAppAppUrl($customPhone, $customMsg);
        $pdfUrl      = route('finance.payroll.pdf', $payroll);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'       => true,
                'whatsapp_url'  => $whatsappUrl,
                'web_url'       => $webUrl,
                'app_url'       => $appUrl,
                'pdf_url'       => $pdfUrl,
                'phone'         => $phone,
                'employee_name' => $payroll->user?->name ?? 'Employee',
                'message'       => $message,
            ]);
        }

        return redirect()->away($whatsappUrl);
    }

    /**
     * Executive Finance & Salary Analysis Dashboard (Monthly, Weekly, and Per-Day).
     */
    public function analytics(Request $request): View
    {
        // Granularity toggle: 'monthly', 'weekly', 'daily'
        $viewMode = $request->input('mode', 'monthly');
        if (!in_array($viewMode, ['monthly', 'weekly', 'daily'])) {
            $viewMode = 'monthly';
        }

        $internalStaff = User::whereIn('role', ['admin', 'staff', 'technician'])
            ->with('salaryStructure')
            ->orderBy('name')
            ->get();

        // 1. Per-Day Cost Aggregates
        $totalDailyCost = $internalStaff->sum(function ($u) {
            $s = $u->salaryStructure;
            if (!$s) return 0;
            return (float) ($s->daily_rate > 0 ? $s->daily_rate : ($s->base_salary_monthly / 26));
        });

        // 2. Weekly Cost Aggregates
        $totalWeeklyCost = $internalStaff->sum(function ($u) {
            $s = $u->salaryStructure;
            if (!$s) return 0;
            return (float) ($s->weekly_rate > 0 ? $s->weekly_rate : ($s->base_salary_monthly / 4.33));
        });

        // 3. Monthly Cost Aggregates
        $totalMonthlyCost = $internalStaff->sum(fn($u) => (float) ($u->salaryStructure?->base_salary_monthly ?? 0));

        // Role-wise breakdown
        $roleBreakdown = [
            'admin' => [
                'count'        => $internalStaff->where('role', 'admin')->count(),
                'monthly_cost' => $internalStaff->where('role', 'admin')->sum(fn($u) => (float) ($u->salaryStructure?->base_salary_monthly ?? 0)),
                'weekly_cost'  => $internalStaff->where('role', 'admin')->sum(fn($u) => (float) ($u->salaryStructure?->weekly_rate ?? ($u->salaryStructure?->base_salary_monthly ? $u->salaryStructure->base_salary_monthly / 4.33 : 0))),
                'daily_cost'   => $internalStaff->where('role', 'admin')->sum(fn($u) => (float) ($u->salaryStructure?->daily_rate ?? ($u->salaryStructure?->base_salary_monthly ? $u->salaryStructure->base_salary_monthly / 26 : 0))),
            ],
            'staff' => [
                'count'        => $internalStaff->where('role', 'staff')->count(),
                'monthly_cost' => $internalStaff->where('role', 'staff')->sum(fn($u) => (float) ($u->salaryStructure?->base_salary_monthly ?? 0)),
                'weekly_cost'  => $internalStaff->where('role', 'staff')->sum(fn($u) => (float) ($u->salaryStructure?->weekly_rate ?? ($u->salaryStructure?->base_salary_monthly ? $u->salaryStructure->base_salary_monthly / 4.33 : 0))),
                'daily_cost'   => $internalStaff->where('role', 'staff')->sum(fn($u) => (float) ($u->salaryStructure?->daily_rate ?? ($u->salaryStructure?->base_salary_monthly ? $u->salaryStructure->base_salary_monthly / 26 : 0))),
            ],
            'technician' => [
                'count'        => $internalStaff->where('role', 'technician')->count(),
                'monthly_cost' => $internalStaff->where('role', 'technician')->sum(fn($u) => (float) ($u->salaryStructure?->base_salary_monthly ?? 0)),
                'weekly_cost'  => $internalStaff->where('role', 'technician')->sum(fn($u) => (float) ($u->salaryStructure?->weekly_rate ?? ($u->salaryStructure?->base_salary_monthly ? $u->salaryStructure->base_salary_monthly / 4.33 : 0))),
                'daily_cost'   => $internalStaff->where('role', 'technician')->sum(fn($u) => (float) ($u->salaryStructure?->daily_rate ?? ($u->salaryStructure?->base_salary_monthly ? $u->salaryStructure->base_salary_monthly / 26 : 0))),
            ],
        ];

        // Last 6 months payroll historical trend
        $monthlyTrendLabels = [];
        $monthlyTrendValues = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthObj = now()->subMonths($i);
            $monthLabel = $monthObj->format('M Y');
            $start = $monthObj->copy()->startOfMonth()->toDateString();
            $end   = $monthObj->copy()->endOfMonth()->toDateString();

            $actualPaid = Payroll::whereBetween('period_start', [$start, $end])->sum('net_salary');
            // If no payroll was generated yet, fallback to base monthly cost as baseline
            $displayValue = $actualPaid > 0 ? $actualPaid : $totalMonthlyCost;

            $monthlyTrendLabels[] = $monthLabel;
            $monthlyTrendValues[] = round($displayValue, 2);
        }

        // Current month attendance efficiency
        $currentMonthStart = now()->startOfMonth()->toDateString();
        $currentMonthEnd   = now()->endOfMonth()->toDateString();
        $attendanceDaysThisMonth = EmployeeAttendance::whereBetween('date', [$currentMonthStart, $currentMonthEnd])
            ->whereIn('status', ['present', 'late'])
            ->count();

        return view('finance.analytics', compact(
            'viewMode',
            'internalStaff',
            'totalDailyCost',
            'totalWeeklyCost',
            'totalMonthlyCost',
            'roleBreakdown',
            'monthlyTrendLabels',
            'monthlyTrendValues',
            'attendanceDaysThisMonth'
        ));
    }
}

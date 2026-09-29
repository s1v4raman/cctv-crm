<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalary;
use App\Models\ExpenseClaim;
use App\Models\GstFiling;
use App\Models\InstallationJob;
use App\Models\Invoice;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\PettyCashAccount;
use App\Models\PurchaseOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleHubController extends Controller
{
    /**
     * Display the Dedicated Employee & Workforce Hub.
     */
    public function employeeHub(Request $request): View
    {
        $today = now()->toDateString();
        $currentMonth = now()->format('Y-m');

        // ── Workforce roster with today's attendance ─────────────────────────
        $allInternal = User::whereIn('role', ['admin', 'staff', 'technician'])
            ->with(['salaryStructure', 'attendances' => fn($q) => $q->where('date', $today)])
            ->orderBy('name')
            ->get();

        $totalEmployees     = $allInternal->count();
        $technicianCount    = $allInternal->where('role', 'technician')->count();
        $staffCount         = $allInternal->where('role', 'staff')->count();
        $adminCount         = $allInternal->where('role', 'admin')->count();

        // Today's Attendance
        $todayAttendance    = EmployeeAttendance::where('date', $today)->get();
        $presentCount       = $todayAttendance->whereIn('status', ['present', 'half_day'])->count();
        $lateCount          = $todayAttendance->where('status', 'late')->count();
        $absentCount        = $todayAttendance->where('status', 'absent')->count();
        $leaveCount         = $todayAttendance->whereIn('status', ['leave', 'on_leave'])->count();
        $unmarkedCount      = max(0, $totalEmployees - $todayAttendance->count());

        // ── Employee Roster for table ─────────────────────────────────────────
        $employeeRoster = $allInternal->map(function ($user) use ($today) {
            $att = $user->attendances->first();
            return [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'role'     => $user->role,
                'salary'   => $user->salaryStructure?->base_salary_monthly ?? 0,
                'status'   => $att?->status ?? 'unmarked',
                'clock_in' => $att?->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '—',
                'clock_out'=> $att?->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '—',
            ];
        });

        // ── 7-Day Attendance Trend ────────────────────────────────────────────
        $last7Days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->toDateString());
        $attendanceTrend = $last7Days->map(function ($date) use ($totalEmployees) {
            $rows = EmployeeAttendance::where('date', $date)->get();
            return [
                'date'     => \Carbon\Carbon::parse($date)->format('D d'),
                'present'  => $rows->whereIn('status', ['present', 'half_day', 'late'])->count(),
                'absent'   => $rows->where('status', 'absent')->count(),
                'on_leave' => $rows->whereIn('status', ['leave', 'on_leave'])->count(),
                'unmarked' => max(0, $totalEmployees - $rows->count()),
            ];
        });

        // ── Salary Analytics by Role ──────────────────────────────────────────
        $monthlySalaryBurn = (float) $allInternal->sum(fn($u) => $u->salaryStructure?->base_salary_monthly ?? 0);
        $configuredSalariesCount = $allInternal->filter(fn($u) => $u->salaryStructure && $u->salaryStructure->base_salary_monthly > 0)->count();

        $salaryByRole = [
            'admin'      => (float) $allInternal->where('role', 'admin')->sum(fn($u) => $u->salaryStructure?->base_salary_monthly ?? 0),
            'staff'      => (float) $allInternal->where('role', 'staff')->sum(fn($u) => $u->salaryStructure?->base_salary_monthly ?? 0),
            'technician' => (float) $allInternal->where('role', 'technician')->sum(fn($u) => $u->salaryStructure?->base_salary_monthly ?? 0),
        ];

        // ── Pending Approvals ─────────────────────────────────────────────────
        $pendingLeavesCount   = LeaveRequest::where('status', 'pending')->count();
        $pendingExpensesCount = ExpenseClaim::where('status', 'pending')->count();
        $pendingExpensesAmount = (float) ExpenseClaim::where('status', 'pending')->sum('amount');

        // ── Payroll (current month + last 6 months trend) ─────────────────────
        $currentMonthPayrolls = Payroll::where('payroll_month', $currentMonth)->get();
        $isPayrollGenerated   = $currentMonthPayrolls->isNotEmpty();
        $payrollPaidCount     = $currentMonthPayrolls->where('payment_status', 'paid')->count();
        $payrollTotalAmount   = (float) $currentMonthPayrolls->sum('net_salary');

        $payrollHistory = collect(range(5, 0))->map(function ($m) {
            $month = now()->subMonths($m)->format('Y-m');
            $label = now()->subMonths($m)->format('M y');
            $total = (float) Payroll::where('payroll_month', $month)->sum('net_salary');
            return ['month' => $label, 'total' => $total];
        });

        // ── Recent Leave Requests ─────────────────────────────────────────────
        $recentLeaves = LeaveRequest::with('user')
            ->latest()
            ->limit(5)
            ->get();

        return view('hub.employee', compact(
            'totalEmployees', 'technicianCount', 'staffCount', 'adminCount',
            'presentCount', 'lateCount', 'absentCount', 'leaveCount', 'unmarkedCount',
            'employeeRoster', 'attendanceTrend',
            'monthlySalaryBurn', 'configuredSalariesCount', 'salaryByRole',
            'pendingLeavesCount', 'pendingExpensesCount', 'pendingExpensesAmount',
            'isPayrollGenerated', 'payrollPaidCount', 'payrollTotalAmount',
            'payrollHistory', 'recentLeaves'
        ));
    }

    /**
     * Display the Dedicated Finance & Accounting Hub — Analytics Dashboard.
     */
    public function financeHub(Request $request): View
    {
        $today = now()->toDateString();
        $currentMonth = now()->format('Y-m');

        // ── Invoicing & Billing ──────────────────────────────────────────────
        $totalInvoicesCount   = Invoice::count();
        $totalInvoicedAmount  = (float) Invoice::sum('total');
        $totalPaidAmount      = (float) Invoice::sum('amount_paid');
        $totalOutstandingAR   = (float) Invoice::whereIn('status', ['unpaid', 'partially_paid'])->sum('balance_due');
        $overdueInvoicesCount = Invoice::whereIn('status', ['unpaid', 'partially_paid'])
            ->where('due_date', '<', $today)->count();

        // ── Invoice Status Breakdown ─────────────────────────────────────────
        $invoiceStatusBreakdown = [
            'paid'           => Invoice::where('status', 'paid')->count(),
            'unpaid'         => Invoice::where('status', 'unpaid')->count(),
            'partially_paid' => Invoice::where('status', 'partially_paid')->count(),
            'overdue'        => Invoice::whereIn('status', ['unpaid', 'partially_paid'])
                                    ->where('due_date', '<', $today)->count(),
            'draft'          => Invoice::where('status', 'draft')->count(),
        ];

        // ── 6-Month Revenue Trend ─────────────────────────────────────────────
        $revenueTrend = collect(range(5, 0))->map(function ($m) {
            $start = now()->subMonths($m)->startOfMonth()->toDateString();
            $end   = now()->subMonths($m)->endOfMonth()->toDateString();
            $label = now()->subMonths($m)->format('M y');
            return [
                'month'     => $label,
                'invoiced'  => (float) Invoice::whereBetween('invoice_date', [$start, $end])->sum('total'),
                'collected' => (float) Invoice::whereBetween('invoice_date', [$start, $end])->sum('amount_paid'),
            ];
        });

        // ── AR Aging Buckets (0-30, 31-60, 61-90, 90+) ───────────────────────
        $arAging = [
            '0-30'  => (float) Invoice::whereIn('status', ['unpaid', 'partially_paid'])
                            ->where('due_date', '>=', now()->subDays(30)->toDateString())
                            ->sum('balance_due'),
            '31-60' => (float) Invoice::whereIn('status', ['unpaid', 'partially_paid'])
                            ->whereBetween('due_date', [now()->subDays(60)->toDateString(), now()->subDays(31)->toDateString()])
                            ->sum('balance_due'),
            '61-90' => (float) Invoice::whereIn('status', ['unpaid', 'partially_paid'])
                            ->whereBetween('due_date', [now()->subDays(90)->toDateString(), now()->subDays(61)->toDateString()])
                            ->sum('balance_due'),
            '90+'   => (float) Invoice::whereIn('status', ['unpaid', 'partially_paid'])
                            ->where('due_date', '<', now()->subDays(90)->toDateString())
                            ->sum('balance_due'),
        ];

        // ── Accounts Payable ─────────────────────────────────────────────────
        $totalPayablesAmount  = (float) PurchaseOrder::whereIn('payment_status', ['unpaid', 'partially_paid'])->sum('total_amount');
        $unpaidPoCount        = PurchaseOrder::whereIn('payment_status', ['unpaid', 'partially_paid'])->count();
        $threeWayMatchedCount = PurchaseOrder::where('status', 'received')->where('payment_status', 'paid')->count();

        // ── GST Summary ──────────────────────────────────────────────────────
        $totalOutputGst  = (float) Invoice::sum('tax_amount');
        $totalInputGst   = (float) PurchaseOrder::where('status', 'received')->sum('tax_amount');
        $netGstLiability = max(0, $totalOutputGst - $totalInputGst);

        // ── Job Costing / Profitability ───────────────────────────────────────
        $jobsCount          = InstallationJob::count();
        $completedJobsCount = InstallationJob::where('status', 'completed')->count();

        // ── Petty Cash ────────────────────────────────────────────────────────
        $pettyCashAccounts      = PettyCashAccount::all();
        $totalPettyCashBalance  = (float) $pettyCashAccounts->sum('current_balance');
        $activePettyAccountsCount = $pettyCashAccounts->count();

        // ── Recent Invoices (last 8) ──────────────────────────────────────────
        $recentInvoices = Invoice::with(['installationJob.quotation.lead', 'quotation.lead'])
            ->latest('invoice_date')
            ->limit(8)
            ->get();

        // ── Top Unpaid Receivables ────────────────────────────────────────────
        $topUnpaid = Invoice::with(['installationJob.quotation.lead', 'quotation.lead'])
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->orderByRaw('(total - amount_paid) DESC')
            ->limit(5)
            ->get();

        return view('hub.finance', compact(
            'totalInvoicesCount', 'totalInvoicedAmount', 'totalPaidAmount',
            'totalOutstandingAR', 'overdueInvoicesCount',
            'invoiceStatusBreakdown', 'revenueTrend', 'arAging',
            'totalPayablesAmount', 'unpaidPoCount', 'threeWayMatchedCount',
            'totalOutputGst', 'totalInputGst', 'netGstLiability',
            'jobsCount', 'completedJobsCount',
            'totalPettyCashBalance', 'activePettyAccountsCount',
            'recentInvoices', 'topUnpaid'
        ));
    }
}

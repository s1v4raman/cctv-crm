<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAttendance;
use App\Models\LeaveRequest;
use App\Models\NotificationLog;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    /**
     * Display the Leave Management Hub.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        
        $selectedYear = (int) $request->input('year', now()->format('Y'));
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $statusFilter = $request->input('status');
        $employeeFilter = $request->input('employee_id');
        $tab = $request->input('tab', $isAdmin ? 'pending' : 'my_leaves');

        // Leave Quotas for current user
        $leaveSummary = $user->getLeaveSummary($selectedYear);

        // Pending Leave Requests for Admin Approval Queue
        $pendingQuery = LeaveRequest::with(['user', 'actioner'])->pending()->orderBy('created_at', 'asc');
        $pendingRequests = $pendingQuery->get();
        $pendingCount = $pendingRequests->count();

        // My Leaves History (for currently logged in user)
        $myLeavesQuery = LeaveRequest::with('actioner')
            ->forUser($user->id)
            ->whereYear('start_date', $selectedYear)
            ->orderByDesc('created_at');

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected', 'cancelled'])) {
            $myLeavesQuery->where('status', $statusFilter);
        }
        $myLeaves = $myLeavesQuery->paginate(10, ['*'], 'my_page')->withQueryString();

        // Master Company-wide Leaves (for Admin / HR oversight)
        $allLeavesQuery = LeaveRequest::with(['user', 'actioner'])->orderByDesc('created_at');

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected', 'cancelled'])) {
            $allLeavesQuery->where('status', $statusFilter);
        }

        if ($employeeFilter) {
            $allLeavesQuery->where('user_id', $employeeFilter);
        }

        if ($selectedMonth) {
            $monthCarbon = Carbon::parse($selectedMonth . '-01');
            $allLeavesQuery->where(function ($q) use ($monthCarbon) {
                $q->whereBetween('start_date', [$monthCarbon->copy()->startOfMonth(), $monthCarbon->copy()->endOfMonth()])
                  ->orWhereBetween('end_date', [$monthCarbon->copy()->startOfMonth(), $monthCarbon->copy()->endOfMonth()]);
            });
        }

        $allLeaves = $allLeavesQuery->paginate(15, ['*'], 'all_page')->withQueryString();

        // Workforce list for filters
        $workforce = User::whereIn('role', ['admin', 'staff', 'technician'])->orderBy('name')->get();

        // Active Leaves Today
        $activeLeavesToday = LeaveRequest::with('user')
            ->approved()
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->get();

        return view('attendance.leaves', compact(
            'user',
            'isAdmin',
            'leaveSummary',
            'pendingRequests',
            'pendingCount',
            'myLeaves',
            'allLeaves',
            'workforce',
            'activeLeavesToday',
            'selectedYear',
            'selectedMonth',
            'statusFilter',
            'employeeFilter',
            'tab'
        ));
    }

    /**
     * Submit a new Leave Request (Employee Self-Service).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'leave_type'        => ['required', 'in:casual,sick,earned,unpaid_lwp,emergency'],
            'start_date'        => ['required', 'date', 'after_or_equal:today - 30 days'],
            'end_date'          => ['required', 'date', 'after_or_equal:start_date'],
            'is_half_day'       => ['nullable', 'boolean'],
            'half_day_session'  => ['nullable', 'required_if:is_half_day,1,true', 'in:morning,afternoon'],
            'reason'            => ['required', 'string', 'min:5', 'max:1000'],
            'attachment'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $isHalfDay = !empty($validated['is_half_day']);

        if ($isHalfDay) {
            $daysCount = 0.5;
            $endDate = $startDate->copy(); // Force single day for half-day
        } else {
            // Calculate working days excluding Sundays
            $period = CarbonPeriod::create($startDate, $endDate);
            $workingDays = 0;
            foreach ($period as $date) {
                if ($date->dayOfWeek !== Carbon::SUNDAY) {
                    $workingDays++;
                }
            }
            $daysCount = max(1.0, (float) $workingDays);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave_attachments', 'public');
        }

        $leave = LeaveRequest::create([
            'user_id'          => Auth::id(),
            'leave_type'       => $validated['leave_type'],
            'start_date'       => $startDate->toDateString(),
            'end_date'         => $endDate->toDateString(),
            'days_count'       => $daysCount,
            'is_half_day'      => $isHalfDay,
            'half_day_session' => $isHalfDay ? $validated['half_day_session'] : null,
            'reason'           => $validated['reason'],
            'attachment_path'  => $attachmentPath,
            'status'           => 'pending',
        ]);

        // Audit notification log
        try {
            NotificationLog::create([
                'channel'        => 'system',
                'event_type'     => 'leave_applied',
                'recipient_type' => 'admin',
                'recipient_name' => 'Admin Team',
                'subject'        => "New Leave Request from " . Auth::user()->name,
                'message_body'   => Auth::user()->name . " applied for {$leave->days_count} day(s) {$leave->leave_type_label} from {$startDate->format('d M Y')} to {$endDate->format('d M Y')}.",
                'action_url'     => route('leaves.index', ['tab' => 'pending']),
                'status'         => 'sent',
                'reference_type' => get_class($leave),
                'reference_id'   => $leave->id,
                'sent_at'        => now(),
                'created_by'     => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            // Non-blocking log
        }

        return redirect()
            ->route('leaves.index', ['tab' => 'my_leaves'])
            ->with('status', 'Your leave request has been submitted successfully and is pending admin approval.');
    }

    /**
     * Admin 1-Click Approve Leave Request.
     */
    public function approve(LeaveRequest $leave): RedirectResponse
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized. Only Administrators can approve leave requests.');
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'This leave request has already been processed.');
        }

        DB::transaction(function () use ($leave) {
            $leave->update([
                'status'      => 'approved',
                'actioned_by' => Auth::id(),
                'actioned_at' => now(),
            ]);

            // Auto-synchronize and mark Employee Attendance records as on_leave
            $period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($period as $date) {
                // Skip Sundays for automatic on-leave records
                if ($date->dayOfWeek === Carbon::SUNDAY && !$leave->is_half_day) {
                    continue;
                }

                $attendanceStatus = $leave->is_half_day ? 'half_day' : 'on_leave';
                $sessionNote = $leave->is_half_day ? " [{$leave->half_day_session} session]" : '';

                EmployeeAttendance::updateOrCreate(
                    [
                        'user_id' => $leave->user_id,
                        'date'    => $date->toDateString(),
                    ],
                    [
                        'status'        => $attendanceStatus,
                        'notes'         => "Approved {$leave->leave_type_label}{$sessionNote} (Ref #LR-{$leave->id}): {$leave->reason}",
                        'marked_by'     => Auth::id(),
                        'total_hours'   => $leave->is_half_day ? 4.0 : 0.0,
                        'location_type' => 'office',
                    ]
                );
            }
        });

        // Audit notification log to employee
        try {
            NotificationLog::create([
                'channel'         => 'system',
                'event_type'      => 'leave_approved',
                'recipient_type'  => 'employee',
                'recipient_name'  => $leave->user->name,
                'recipient_email' => $leave->user->email,
                'recipient_phone' => $leave->user->salaryStructure?->notes ?? null,
                'subject'         => "Leave Request Approved (Ref #LR-{$leave->id})",
                'message_body'    => "Your request for {$leave->days_count} day(s) {$leave->leave_type_label} has been approved by " . Auth::user()->name . ".",
                'action_url'      => route('leaves.index', ['tab' => 'my_leaves']),
                'status'          => 'sent',
                'reference_type'  => get_class($leave),
                'reference_id'    => $leave->id,
                'sent_at'         => now(),
                'created_by'      => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            // Non-blocking log
        }

        return back()->with('status', "Leave Request #LR-{$leave->id} for {$leave->user->name} has been APPROVED and attendance updated.");
    }

    /**
     * Admin Reject Leave Request with Reason.
     */
    public function reject(Request $request, LeaveRequest $leave): RedirectResponse
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized. Only Administrators can reject leave requests.');
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'This leave request has already been processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $leave->update([
            'status'           => 'rejected',
            'actioned_by'      => Auth::id(),
            'actioned_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Audit notification log to employee
        try {
            NotificationLog::create([
                'channel'         => 'system',
                'event_type'      => 'leave_rejected',
                'recipient_type'  => 'employee',
                'recipient_name'  => $leave->user->name,
                'recipient_email' => $leave->user->email,
                'subject'         => "Leave Request Rejected (Ref #LR-{$leave->id})",
                'message_body'    => "Your request for {$leave->days_count} day(s) {$leave->leave_type_label} was rejected. Reason: " . $validated['rejection_reason'],
                'action_url'      => route('leaves.index', ['tab' => 'my_leaves']),
                'status'          => 'sent',
                'reference_type'  => get_class($leave),
                'reference_id'    => $leave->id,
                'sent_at'         => now(),
                'created_by'      => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            // Non-blocking log
        }

        return back()->with('status', "Leave Request #LR-{$leave->id} has been rejected.");
    }

    /**
     * Employee Cancel Pending Leave Request.
     */
    public function cancel(LeaveRequest $leave): RedirectResponse
    {
        $user = Auth::user();

        if ($leave->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized. You can only cancel your own leave requests.');
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending leave requests can be cancelled.');
        }

        $leave->update([
            'status' => 'cancelled',
        ]);

        return back()->with('status', 'Your leave request has been cancelled.');
    }
}

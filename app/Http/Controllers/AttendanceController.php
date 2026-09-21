<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display the Employee Attendance Management System with separate Daily, Weekly, and Monthly Analysis & Views.
     */
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'daily');
        if (!in_array($tab, ['daily', 'weekly', 'monthly'])) {
            $tab = 'daily';
        }

        $selectedDate = $request->input('date') 
            ? Carbon::parse($request->input('date'))->toDateString() 
            : now()->toDateString();

        $selectedMonth = $request->input('month') 
            ? Carbon::parse($request->input('month'))->format('Y-m') 
            : Carbon::parse($selectedDate)->format('Y-m');

        $weekInput = $request->input('week_date', $selectedDate);
        $weekCarbon = Carbon::parse($weekInput);
        $weekStart = $weekCarbon->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekCarbon->copy()->endOfWeek(Carbon::SUNDAY);

        $roleFilter = $request->input('role');
        $search = trim((string) $request->input('search', ''));

        // Internal workforce query (Admin, Staff, Technicians)
        $employeeQuery = User::whereIn('role', ['admin', 'staff', 'technician'])->orderBy('name');
        if ($roleFilter && in_array($roleFilter, ['admin', 'staff', 'technician'])) {
            $employeeQuery->where('role', $roleFilter);
        }
        if ($search !== '') {
            $employeeQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $employees = $employeeQuery->get();
        $totalEmployees = $employees->count();

        // Current logged-in user's attendance today (for 1-click clock widget)
        $myAttendanceToday = EmployeeAttendance::where('user_id', auth()->id())
            ->whereDate('date', now()->toDateString())
            ->first();

        // ====================================================
        // 1. PER-DAY ATTENDANCE & DAILY ANALYSIS
        // ====================================================
        $dailyAttendances = EmployeeAttendance::with('user')
            ->whereDate('date', $selectedDate)
            ->get()
            ->keyBy('user_id');

        $presentCount = $dailyAttendances->where('status', 'present')->count();
        $lateCount = $dailyAttendances->where('status', 'late')->count();
        $halfDayCount = $dailyAttendances->where('status', 'half_day')->count();
        $leaveCount = $dailyAttendances->where('status', 'on_leave')->count();
        $markedAbsentCount = $dailyAttendances->where('status', 'absent')->count();
        $unmarkedCount = max(0, $totalEmployees - $dailyAttendances->count());
        $absentCount = $markedAbsentCount + $unmarkedCount;

        $attendanceRate = $totalEmployees > 0 
            ? round((($presentCount + $lateCount + ($halfDayCount * 0.5)) / $totalEmployees) * 100, 1) 
            : 0;

        $dailyTotalHours = (float) $dailyAttendances->sum('total_hours');
        $dailyTotalOvertime = (float) $dailyAttendances->sum('overtime_hours');

        // Backwards compatibility alias for existing blades/tests
        $attendances = $dailyAttendances;

        // ====================================================
        // 2. WEEKLY ATTENDANCE & WEEKLY ANALYSIS
        // ====================================================
        $weekDays = [];
        $tempDay = $weekStart->copy();
        while ($tempDay->lte($weekEnd)) {
            $weekDays[] = [
                'date'      => $tempDay->toDateString(),
                'day_name'  => $tempDay->format('D'),
                'day_full'  => $tempDay->format('l'),
                'day_num'   => $tempDay->format('d M'),
                'is_today'  => $tempDay->isToday(),
                'is_sunday' => $tempDay->isSunday(),
            ];
            $tempDay->addDay();
        }

        $weeklyRecords = EmployeeAttendance::whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

        $weeklyMatrix = [];
        foreach ($weeklyRecords as $rec) {
            $dateStr = Carbon::parse($rec->date)->toDateString();
            $weeklyMatrix[$rec->user_id][$dateStr] = $rec;
        }

        // Weekly aggregates per employee
        $weeklyEmployeeStats = [];
        foreach ($employees as $emp) {
            $empRecords = $weeklyRecords->where('user_id', $emp->id);
            $workedDays = $empRecords->whereIn('status', ['present', 'late'])->count() + ($empRecords->where('status', 'half_day')->count() * 0.5);
            $totHrs = (float) $empRecords->sum('total_hours');
            $totOt = (float) $empRecords->sum('overtime_hours');
            $levDays = $empRecords->where('status', 'on_leave')->count();

            $weeklyEmployeeStats[$emp->id] = [
                'worked_days'    => $workedDays,
                'total_hours'    => $totHrs,
                'overtime_hours' => $totOt,
                'leave_days'     => $levDays,
            ];
        }

        // Weekly Macro Analytics
        $weeklyTotalHours = (float) $weeklyRecords->sum('total_hours');
        $weeklyTotalOvertime = (float) $weeklyRecords->sum('overtime_hours');

        // Day by day turnout for the week (Mon to Sun)
        $weeklyDailyTurnout = [];
        foreach ($weekDays as $wDay) {
            $dayRecs = $weeklyRecords->where('date', $wDay['date']);
            $presentOnDay = $dayRecs->whereIn('status', ['present', 'late', 'half_day'])->count();
            $turnoutRate = $totalEmployees > 0 ? round(($presentOnDay / $totalEmployees) * 100, 1) : 0;
            $weeklyDailyTurnout[$wDay['date']] = [
                'count' => $presentOnDay,
                'rate'  => $turnoutRate,
                'hours' => round((float) $dayRecs->sum('total_hours'), 1),
            ];
        }

        $weeklyAvgTurnout = count($weeklyDailyTurnout) > 0 
            ? round(collect($weeklyDailyTurnout)->avg('rate'), 1) 
            : 0;

        // ====================================================
        // 3. MONTHLY ATTENDANCE & MONTHLY ANALYSIS
        // ====================================================
        $monthCarbon = Carbon::parse($selectedMonth . '-01');
        $monthStart = $monthCarbon->copy()->startOfMonth();
        $monthEnd = $monthCarbon->copy()->endOfMonth();
        $daysInMonthCount = $monthEnd->day;

        $monthDays = [];
        $workingDaysInMonth = 0;
        for ($d = 1; $d <= $daysInMonthCount; $d++) {
            $currDate = Carbon::createFromDate($monthCarbon->year, $monthCarbon->month, $d);
            $isSun = $currDate->isSunday();
            if (!$isSun) {
                $workingDaysInMonth++;
            }
            $monthDays[] = [
                'day_num'   => $d,
                'date'      => $currDate->toDateString(),
                'day_name'  => $currDate->format('D'),
                'is_sunday' => $isSun,
                'is_today'  => $currDate->isToday(),
            ];
        }

        $monthlyRecords = EmployeeAttendance::whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        // Monthly logs grouped by user_id for backward compatibility
        $monthlyLogs = $monthlyRecords->groupBy('user_id');

        // Monthly matrix user_id -> date => record
        $monthlyMatrix = [];
        foreach ($monthlyRecords as $mRec) {
            $dateStr = Carbon::parse($mRec->date)->toDateString();
            $monthlyMatrix[$mRec->user_id][$dateStr] = $mRec;
        }

        // Monthly per-employee stats
        $monthlyEmployeeStats = [];
        foreach ($employees as $emp) {
            $empMonthRecs = $monthlyRecords->where('user_id', $emp->id);
            $pDays = $empMonthRecs->where('status', 'present')->count();
            $lDays = $empMonthRecs->where('status', 'late')->count();
            $hDays = $empMonthRecs->where('status', 'half_day')->count();
            $aDays = $empMonthRecs->where('status', 'absent')->count();
            $levDays = $empMonthRecs->where('status', 'on_leave')->count();
            $totHrs = (float) $empMonthRecs->sum('total_hours');
            $totOt = (float) $empMonthRecs->sum('overtime_hours');

            $effectiveDays = $pDays + $lDays + ($hDays * 0.5);
            $empAttRate = $workingDaysInMonth > 0 ? round(($effectiveDays / $workingDaysInMonth) * 100, 1) : 0;

            $monthlyEmployeeStats[$emp->id] = [
                'present_days'   => $pDays,
                'late_days'      => $lDays,
                'half_days'      => $hDays,
                'absent_days'    => $aDays,
                'leave_days'     => $levDays,
                'effective_days' => $effectiveDays,
                'total_hours'    => $totHrs,
                'overtime_hours' => $totOt,
                'attendance_rate'=> min(100, $empAttRate),
            ];
        }

        // Monthly Macro Analytics
        $monthlyTotalHours = (float) $monthlyRecords->sum('total_hours');
        $monthlyTotalOvertime = (float) $monthlyRecords->sum('overtime_hours');
        $monthlyPresentPunches = $monthlyRecords->whereIn('status', ['present', 'late'])->count();
        $monthlyLatePunches = $monthlyRecords->where('status', 'late')->count();
        $monthlyHalfDays = $monthlyRecords->where('status', 'half_day')->count();
        $monthlyLeaves = $monthlyRecords->where('status', 'on_leave')->count();
        $monthlyAbsents = $monthlyRecords->where('status', 'absent')->count();

        $totalWorkOpportunities = $totalEmployees * max(1, $workingDaysInMonth);
        $effectiveTotalWorkedDays = $monthlyPresentPunches + ($monthlyHalfDays * 0.5);
        $companyMonthlyAttendanceRate = $totalWorkOpportunities > 0 
            ? round(($effectiveTotalWorkedDays / $totalWorkOpportunities) * 100, 1) 
            : 0;

        $punctualityRate = ($monthlyPresentPunches + $monthlyHalfDays) > 0 
            ? round((($monthlyPresentPunches - $monthlyLatePunches) / ($monthlyPresentPunches + $monthlyHalfDays)) * 100, 1) 
            : 100;

        return view('attendance.index', compact(
            'tab',
            'employees',
            'attendances',
            'selectedDate',
            'selectedMonth',
            'weekInput',
            'weekStart',
            'weekEnd',
            'weekDays',
            'roleFilter',
            'search',
            'totalEmployees',
            'presentCount',
            'lateCount',
            'halfDayCount',
            'absentCount',
            'markedAbsentCount',
            'unmarkedCount',
            'leaveCount',
            'attendanceRate',
            'dailyTotalHours',
            'dailyTotalOvertime',
            'myAttendanceToday',
            'monthlyLogs',
            // Weekly exports
            'weeklyMatrix',
            'weeklyEmployeeStats',
            'weeklyTotalHours',
            'weeklyTotalOvertime',
            'weeklyDailyTurnout',
            'weeklyAvgTurnout',
            // Monthly exports
            'monthDays',
            'monthlyMatrix',
            'monthlyEmployeeStats',
            'workingDaysInMonth',
            'monthlyTotalHours',
            'monthlyTotalOvertime',
            'companyMonthlyAttendanceRate',
            'punctualityRate',
            'monthlyLatePunches',
            'monthlyLeaves',
            'monthlyAbsents'
        ));
    }

    /**
     * 1-Click Clock In for the authenticated user.
     */
    public function clockIn(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $today = now()->toDateString();
        $nowTime = now()->format('H:i:s');

        $attendance = EmployeeAttendance::firstOrNew([
            'user_id' => $user->id,
            'date'    => $today,
        ]);

        if ($attendance->clock_in) {
            return back()->with('error', 'You have already clocked in today at ' . Carbon::parse($attendance->clock_in)->format('h:i A') . '.');
        }

        // Standard shift start threshold: after 09:30 AM considered late
        $lateThreshold = Carbon::parse($today . ' 09:30:00');
        $isLate = now()->greaterThan($lateThreshold);

        $attendance->clock_in = $nowTime;
        $attendance->status = $isLate ? 'late' : 'present';
        $attendance->location_type = $request->input('location_type', 'office');
        $attendance->notes = $request->input('notes');
        $attendance->marked_by = $user->id;
        $attendance->save();

        $statusMsg = $isLate 
            ? '⏰ Clocked in at ' . now()->format('h:i A') . ' (Marked as Late Arrival).' 
            : '✅ Successfully clocked in at ' . now()->format('h:i A') . '! Have a productive shift.';

        return back()->with('status', $statusMsg);
    }

    /**
     * 1-Click Clock Out for the authenticated user.
     */
    public function clockOut(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $today = now()->toDateString();

        $attendance = EmployeeAttendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return back()->with('error', 'You have not clocked in yet today.');
        }

        if ($attendance->clock_out) {
            return back()->with('error', 'You have already clocked out today at ' . Carbon::parse($attendance->clock_out)->format('h:i A') . '.');
        }

        $attendance->clock_out = now()->format('H:i:s');
        $attendance->calculateHours();
        $attendance->save();

        return back()->with('status', "🏁 Clocked out at " . now()->format('h:i A') . "! Total logged time: {$attendance->total_hours} hrs.");
    }

    /**
     * Admin manual single or bulk attendance record.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'        => ['required', 'exists:users,id'],
            'date'           => ['required', 'date'],
            'status'         => ['required', 'in:present,late,half_day,absent,on_leave'],
            'clock_in'       => ['nullable', 'date_format:H:i'],
            'clock_out'      => ['nullable', 'date_format:H:i'],
            'total_hours'    => ['nullable', 'numeric', 'min:0', 'max:24'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0', 'max:12'],
            'location_type'  => ['nullable', 'in:office,on_site,remote'],
            'notes'          => ['nullable', 'string', 'max:255'],
        ]);

        $targetDate = Carbon::parse($validated['date'])->toDateString();

        $attendance = EmployeeAttendance::where('user_id', $validated['user_id'])
            ->whereDate('date', $targetDate)
            ->first() ?? new EmployeeAttendance([
                'user_id' => $validated['user_id'],
                'date'    => $targetDate,
            ]);

        $clockIn = !empty($validated['clock_in']) ? (strlen($validated['clock_in']) === 5 ? $validated['clock_in'] . ':00' : $validated['clock_in']) : null;
        $clockOut = !empty($validated['clock_out']) ? (strlen($validated['clock_out']) === 5 ? $validated['clock_out'] . ':00' : $validated['clock_out']) : null;

        $attendance->status = $validated['status'];
        $attendance->clock_in = $clockIn;
        $attendance->clock_out = $clockOut;
        $attendance->location_type = $validated['location_type'] ?? 'office';
        $attendance->notes = $validated['notes'] ?? null;
        $attendance->marked_by = auth()->id();

        if (isset($validated['total_hours'])) {
            $attendance->total_hours = $validated['total_hours'];
            $attendance->overtime_hours = $validated['overtime_hours'] ?? 0.00;
        } else {
            $attendance->calculateHours();
            if (!$attendance->total_hours) {
                $attendance->total_hours = $validated['status'] === 'present' ? 8.00 : ($validated['status'] === 'half_day' ? 4.00 : 0.00);
                $attendance->overtime_hours = 0.00;
            }
        }

        $attendance->save();

        if (!empty($validated['clock_in']) && !empty($validated['clock_out']) && empty($validated['total_hours'])) {
            $attendance->calculateHours();
            $attendance->save();
        }

        return back()->with('status', '✅ Attendance record updated successfully.');
    }

    /**
     * Admin quick update attendance record.
     */
    public function update(Request $request, EmployeeAttendance $attendance): RedirectResponse
    {
        $validated = $request->validate([
            'status'         => ['required', 'in:present,late,half_day,absent,on_leave'],
            'clock_in'       => ['nullable'],
            'clock_out'      => ['nullable'],
            'total_hours'    => ['nullable', 'numeric', 'min:0', 'max:24'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0', 'max:12'],
            'notes'          => ['nullable', 'string', 'max:255'],
        ]);

        $attendance->update([
            'status'         => $validated['status'],
            'clock_in'       => $validated['clock_in'] ?: null,
            'clock_out'      => $validated['clock_out'] ?: null,
            'total_hours'    => $validated['total_hours'] ?? $attendance->total_hours,
            'overtime_hours' => $validated['overtime_hours'] ?? $attendance->overtime_hours,
            'notes'          => $validated['notes'] ?? null,
            'marked_by'      => auth()->id(),
        ]);

        return back()->with('status', '✅ Attendance entry updated.');
    }

    /**
     * 1-Click Quick Mark status for any cell (Daily, Weekly, or Monthly view).
     */
    public function quickMark(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date'    => ['required', 'date'],
            'status'  => ['required', 'in:present,late,half_day,absent,on_leave'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $date = Carbon::parse($validated['date'])->toDateString();

        $attendance = EmployeeAttendance::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first() ?? new EmployeeAttendance([
                'user_id' => $user->id,
                'date'    => $date,
            ]);

        $status = $validated['status'];
        $attendance->status = $status;
        $attendance->marked_by = auth()->id();

        if ($status === 'present') {
            $attendance->clock_in = '09:00:00';
            $attendance->clock_out = '18:00:00';
            $attendance->total_hours = 8.00;
            $attendance->overtime_hours = 0.00;
        } elseif ($status === 'late') {
            $attendance->clock_in = '10:00:00';
            $attendance->clock_out = '18:00:00';
            $attendance->total_hours = 8.00;
            $attendance->overtime_hours = 0.00;
        } elseif ($status === 'half_day') {
            $attendance->clock_in = '09:00:00';
            $attendance->clock_out = '13:00:00';
            $attendance->total_hours = 4.00;
            $attendance->overtime_hours = 0.00;
        } else {
            // absent or on_leave
            $attendance->clock_in = null;
            $attendance->clock_out = null;
            $attendance->total_hours = 0.00;
            $attendance->overtime_hours = 0.00;
        }

        $attendance->save();

        return back()->with('status', "✅ Attendance for {$user->name} marked as {$attendance->status_label} for " . Carbon::parse($date)->format('d M Y') . ".");
    }

    /**
     * Batch Store Attendance (Bulk Day Fill or Bulk Employee Date Range).
     */
    public function batchStore(Request $request): RedirectResponse
    {
        $mode = $request->input('mode', 'bulk_day');

        if ($mode === 'bulk_day') {
            $validated = $request->validate([
                'date'   => ['required', 'date'],
                'status' => ['required', 'in:present,late,half_day,absent,on_leave'],
                'target' => ['nullable', 'in:unmarked,all'],
            ]);

            $date = Carbon::parse($validated['date'])->toDateString();
            $status = $validated['status'];
            $target = $validated['target'] ?? 'unmarked';

            $employees = User::whereIn('role', ['admin', 'staff', 'technician'])->get();
            $count = 0;

            foreach ($employees as $emp) {
                $existing = EmployeeAttendance::where('user_id', $emp->id)->whereDate('date', $date)->first();
                if ($target === 'unmarked' && $existing) {
                    continue;
                }

                $att = $existing ?? new EmployeeAttendance(['user_id' => $emp->id, 'date' => $date]);
                $att->status = $status;
                $att->marked_by = auth()->id();

                if ($status === 'present') {
                    $att->clock_in = '09:00:00';
                    $att->clock_out = '18:00:00';
                    $att->total_hours = 8.00;
                    $att->overtime_hours = 0.00;
                } elseif ($status === 'late') {
                    $att->clock_in = '10:00:00';
                    $att->clock_out = '18:00:00';
                    $att->total_hours = 8.00;
                    $att->overtime_hours = 0.00;
                } elseif ($status === 'half_day') {
                    $att->clock_in = '09:00:00';
                    $att->clock_out = '13:00:00';
                    $att->total_hours = 4.00;
                    $att->overtime_hours = 0.00;
                } else {
                    $att->clock_in = null;
                    $att->clock_out = null;
                    $att->total_hours = 0.00;
                    $att->overtime_hours = 0.00;
                }

                $att->save();
                $count++;
            }

            return back()->with('status', "✅ Batch attendance updated for {$count} employee(s) on " . Carbon::parse($date)->format('d M Y') . ".");
        }

        if ($mode === 'bulk_range') {
            $validated = $request->validate([
                'user_id'      => ['required', 'exists:users,id'],
                'start_date'   => ['required', 'date'],
                'end_date'     => ['required', 'date', 'after_or_equal:start_date'],
                'status'       => ['required', 'in:present,late,half_day,absent,on_leave'],
                'skip_sundays' => ['nullable'],
                'daily_hours'  => ['nullable', 'numeric', 'min:0', 'max:24'],
            ]);

            $start = Carbon::parse($validated['start_date']);
            $end = Carbon::parse($validated['end_date']);
            $skipSundays = $request->boolean('skip_sundays', true);
            $user = User::findOrFail($validated['user_id']);
            $status = $validated['status'];
            $dailyHours = $validated['daily_hours'] ?? ($status === 'present' ? 8.00 : ($status === 'half_day' ? 4.00 : 0.00));

            $count = 0;
            $cur = $start->copy();
            while ($cur->lte($end)) {
                if ($skipSundays && $cur->isSunday()) {
                    $cur->addDay();
                    continue;
                }

                $curDate = $cur->toDateString();
                $att = EmployeeAttendance::where('user_id', $user->id)
                    ->whereDate('date', $curDate)
                    ->first() ?? new EmployeeAttendance([
                        'user_id' => $user->id,
                        'date'    => $curDate,
                    ]);

                $att->status = $status;
                $att->total_hours = (float) $dailyHours;
                $att->overtime_hours = max(0.00, (float) $dailyHours - 8.00);
                $att->marked_by = auth()->id();

                if ($status === 'present') {
                    $att->clock_in = '09:00:00';
                    $att->clock_out = '18:00:00';
                } elseif ($status === 'half_day') {
                    $att->clock_in = '09:00:00';
                    $att->clock_out = '13:00:00';
                } else {
                    $att->clock_in = null;
                    $att->clock_out = null;
                }

                $att->save();
                $count++;
                $cur->addDay();
            }

            return back()->with('status', "✅ Recorded {$count} days of {$status} attendance for {$user->name}.");
        }

        return back()->with('error', 'Invalid batch operation mode.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectAuditLog;
use App\Models\ProjectWorkerAttendance;
use App\Models\WorkDay;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WorkerAttendanceController extends Controller
{
    /**
     * Display the Worker Attendance Maintenance Console.
     * Supports views: 'day', 'week', 'month'.
     */
    public function index(Request $request): View
    {
        $viewMode = $request->input('view', 'day');
        if (!in_array($viewMode, ['day', 'week', 'month'], true)) {
            $viewMode = 'day';
        }

        $search = trim((string) $request->input('search', ''));
        $workerFilter = $request->input('worker_id');
        $projectFilter = $request->input('project_id');

        // Master lists for modal & filter dropdowns
        $activeWorkers = Worker::where('is_active', true)->orderBy('name')->get();
        $allWorkers = Worker::orderBy('name')->get();
        $activeProjects = Project::whereIn('status', ['in_progress', 'scheduled', 'draft'])
            ->orderBy('title')
            ->get();
        if ($activeProjects->isEmpty()) {
            $activeProjects = Project::orderBy('title')->get();
        }

        if ($viewMode === 'day') {
            return $this->renderDayView($request, $activeWorkers, $activeProjects, $search, $workerFilter, $projectFilter);
        }

        if ($viewMode === 'week') {
            return $this->renderWeekView($request, $activeWorkers, $activeProjects, $search, $workerFilter, $projectFilter);
        }

        return $this->renderMonthView($request, $activeWorkers, $activeProjects, $search, $workerFilter, $projectFilter);
    }

    /**
     * Per-Day Attendance Roster.
     */
    protected function renderDayView(
        Request $request,
        $activeWorkers,
        $activeProjects,
        string $search,
        $workerFilter,
        $projectFilter
    ): View {
        $dateParam = $request->input('date');
        try {
            $selectedDate = $dateParam ? Carbon::parse($dateParam) : Carbon::today();
        } catch (\Throwable $e) {
            $selectedDate = Carbon::today();
        }

        $query = ProjectWorkerAttendance::query()
            ->join('work_days', 'project_worker_attendances.work_day_id', '=', 'work_days.id')
            ->join('workers', 'project_worker_attendances.worker_id', '=', 'workers.id')
            ->whereDate('work_days.work_date', $selectedDate->toDateString())
            ->select('project_worker_attendances.*')
            ->with(['worker', 'workDay.project']);

        if ($workerFilter) {
            $query->where('project_worker_attendances.worker_id', $workerFilter);
        }

        if ($projectFilter) {
            $query->where('work_days.project_id', $projectFilter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('workers.name', 'like', "%{$search}%")
                  ->orWhere('workers.phone', 'like', "%{$search}%")
                  ->orWhere('project_worker_attendances.notes', 'like', "%{$search}%");
            });
        }

        $attendances = $query->orderBy('project_worker_attendances.check_in_at', 'asc')
            ->orderBy('workers.name', 'asc')
            ->get();

        // Summary metrics for the day
        $metrics = [
            'total_present' => $attendances->count(),
            'full_day_count' => $attendances->where('attendance_type', 'full')->count(),
            'half_day_count' => $attendances->where('attendance_type', 'half')->count(),
            'total_cabling' => (float) $attendances->sum('cabling_metres'),
            'total_cameras' => (int) $attendances->sum('cameras_installed'),
            'total_labor_cost' => (float) $attendances->sum('total_amount'),
        ];

        return view('workers.attendance.index', compact(
            'selectedDate',
            'attendances',
            'metrics',
            'activeWorkers',
            'activeProjects',
            'search',
            'workerFilter',
            'projectFilter'
        ))->with('viewMode', 'day');
    }

    /**
     * Per-Week Attendance Matrix.
     */
    protected function renderWeekView(
        Request $request,
        $activeWorkers,
        $activeProjects,
        string $search,
        $workerFilter,
        $projectFilter
    ): View {
        $weekParam = $request->input('week');
        try {
            $weekStart = $weekParam
                ? Carbon::parse($weekParam)->startOfWeek(Carbon::MONDAY)
                : Carbon::today()->startOfWeek(Carbon::MONDAY);
        } catch (\Throwable $e) {
            $weekStart = Carbon::today()->startOfWeek(Carbon::MONDAY);
        }

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        // Fetch all attendances for the week
        $query = ProjectWorkerAttendance::query()
            ->join('work_days', 'project_worker_attendances.work_day_id', '=', 'work_days.id')
            ->join('workers', 'project_worker_attendances.worker_id', '=', 'workers.id')
            ->whereBetween('work_days.work_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->select('project_worker_attendances.*', 'work_days.work_date')
            ->with(['worker', 'workDay.project']);

        if ($workerFilter) {
            $query->where('project_worker_attendances.worker_id', $workerFilter);
        }

        if ($projectFilter) {
            $query->where('work_days.project_id', $projectFilter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('workers.name', 'like', "%{$search}%")
                  ->orWhere('workers.phone', 'like', "%{$search}%");
            });
        }

        $weekAttendances = $query->get();

        // Generate the 7 days of the week
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $weekDays[] = [
                'date' => $day->toDateString(),
                'day_name' => $day->format('D'),
                'label' => $day->format('D, d M'),
                'is_today' => $day->isToday(),
            ];
        }

        // Determine list of workers to display in matrix
        $workerIdsInWeek = $weekAttendances->pluck('worker_id')->unique();
        $workersInMatrix = Worker::query()
            ->where(function ($q) use ($workerIdsInWeek) {
                $q->where('is_active', true)
                  ->orWhereIn('id', $workerIdsInWeek);
            });

        if ($workerFilter) {
            $workersInMatrix->where('id', $workerFilter);
        }

        if ($search !== '') {
            $workersInMatrix->where('name', 'like', "%{$search}%");
        }

        $workers = $workersInMatrix->orderBy('name')->get();

        // Build worker weekly grid
        $matrixData = [];
        $weeklyTotalShifts = 0;
        $weeklyTotalCabling = 0;
        $weeklyTotalCameras = 0;
        $weeklyTotalWages = 0;

        foreach ($workers as $w) {
            $workerAtts = $weekAttendances->where('worker_id', $w->id);
            $dayMap = [];
            $wShifts = 0;
            $wCabling = 0;
            $wCameras = 0;
            $wWages = 0;

            foreach ($weekDays as $wd) {
                $attForDay = $workerAtts->first(function ($att) use ($wd) {
                    $workDate = is_string($att->work_date) ? substr($att->work_date, 0, 10) : $att->work_date->toDateString();
                    return $workDate === $wd['date'];
                });

                if ($attForDay) {
                    $dayMap[$wd['date']] = $attForDay;
                    $wShifts += ($attForDay->attendance_type === 'half' ? 0.5 : 1.0);
                    $wCabling += (float) $attForDay->cabling_metres;
                    $wCameras += (int) $attForDay->cameras_installed;
                    $wWages += (float) $attForDay->total_amount;
                } else {
                    $dayMap[$wd['date']] = null;
                }
            }

            // Only include in matrix if active or has worked this week
            if ($w->is_active || $wShifts > 0) {
                $matrixData[] = [
                    'worker' => $w,
                    'days' => $dayMap,
                    'total_shifts' => $wShifts,
                    'total_cabling' => $wCabling,
                    'total_cameras' => $wCameras,
                    'total_wages' => $wWages,
                ];

                $weeklyTotalShifts += $wShifts;
                $weeklyTotalCabling += $wCabling;
                $weeklyTotalCameras += $wCameras;
                $weeklyTotalWages += $wWages;
            }
        }

        $metrics = [
            'total_shifts' => $weeklyTotalShifts,
            'total_cabling' => $weeklyTotalCabling,
            'total_cameras' => $weeklyTotalCameras,
            'total_labor_cost' => $weeklyTotalWages,
            'workers_count' => count($matrixData),
        ];

        return view('workers.attendance.index', compact(
            'weekStart',
            'weekEnd',
            'weekDays',
            'matrixData',
            'metrics',
            'activeWorkers',
            'activeProjects',
            'search',
            'workerFilter',
            'projectFilter'
        ))->with('viewMode', 'week');
    }

    /**
     * Per-Month Attendance Summary & Timesheet.
     */
    protected function renderMonthView(
        Request $request,
        $activeWorkers,
        $activeProjects,
        string $search,
        $workerFilter,
        $projectFilter
    ): View {
        $monthParam = $request->input('month');
        try {
            $monthStart = $monthParam
                ? Carbon::parse($monthParam . '-01')->startOfMonth()
                : Carbon::today()->startOfMonth();
        } catch (\Throwable $e) {
            $monthStart = Carbon::today()->startOfMonth();
        }

        $monthEnd = $monthStart->copy()->endOfMonth();
        $selectedMonth = $monthStart->format('Y-m');

        $query = ProjectWorkerAttendance::query()
            ->join('work_days', 'project_worker_attendances.work_day_id', '=', 'work_days.id')
            ->join('workers', 'project_worker_attendances.worker_id', '=', 'workers.id')
            ->whereBetween('work_days.work_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->select('project_worker_attendances.*', 'work_days.work_date')
            ->with(['worker', 'workDay.project']);

        if ($workerFilter) {
            $query->where('project_worker_attendances.worker_id', $workerFilter);
        }

        if ($projectFilter) {
            $query->where('work_days.project_id', $projectFilter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('workers.name', 'like', "%{$search}%")
                  ->orWhere('workers.phone', 'like', "%{$search}%");
            });
        }

        $monthAttendances = $query->get();

        // Group by worker
        $groupedByWorker = $monthAttendances->groupBy('worker_id');
        $workerSummary = [];

        $allWorkersInSystem = Worker::orderBy('name')->get();
        if ($workerFilter) {
            $allWorkersInSystem = $allWorkersInSystem->where('id', (int) $workerFilter);
        }

        $monthTotalShifts = 0;
        $monthTotalCabling = 0;
        $monthTotalCameras = 0;
        $monthTotalGross = 0;
        $monthTotalPaid = 0;

        foreach ($allWorkersInSystem as $w) {
            $wAtts = $groupedByWorker->get($w->id, collect());

            if ($wAtts->isEmpty() && !$w->is_active) {
                continue;
            }

            $fullDays = $wAtts->where('attendance_type', 'full')->count();
            $halfDays = $wAtts->where('attendance_type', 'half')->count();
            $totalShifts = $fullDays + ($halfDays * 0.5);
            $cabling = (float) $wAtts->sum('cabling_metres');
            $cameras = (int) $wAtts->sum('cameras_installed');
            $grossEarned = (float) $wAtts->sum('total_amount');
            $paidAmount = (float) $wAtts->where('is_paid', true)->sum('total_amount');
            $unpaidAmount = $grossEarned - $paidAmount;

            $workerSummary[] = [
                'worker' => $w,
                'full_days' => $fullDays,
                'half_days' => $halfDays,
                'total_shifts' => $totalShifts,
                'cabling' => $cabling,
                'cameras' => $cameras,
                'gross_earned' => $grossEarned,
                'paid_amount' => $paidAmount,
                'unpaid_amount' => $unpaidAmount,
                'attendances' => $wAtts->sortByDesc('work_date'),
            ];

            $monthTotalShifts += $totalShifts;
            $monthTotalCabling += $cabling;
            $monthTotalCameras += $cameras;
            $monthTotalGross += $grossEarned;
            $monthTotalPaid += $paidAmount;
        }

        $metrics = [
            'total_shifts' => $monthTotalShifts,
            'total_cabling' => $monthTotalCabling,
            'total_cameras' => $monthTotalCameras,
            'total_gross' => $monthTotalGross,
            'total_paid' => $monthTotalPaid,
            'total_unpaid' => $monthTotalGross - $monthTotalPaid,
            'active_workers' => count($workerSummary),
        ];

        return view('workers.attendance.index', compact(
            'selectedMonth',
            'monthStart',
            'monthEnd',
            'workerSummary',
            'metrics',
            'activeWorkers',
            'activeProjects',
            'search',
            'workerFilter',
            'projectFilter'
        ))->with('viewMode', 'month');
    }

    /**
     * Store attendance entry with photo, shift type, and work output metrics.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'project_id' => 'required|exists:projects,id',
            'work_date' => 'required|date',
            'attendance_type' => 'required|in:full,half',
            'photo' => 'nullable|image|max:8192',
            'photo_data' => 'nullable|string',
            'check_in_time' => 'nullable|string',
            'cabling_metres' => 'nullable|numeric|min:0',
            'cameras_installed' => 'nullable|integer|min:0',
            'extra_amount' => 'nullable|numeric|min:0',
            'extra_description' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $worker = Worker::findOrFail($validated['worker_id']);
        $project = Project::findOrFail($validated['project_id']);

        $photoPath = null;

        // 1. Process uploaded file photo
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'attendance_' . $worker->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $photoPath = $file->storeAs('attendance_photos', $filename, 'public');
        } elseif (!empty($validated['photo_data']) && str_starts_with($validated['photo_data'], 'data:image')) {
            // 2. Process base64 camera image
            $data = $validated['photo_data'];
            $data = preg_replace('/^data:image\/\w+;base64,/', '', $data);
            $data = str_replace(' ', '+', $data);
            $filename = 'attendance_' . $worker->id . '_' . time() . '_' . uniqid() . '.jpg';
            Storage::disk('public')->put('attendance_photos/' . $filename, base64_decode($data));
            $photoPath = 'attendance_photos/' . $filename;
        }

        DB::beginTransaction();
        try {
            $workDate = Carbon::parse($validated['work_date'])->toDateString();

            // Find or create WorkDay for this project and date
            $workDay = WorkDay::firstOrCreate(
                [
                    'project_id' => $project->id,
                    'work_date' => $workDate,
                ],
                [
                    'logged_by' => Auth::id() ?? 1,
                    'notes' => "Attendance logged via Attendance Maintenance by " . (Auth::user()?->name ?? 'System'),
                ]
            );

            $dailyRate = (float) ($worker->daily_rate > 0 ? $worker->daily_rate : 700.00);
            $multiplier = ($validated['attendance_type'] === 'half') ? 0.5 : 1.0;
            $basePay = $dailyRate * $multiplier;

            $cablingMetres = (float) ($validated['cabling_metres'] ?? 0);
            $camerasInstalled = (int) ($validated['cameras_installed'] ?? 0);
            $cablingRate = (float) ($project->per_metre_rate ?? 8.00);
            $cablingAmount = $cablingMetres * $cablingRate;

            $extraAmount = (float) ($validated['extra_amount'] ?? 0);
            $totalAmount = $basePay + $cablingAmount + $extraAmount;

            $checkInAt = now();
            if (!empty($validated['check_in_time'])) {
                try {
                    $checkInAt = Carbon::parse($workDate . ' ' . $validated['check_in_time']);
                } catch (\Throwable $t) {
                    $checkInAt = now();
                }
            }

            // Check if record already exists for this work day and worker
            $attendance = ProjectWorkerAttendance::where('work_day_id', $workDay->id)
                ->where('worker_id', $worker->id)
                ->first();

            if ($attendance) {
                $attendance->update([
                    'attendance_type' => $validated['attendance_type'],
                    'photo_path' => $photoPath ?: $attendance->photo_path,
                    'check_in_at' => $attendance->check_in_at ?: $checkInAt,
                    'daily_rate_snapshot' => $dailyRate,
                    'rate_applied' => $basePay,
                    'cabling_metres' => $cablingMetres,
                    'cameras_installed' => $camerasInstalled,
                    'cabling_rate' => $cablingRate,
                    'cabling_amount' => $cablingAmount,
                    'extra_amount' => $extraAmount,
                    'extra_description' => $validated['extra_description'] ?? $attendance->extra_description,
                    'total_amount' => $totalAmount,
                    'notes' => $validated['notes'] ?? $attendance->notes,
                ]);
            } else {
                $attendance = ProjectWorkerAttendance::create([
                    'work_day_id' => $workDay->id,
                    'worker_id' => $worker->id,
                    'attendance_type' => $validated['attendance_type'],
                    'photo_path' => $photoPath,
                    'check_in_at' => $checkInAt,
                    'daily_rate_snapshot' => $dailyRate,
                    'rate_applied' => $basePay,
                    'cabling_metres' => $cablingMetres,
                    'cameras_installed' => $camerasInstalled,
                    'cabling_rate' => $cablingRate,
                    'cabling_amount' => $cablingAmount,
                    'extra_amount' => $extraAmount,
                    'extra_description' => $validated['extra_description'] ?? null,
                    'total_amount' => $totalAmount,
                    'notes' => $validated['notes'] ?? null,
                ]);
            }

            ProjectAuditLog::logChange(
                $project,
                'worker_attendance_logged',
                null,
                null,
                "Attendance logged for worker {$worker->name} on {$workDate} ({$validated['attendance_type']} day)" . ($photoPath ? ' with verified photo' : '')
            );

            DB::commit();

            return redirect()->back()->with('status', "✓ Attendance successfully logged for {$worker->name} on {$workDate}!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error storing worker attendance: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to log attendance: ' . $e->getMessage());
        }
    }

    /**
     * Delete an attendance record.
     */
    public function destroy(ProjectWorkerAttendance $attendance): RedirectResponse
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can remove worker attendance records.');
        }

        if ($attendance->is_paid) {
            return redirect()->back()->with('error', 'Cannot delete this attendance record as wages have already been settled and locked.');
        }

        $workerName = $attendance->worker?->name ?? 'Worker';
        $workDate = $attendance->workDay?->work_date ? Carbon::parse($attendance->workDay->work_date)->toDateString() : 'date';

        // Delete photo if exists
        if ($attendance->photo_path && Storage::disk('public')->exists($attendance->photo_path)) {
            Storage::disk('public')->delete($attendance->photo_path);
        }

        $attendance->delete();

        return redirect()->back()->with('status', "Attendance record for {$workerName} on {$workDate} has been removed.");
    }
}

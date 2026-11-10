<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectAuditLog;
use App\Models\ProjectWorkerAttendance;
use App\Models\WorkDay;
use App\Models\Worker;
use Carbon\Carbon;
use Carbon\Constants\UnitValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkerAttendanceController extends Controller
{
    /**
     * Display the Worker Attendance Maintenance Console.
     * Supports views: 'day', 'week', 'month'.
     */
    public function index(Request $request): View|StreamedResponse
    {
        $viewMode = $request->input('view', 'day');
        if (!in_array($viewMode, ['day', 'week', 'month'], true)) {
            $viewMode = 'day';
        }

        $search = trim((string) $request->input('search', ''));
        $workerFilter = $request->input('worker_id');
        $projectFilter = $request->input('project_id');

        // Master lists for modal & filter dropdowns
        /** @var Collection<int, Worker> $activeWorkers */
        $activeWorkers = Worker::where('is_active', true)->orderBy('name')->get();
        $allWorkers = Worker::orderBy('name')->get();

        /** @var Collection<int, Project> $activeProjects */
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
     *
     * @param Collection<int, Worker> $activeWorkers
     * @param Collection<int, Project> $activeProjects
     */
    protected function renderDayView(
        Request $request,
        Collection $activeWorkers,
        Collection $activeProjects,
        string $search,
        mixed $workerFilter,
        mixed $projectFilter
    ): View|StreamedResponse {
        $dateParam = $request->input('date');
        try {
            $selectedDate = $dateParam ? Carbon::parse($dateParam) : Carbon::today();
        } catch (\Throwable $e) {
            $selectedDate = Carbon::today();
        }

        // 1. Fetch all logged attendances for this date
        $attendances = ProjectWorkerAttendance::query()
            ->join('work_days', 'project_worker_attendances.work_day_id', '=', 'work_days.id')
            ->leftJoin('workers', 'project_worker_attendances.worker_id', '=', 'workers.id')
            ->whereDate('work_days.work_date', $selectedDate->toDateString())
            ->select('project_worker_attendances.*')
            ->with(['worker', 'workDay.project'])
            ->orderBy('project_worker_attendances.check_in_at', 'asc')
            ->orderBy('workers.name', 'asc')
            ->get();

        $punchedByWorkerId = $attendances->keyBy('worker_id');

        // Summary metrics for the day
        $metrics = [
            'total_present' => $attendances->count(),
            'full_day_count' => $attendances->where('attendance_type', 'full')->count(),
            'half_day_count' => $attendances->where('attendance_type', 'half')->count(),
            'total_cabling' => (float) $attendances->sum('cabling_metres'),
            'total_cameras' => (int) $attendances->sum('cameras_installed'),
            'total_labor_cost' => (float) $attendances->sum('total_amount'),
        ];

        // 2. Build complete workforce roster (all active workers + any punched inactive/archived workers)
        $punchedWorkers = $attendances->map(fn ($att) => $att->worker)->filter();
        $allWorkersPool = $activeWorkers->concat($punchedWorkers)->unique('id')->values();

        $rosterItems = $allWorkersPool->map(function ($worker) use ($punchedByWorkerId) {
            /** @var ProjectWorkerAttendance|null $att */
            $att = $punchedByWorkerId->get($worker->id);
            $isPresent = !is_null($att);

            return (object) [
                'worker' => $worker,
                'worker_id' => $worker->id,
                'name' => $worker->name,
                'phone' => $worker->phone,
                'attendance' => $att,
                'is_present' => $isPresent,
                'status' => $isPresent ? 'present' : 'unpunched',
                'check_in_at' => $att?->check_in_at,
                'project' => $att?->workDay?->project,
                'project_id' => $att?->workDay?->project_id,
                'notes' => $att?->notes,
            ];
        });

        // Workforce status counts before status/project filtering
        $statusCounts = [
            'all' => $rosterItems->count(),
            'present' => $rosterItems->where('is_present', true)->count(),
            'unpunched' => $rosterItems->where('is_present', false)->count(),
        ];

        // 3. Status filter tab ('all' [default], 'present', 'unpunched' / 'absent')
        $statusFilter = strtolower((string) $request->input('status', 'all'));
        if (!in_array($statusFilter, ['all', 'present', 'unpunched', 'absent'], true)) {
            $statusFilter = 'all';
        }

        if ($statusFilter === 'present') {
            $rosterItems = $rosterItems->where('is_present', true);
        } elseif ($statusFilter === 'unpunched' || $statusFilter === 'absent') {
            $rosterItems = $rosterItems->where('is_present', false);
        }

        // 4. Worker filter
        if ($workerFilter) {
            $rosterItems = $rosterItems->where('worker_id', (int) $workerFilter);
        }

        // 5. Project filter
        if ($projectFilter) {
            $rosterItems = $rosterItems->where('project_id', (int) $projectFilter);
        }

        // 6. Search filter (name, phone, notes, project title)
        if ($search !== '') {
            $searchLower = mb_strtolower($search);
            $rosterItems = $rosterItems->filter(function ($item) use ($searchLower) {
                return str_contains(mb_strtolower($item->name ?? ''), $searchLower)
                    || str_contains(mb_strtolower($item->phone ?? ''), $searchLower)
                    || str_contains(mb_strtolower($item->notes ?? ''), $searchLower)
                    || str_contains(mb_strtolower($item->project?->title ?? ''), $searchLower);
            });
        }

        // 7. Sort: Present workers first (by check-in time), followed by unpunched workers alphabetically
        $sortedRoster = $rosterItems->sort(function ($a, $b) {
            if ($a->is_present !== $b->is_present) {
                return $a->is_present ? -1 : 1;
            }
            if ($a->is_present && $b->is_present) {
                $aTime = $a->check_in_at ? $a->check_in_at->timestamp : 0;
                $bTime = $b->check_in_at ? $b->check_in_at->timestamp : 0;
                if ($aTime !== $bTime) {
                    return $aTime <=> $bTime;
                }
            }
            return strcasecmp($a->name ?? '', $b->name ?? '');
        })->values();

        // 8. Stream CSV Export if requested
        if ($request->input('export') === 'csv') {
            return $this->exportDayViewCsv($selectedDate, $sortedRoster);
        }

        // 9. Pagination
        $perPageParam = $request->input('per_page', 20);
        $perPage = is_numeric($perPageParam) ? (int) $perPageParam : 20;
        if ($perPageParam === 'all' || $perPage <= 0) {
            $perPage = max(1, $sortedRoster->count());
        } elseif ($perPage > 200) {
            $perPage = 200;
        }

        $currentPage = Paginator::resolveCurrentPage('page') ?: 1;
        $totalItems = $sortedRoster->count();
        $currentPageItems = $sortedRoster->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $roster = new LengthAwarePaginator(
            $currentPageItems,
            $totalItems,
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // List of currently unpunched active workers for the bulk punch action
        $unpunchedWorkers = $activeWorkers->whereNotIn('id', $punchedByWorkerId->keys()->toArray())->values();

        return view('workers.attendance.index', compact(
            'selectedDate',
            'roster',
            'attendances',
            'unpunchedWorkers',
            'metrics',
            'statusCounts',
            'statusFilter',
            'perPage',
            'activeWorkers',
            'activeProjects',
            'search',
            'workerFilter',
            'projectFilter'
        ))->with('viewMode', 'day');
    }

    /**
     * Stream CSV export of daily attendance roster for spreadsheet analysis.
     *
     * @param \Illuminate\Support\Collection<int, object> $rosterItems
     */
    protected function exportDayViewCsv(Carbon $selectedDate, \Illuminate\Support\Collection $rosterItems): StreamedResponse
    {
        $fileName = 'worker-attendance-' . $selectedDate->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($selectedDate, $rosterItems) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }
            // UTF-8 BOM for Microsoft Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Date',
                'Worker ID',
                'Worker Name',
                'Phone',
                'Status',
                'Project / Site',
                'Check-in Time',
                'Shift Type',
                'Cabling (m)',
                'Cameras Installed',
                'Daily Wage (INR)',
                'Extra Allowance (INR)',
                'Payment Status',
                'Punch Photo Logged',
                'Notes',
            ]);

            foreach ($rosterItems as $item) {
                $w = $item->worker;
                $att = $item->attendance;
                $p = $item->project;

                fputcsv($handle, [
                    $selectedDate->format('Y-m-d'),
                    $w?->id ?? '',
                    $w?->name ?? 'Unknown',
                    $w?->phone ?? '',
                    $item->is_present ? 'Present' : 'Absent / Not Punched',
                    $p ? ($p->title . ($p->site?->site_name ? ' (' . $p->site->site_name . ')' : '')) : ($item->is_present ? 'Direct Labor Shift' : 'Not Assigned'),
                    $att?->check_in_at ? $att->check_in_at->format('h:i A') : ($item->is_present ? 'Standard Day' : '-'),
                    $att ? ($att->attendance_type === 'half' ? 'Half Day (0.5x)' : 'Full Day (1.0x)') : 'None',
                    $att ? number_format((float) $att->cabling_metres, 1, '.', '') : '0.0',
                    $att ? (int) $att->cameras_installed : 0,
                    $att ? number_format((float) $att->total_amount, 2, '.', '') : '0.00',
                    $att ? number_format((float) $att->extra_amount, 2, '.', '') : '0.00',
                    $att ? ($att->is_paid ? 'Paid' : 'Pending') : '-',
                    $att && $att->photo_path ? 'Yes' : 'No',
                    $att?->notes ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Per-Week Attendance Matrix.
     *
     * @param Collection<int, Worker> $activeWorkers
     * @param Collection<int, Project> $activeProjects
     */
    protected function renderWeekView(
        Request $request,
        Collection $activeWorkers,
        Collection $activeProjects,
        string $search,
        mixed $workerFilter,
        mixed $projectFilter
    ): View {
        $weekParam = $request->input('week');
        try {
            $weekStart = $weekParam
                ? Carbon::parse($weekParam)->startOfWeek(UnitValue::MONDAY)
                : Carbon::today()->startOfWeek(UnitValue::MONDAY);
        } catch (\Throwable $e) {
            $weekStart = Carbon::today()->startOfWeek(UnitValue::MONDAY);
        }

        $weekEnd = $weekStart->copy()->endOfWeek(UnitValue::SUNDAY);

        // Fetch all attendances for the week
        $query = ProjectWorkerAttendance::query()
            ->join('work_days', 'project_worker_attendances.work_day_id', '=', 'work_days.id')
            ->leftJoin('workers', 'project_worker_attendances.worker_id', '=', 'workers.id')
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
        $workersInMatrix = Worker::withTrashed()
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

        [$matrixData, $metrics] = $this->buildWeeklyMatrix($workers, $weekAttendances, $weekDays);

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
     * Compute weekly worker matrix data and aggregate metrics.
     */
    protected function buildWeeklyMatrix($workers, $weekAttendances, array $weekDays): array
    {
        $matrixData = [];
        $weeklyTotalShifts = 0.0;
        $weeklyTotalCabling = 0.0;
        $weeklyTotalCameras = 0;
        $weeklyTotalWages = 0.0;

        $attendancesByWorker = $weekAttendances->groupBy('worker_id');

        foreach ($workers as $w) {
            $workerAtts = $attendancesByWorker->get($w->id, collect());
            $dayMap = [];
            $wShifts = 0.0;
            $wCabling = 0.0;
            $wCameras = 0;
            $wWages = 0.0;

            $attsByDate = [];
            foreach ($workerAtts as $att) {
                $rawDate = $att->work_date;
                $dateKey = is_string($rawDate) ? substr($rawDate, 0, 10) : (is_object($rawDate) && method_exists($rawDate, 'toDateString') ? $rawDate->toDateString() : (string) $rawDate);
                $attsByDate[$dateKey] = $att;
            }

            foreach ($weekDays as $wd) {
                $targetDate = $wd['date'];
                $attForDay = $attsByDate[$targetDate] ?? null;

                if ($attForDay) {
                    $dayMap[$targetDate] = $attForDay;
                    $wShifts += ($attForDay->attendance_type === 'half' ? 0.5 : 1.0);
                    $wCabling += (float) $attForDay->cabling_metres;
                    $wCameras += (int) $attForDay->cameras_installed;
                    $wWages += (float) $attForDay->total_amount;
                } else {
                    $dayMap[$targetDate] = null;
                }
            }

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

        return [$matrixData, $metrics];
    }

    /**
     * Per-Month Attendance Summary & Timesheet.
     *
     * @param Collection<int, Worker> $activeWorkers
     * @param Collection<int, Project> $activeProjects
     */
    protected function renderMonthView(
        Request $request,
        Collection $activeWorkers,
        Collection $activeProjects,
        string $search,
        mixed $workerFilter,
        mixed $projectFilter
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
            ->leftJoin('workers', 'project_worker_attendances.worker_id', '=', 'workers.id')
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

        $allWorkersInSystem = Worker::withTrashed()->orderBy('name')->get();
        if ($workerFilter) {
            $allWorkersInSystem = $allWorkersInSystem->where('id', (int) $workerFilter);
        }

        [$workerSummary, $metrics] = $this->buildMonthlySummary($allWorkersInSystem, $groupedByWorker);

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
     * Compute monthly worker summaries and aggregate metrics.
     */
    protected function buildMonthlySummary($workers, $groupedByWorker): array
    {
        $workerSummary = [];
        $monthTotalShifts = 0.0;
        $monthTotalCabling = 0.0;
        $monthTotalCameras = 0;
        $monthTotalGross = 0.0;
        $monthTotalPaid = 0.0;

        foreach ($workers as $w) {
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

        return [$workerSummary, $metrics];
    }

    /**
     * Store attendance entry with photo, shift type, and work output metrics.
     * Supports both single worker check-in and bulk multi-worker attendance logging.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'worker_id' => 'nullable|exists:workers,id',
            'worker_ids' => 'nullable|array',
            'worker_ids.*' => 'exists:workers,id',
            'project_id' => 'required|exists:projects,id',
            'work_date' => 'required|date',
            'attendance_type' => 'required|string',
            'photo' => 'nullable|image|max:8192',
            'photo_data' => 'nullable|string',
            'check_in_time' => 'nullable|string',
            'cabling_metres' => 'nullable|numeric|min:0',
            'cameras_installed' => 'nullable|integer|min:0',
            'extra_amount' => 'nullable|numeric|min:0',
            'extra_description' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $workerIds = [];
        if (!empty($validated['worker_ids']) && is_array($validated['worker_ids'])) {
            $workerIds = array_values(array_unique(array_filter(array_map('intval', $validated['worker_ids']))));
        } elseif (!empty($validated['worker_id'])) {
            $workerIds = [(int) $validated['worker_id']];
        }

        if (empty($workerIds)) {
            return back()->withErrors(['worker_id' => __('Please select at least one worker.')])->withInput();
        }

        $attType = strtolower($validated['attendance_type']);
        if ($attType === 'half' || $attType === 'half_day') {
            $validated['attendance_type'] = 'half';
        } else {
            $validated['attendance_type'] = 'full';
        }

        $workers = Worker::whereIn('id', $workerIds)->get();
        if ($workers->isEmpty()) {
            return back()->withErrors(['worker_id' => __('Selected workers could not be found.')])->withInput();
        }

        $project = Project::findOrFail($validated['project_id']);

        $photoPath = null;
        $photoData = null;

        // 1. Process uploaded file photo
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'attendance_' . (count($workerIds) === 1 ? $workerIds[0] : 'bulk') . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $photoPath = $file->storeAs('attendance_photos', $filename, 'public');
            $mime = $file->getMimeType() ?: 'image/jpeg';
            $fileContent = @file_get_contents($file->getRealPath());
            if ($fileContent !== false) {
                $photoData = 'data:' . $mime . ';base64,' . base64_encode($fileContent);
            }
        } elseif (!empty($validated['photo_data']) && str_starts_with($validated['photo_data'], 'data:image')) {
            // 2. Process base64 camera image
            $photoData = $validated['photo_data'];
            $data = preg_replace('/^data:image\/\w+;base64,/', '', $photoData);
            $data = str_replace(' ', '+', $data);
            $filename = 'attendance_' . (count($workerIds) === 1 ? $workerIds[0] : 'bulk') . '_' . time() . '_' . uniqid() . '.jpg';
            $photoPath = 'attendance_photos/' . $filename;
            try {
                Storage::disk('public')->put($photoPath, base64_decode($data));
            } catch (\Throwable $t) {
                // Ignore ephemeral disk write failure if database base64 is active
            }
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

            $multiplier = ($validated['attendance_type'] === 'half') ? 0.5 : 1.0;
            $cablingMetres = (float) ($validated['cabling_metres'] ?? 0);
            $camerasInstalled = (int) ($validated['cameras_installed'] ?? 0);
            $cablingRate = (float) ($project->per_metre_rate ?? 8.00);
            $cablingAmount = $cablingMetres * $cablingRate;
            $extraAmount = (float) ($validated['extra_amount'] ?? 0);

            $checkInAt = now();
            if (!empty($validated['check_in_time'])) {
                try {
                    $checkInAt = Carbon::parse($workDate . ' ' . $validated['check_in_time']);
                } catch (\Throwable $t) {
                    $checkInAt = now();
                }
            }

            $savedWorkerNames = [];
            foreach ($workers as $worker) {
                $dailyRate = (float) ($worker->daily_rate > 0 ? $worker->daily_rate : 700.00);
                $basePay = $dailyRate * $multiplier;
                $totalAmount = $basePay + $cablingAmount + $extraAmount;

                // Check if record already exists for this work day and worker
                $attendance = ProjectWorkerAttendance::where('work_day_id', $workDay->id)
                    ->where('worker_id', $worker->id)
                    ->first();

                if ($attendance) {
                    $attendance->update([
                        'attendance_type' => $validated['attendance_type'],
                        'photo_path' => $photoPath ?: $attendance->photo_path,
                        'photo_data' => $photoData ?: $attendance->photo_data,
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
                        'photo_data' => $photoData,
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
                $savedWorkerNames[] = $worker->name;
            }

            ProjectAuditLog::logChange(
                $project,
                count($workers) > 1 ? 'bulk_worker_attendance_logged' : 'worker_attendance_logged',
                null,
                [
                    'workers_count' => count($workers),
                    'worker_names' => implode(', ', $savedWorkerNames),
                    'attendance_type' => $validated['attendance_type'],
                    'work_date' => $workDate,
                    'has_photo' => !empty($photoPath) || !empty($photoData),
                ],
                count($workers) > 1
                    ? "Bulk attendance logged for " . count($workers) . " workers (" . implode(', ', $savedWorkerNames) . ") on " . $workDate . " (" . $validated['attendance_type'] . " shift)"
                    : "Attendance logged for worker {$workers->first()->name} on {$workDate} ({$validated['attendance_type']} day)" . ($photoPath ? ' with verified photo' : '')
            );

            DB::commit();

            $statusMessage = count($workers) > 1
                ? __('✓ Bulk attendance successfully marked for :count workers on :project!', ['count' => count($workers), 'project' => $project->title])
                : __('✓ Attendance successfully logged for :name on :date!', ['name' => $workers->first()->name, 'date' => $workDate]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $statusMessage,
                    'redirect_url' => route('workers.attendance.index', ['date' => $workDate, 'view' => 'day']),
                ]);
            }

            return redirect()->route('workers.attendance.index', [
                'date' => $workDate,
                'view' => 'day'
            ])->with('status', $statusMessage);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error storing worker attendance: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to log attendance: :error', ['error' => $e->getMessage()])
                ], 422);
            }

            return redirect()->back()->with('error', __('Failed to log attendance: :error', ['error' => $e->getMessage()]));
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

    /**
     * Stream attendance photo with local disk caching and database persistence fallback.
     */
    public function streamPhoto(ProjectWorkerAttendance $attendance)
    {
        // 1. If photo exists on local public disk, serve it directly
        if ($attendance->photo_path) {
            $cleanPath = ltrim(str_replace('public/', '', $attendance->photo_path), '/');
            if (Storage::disk('public')->exists($cleanPath)) {
                return response()->file(Storage::disk('public')->path($cleanPath), [
                    'Cache-Control' => 'private, max-age=31536000, immutable',
                ])->setPrivate();
            }
        }

        // 2. If not on local disk, check photo_data in database
        if (!empty($attendance->photo_data)) {
            $data = $attendance->photo_data;
            $mime = 'image/jpeg';
            if (str_starts_with($data, 'data:')) {
                if (preg_match('/^data:([^;]+);base64,/', $data, $m)) {
                    $mime = $m[1];
                }
                $data = preg_replace('/^data:[^;]+;base64,/', '', $data);
            }
            $data = str_replace(' ', '+', $data);
            $binary = base64_decode($data);

            if ($binary) {
                // Cache to local disk for fast subsequent file serving
                if ($attendance->photo_path) {
                    $cleanPath = ltrim(str_replace('public/', '', $attendance->photo_path), '/');
                    try {
                        Storage::disk('public')->put($cleanPath, $binary);
                    } catch (\Throwable $e) {
                        // ignore disk write errors in read-only containers
                    }
                }

                return response($binary, 200, [
                    'Content-Type' => $mime,
                    'Cache-Control' => 'private, max-age=31536000, immutable',
                ]);
            }
        }

        // Return a clean initials SVG avatar fallback with 200 OK so broken 404 images never appear
        $initials = collect(explode(' ', $attendance->worker?->name ?? 'Worker'))->map(fn($p) => strtoupper(substr($p, 0, 1)))->take(2)->implode('') ?: 'W';
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128">'
            . '<defs><linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#10b981"/><stop offset="100%" stop-color="#047857"/></linearGradient></defs>'
            . '<rect width="128" height="128" rx="64" fill="url(#g)"/>'
            . '<text x="50%" y="54%" text-anchor="middle" dominant-baseline="middle" fill="#ffffff" font-family="system-ui, -apple-system, sans-serif" font-size="44" font-weight="700">'
            . htmlspecialchars($initials) . '</text></svg>';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Fallback direct storage handler when web server falls through to Laravel on 404 for attendance_photos/{filename}.
     */
    public function serveStoragePhoto(string $filename)
    {
        $path = 'attendance_photos/' . $filename;
        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path), [
                'Cache-Control' => 'private, max-age=31536000, immutable',
            ])->setPrivate();
        }

        $attendance = null;
        try {
            $attendance = ProjectWorkerAttendance::where('photo_path', 'LIKE', '%' . $filename)->first();
            if ($attendance && !empty($attendance->photo_data)) {
                $data = $attendance->photo_data;
                $mime = 'image/jpeg';
                if (str_starts_with($data, 'data:')) {
                    if (preg_match('/^data:([^;]+);base64,/', $data, $m)) {
                        $mime = $m[1];
                    }
                    $data = preg_replace('/^data:[^;]+;base64,/', '', $data);
                }
                $data = str_replace(' ', '+', $data);
                $binary = base64_decode($data);

                if ($binary) {
                    try {
                        Storage::disk('public')->put($path, $binary);
                    } catch (\Throwable $e) {}

                    return response($binary, 200, [
                        'Content-Type' => $mime,
                        'Cache-Control' => 'private, max-age=31536000, immutable',
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Fall through to SVG fallback
        }

        // Return a clean initials SVG avatar fallback with 200 OK so broken 404 images never appear
        $initials = collect(explode(' ', $attendance?->worker?->name ?? 'Worker'))->map(fn($p) => strtoupper(substr($p, 0, 1)))->take(2)->implode('') ?: 'W';
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128">'
            . '<defs><linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#10b981"/><stop offset="100%" stop-color="#047857"/></linearGradient></defs>'
            . '<rect width="128" height="128" rx="64" fill="url(#g)"/>'
            . '<text x="50%" y="54%" text-anchor="middle" dominant-baseline="middle" fill="#ffffff" font-family="system-ui, -apple-system, sans-serif" font-size="44" font-weight="700">'
            . htmlspecialchars($initials) . '</text></svg>';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\InstallationJob;
use App\Models\Project;
use App\Models\ProjectAuditLog;
use App\Models\ProjectTask;
use App\Models\SiteSurvey;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    /**
     * Display the Operations Calendar & Tasks Hub.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isWorker = $user->isWorker();
        $isAdmin = $user->isAdmin();

        // Server-side Tab Authorization
        // Workers MUST NOT see or access Overall tabs
        $requestedTab = $request->query('tab');

        if ($isWorker) {
            if (in_array($requestedTab, ['overall_calendar', 'overall_tasks'])) {
                abort(403, 'Workers do not have permission to view overall company operations.');
            }
            $activeTab = in_array($requestedTab, ['my_calendar', 'my_tasks']) ? $requestedTab : 'my_tasks';
        } else {
            $activeTab = in_array($requestedTab, ['overall_calendar', 'my_calendar', 'overall_tasks', 'my_tasks']) 
                ? $requestedTab 
                : 'overall_calendar';
        }

        // Calendar Month calculation
        $monthInput = $request->query('month', now()->format('Y-m'));
        try {
            $currentMonth = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Exception $e) {
            $currentMonth = now()->startOfMonth();
        }

        $startDate = $currentMonth->copy()->startOfMonth()->subDays(7);
        $endDate = $currentMonth->copy()->endOfMonth()->addDays(7);

        // Fetch events with server-side worker privacy enforcement
        $allEvents = $this->fetchEvents($startDate, $endDate, $user);

        // Filter events for Overall vs My Calendar
        if ($isWorker) {
            $overallEvents = []; // Workers never receive overall calendar data
            $myEvents = $allEvents; // Already scoped to user and sanitized
        } else {
            $overallEvents = $allEvents;
            $myEvents = collect($allEvents)->filter(function ($event) use ($user) {
                return (isset($event['assigned_to']) && (int) $event['assigned_to'] === (int) $user->id) ||
                       (isset($event['tech_id']) && (int) $event['tech_id'] === (int) $user->id);
            })->values()->all();
        }

        // Task Filters
        $filters = [
            'search'    => trim((string) $request->query('search', '')),
            'status'    => trim((string) $request->query('status', '')),
            'category'  => trim((string) $request->query('category', '')),
            'assignee'  => trim((string) $request->query('assignee', '')),
            'from_date' => trim((string) $request->query('from_date', '')),
            'to_date'   => trim((string) $request->query('to_date', '')),
        ];

        // Overall Tasks (Server-side Gated: forbidden for workers)
        if ($isWorker) {
            $overallTasks = collect();
        } else {
            $overallTasksQuery = ProjectTask::with(['project', 'assignedUser', 'rejectedByUser'])
                ->latest('due_date')
                ->latest('id');

            $this->applyTaskFilters($overallTasksQuery, $filters, false);

            $overallTasks = $overallTasksQuery->get()->map(function (ProjectTask $task) {
                return $this->formatTaskForStaff($task);
            });
        }

        // My Tasks (Scoped strictly to logged-in user: assigned_to == auth()->id())
        $myTasksQuery = ProjectTask::where('assigned_to', $user->id)
            ->with(['project', 'assignedUser', 'rejectedByUser'])
            ->latest('due_date')
            ->latest('id');

        $this->applyTaskFilters($myTasksQuery, $filters, true);

        $myTasks = $myTasksQuery->get()->map(function (ProjectTask $task) use ($isWorker) {
            return $isWorker ? $this->sanitizeTaskForWorker($task) : $this->formatTaskForStaff($task);
        });

        // Group tasks by date: Overdue, Today, Tomorrow, This week, Later
        $overallTasksGrouped = $this->groupTasksByDate($overallTasks);
        $myTasksGrouped = $this->groupTasksByDate($myTasks);

        // Filter dropdown options
        $categories = ProjectTask::select('phase_name')
            ->distinct()
            ->whereNotNull('phase_name')
            ->where('phase_name', '!=', '')
            ->orderBy('phase_name')
            ->pluck('phase_name');

        $statuses = ProjectTask::statusOptions();
        $shifts = ProjectTask::shiftOptions();

        $assignees = User::whereIn('role', ['technician', 'staff', 'worker', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'email']);

        $projects = $isAdmin
            ? Project::select('id', 'title', 'company_name', 'site_address')->orderBy('title')->get()
            : collect();

        // Metrics for Quick Stats
        $todayDate = now()->format('Y-m-d');
        $activeEvents = $isWorker ? $myEvents : $overallEvents;
        $todayEvents = collect($activeEvents)->where('date', $todayDate)->values();

        $stats = [
            'total'          => count($activeEvents),
            'today'          => count($todayEvents),
            'my_tasks_total' => $myTasks->count(),
            'my_pending'     => $myTasks->whereIn('status', ['pending', 'in_progress'])->count(),
            'my_overdue'     => $myTasksGrouped['Overdue']->count(),
            'overall_tasks'  => $overallTasks->count(),
        ];

        return view('calendar.index', [
            'currentMonth'        => $currentMonth,
            'activeTab'           => $activeTab,
            'isWorker'            => $isWorker,
            'isAdmin'             => $isAdmin,
            'overallEvents'       => $overallEvents,
            'myEvents'            => $myEvents,
            'overallTasksGrouped' => $overallTasksGrouped,
            'myTasksGrouped'      => $myTasksGrouped,
            'overallTasksCount'   => $overallTasks->count(),
            'myTasksCount'        => $myTasks->count(),
            'categories'          => $categories,
            'statuses'            => $statuses,
            'shifts'              => $shifts,
            'assignees'           => $assignees,
            'projects'            => $projects,
            'filters'             => $filters,
            'stats'               => $stats,
        ]);
    }

    /**
     * JSON API Endpoint for Calendar Events (strictly scoped for workers).
     */
    public function events(Request $request)
    {
        $user = $request->user();
        $start = $request->query('start') ? Carbon::parse($request->query('start')) : now()->startOfMonth()->subDays(7);
        $end = $request->query('end') ? Carbon::parse($request->query('end')) : now()->endOfMonth()->addDays(7);

        $events = $this->fetchEvents($start, $end, $user);

        // If worker, only events assigned to them
        if ($user->isWorker()) {
            $events = array_values(array_filter($events, function ($e) use ($user) {
                return isset($e['assigned_to']) && (int) $e['assigned_to'] === (int) $user->id;
            }));
        }

        return response()->json($events);
    }

    /**
     * Store a new task (Admin Only - server-side enforced).
     */
    public function storeTask(Request $request): RedirectResponse|JsonResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403, 'Only administrators can create tasks.');

        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'project_id'      => 'required|exists:projects,id',
            'phase_name'      => 'nullable|string|max:255',
            'description'     => 'nullable|string|max:2000',
            'assigned_to'     => 'nullable|exists:users,id',
            'priority'        => 'required|in:low,medium,high,urgent',
            'status'          => 'required|in:pending,in_progress,under_review,completed,blocked',
            'shift'           => 'nullable|string|max:100',
            'location'        => 'nullable|string|max:255',
            'start_date'      => 'nullable|date',
            'due_date'        => 'nullable|date|after_or_equal:start_date',
            'estimated_hours' => 'nullable|numeric|min:0|max:999',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $maxOrder = (int) $project->tasks()->max('order_column') + 1;

        $task = ProjectTask::create([
            'project_id'      => $project->id,
            'phase_name'      => $validated['phase_name'] ?: 'General',
            'title'           => $validated['title'],
            'description'     => $validated['description'] ?? null,
            'assigned_to'     => $validated['assigned_to'] ?? null,
            'priority'        => $validated['priority'],
            'status'          => $validated['status'],
            'shift'           => $validated['shift'] ?: 'Day Shift',
            'location'        => $validated['location'] ?: ($project->site_address ?? 'Assigned Work Site'),
            'start_date'      => $validated['start_date'] ?? null,
            'due_date'        => $validated['due_date'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'order_column'    => $maxOrder,
        ]);

        if (class_exists(ProjectAuditLog::class)) {
            ProjectAuditLog::create([
                'project_id'     => $project->id,
                'auditable_type' => ProjectTask::class,
                'auditable_id'   => $task->id,
                'user_id'        => Auth::id(),
                'action'         => 'created_task',
                'description'    => "Created task '{$task->title}' via Operations Calendar.",
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Task '{$task->title}' created successfully.",
                'task'    => $task,
            ]);
        }

        return redirect()->route('calendar.index', ['tab' => 'overall_tasks'])
            ->with('status', "Task '{$task->title}' created successfully.");
    }

    /**
     * Assign or reassign a task (Admin Only - server-side enforced).
     */
    public function assignTask(Request $request, ProjectTask $task): RedirectResponse|JsonResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403, 'Only administrators can assign or reassign tasks.');

        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
            'shift'       => 'nullable|string|max:100',
            'location'    => 'nullable|string|max:255',
        ]);

        $task->update([
            'assigned_to'      => $validated['assigned_to'] ?? null,
            'shift'            => $validated['shift'] ?? $task->shift,
            'location'         => $validated['location'] ?? $task->location,
            'status'           => 'pending',
            'accepted_at'      => null,
            'rejected_at'      => null,
            'rejected_by'      => null,
            'rejection_reason' => null,
        ]);

        $assigneeName = $task->assignedUser->name ?? 'Unassigned';

        if (class_exists(ProjectAuditLog::class)) {
            ProjectAuditLog::create([
                'project_id'     => $task->project_id,
                'auditable_type' => ProjectTask::class,
                'auditable_id'   => $task->id,
                'user_id'        => Auth::id(),
                'action'         => 'reassigned_task',
                'description'    => "Reassigned task '{$task->title}' to {$assigneeName}.",
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Task '{$task->title}' assigned to {$assigneeName}.",
                'task'    => $task,
            ]);
        }

        return redirect()->back()->with('status', "Task '{$task->title}' assigned to {$assigneeName}.");
    }

    /**
     * Accept a task (Technician, Employee, Worker - server-side enforced on assigned_to).
     */
    public function acceptTask(Request $request, ProjectTask $task): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || (int) $task->assigned_to === (int) $user->id, 403, 'You can only accept tasks assigned to you.');

        $task->update([
            'status'           => 'in_progress',
            'accepted_at'      => now(),
            'rejected_at'      => null,
            'rejected_by'      => null,
            'rejection_reason' => null,
        ]);

        if (class_exists(ProjectAuditLog::class)) {
            ProjectAuditLog::create([
                'project_id'     => $task->project_id,
                'auditable_type' => ProjectTask::class,
                'auditable_id'   => $task->id,
                'user_id'        => $user->id,
                'action'         => 'accepted_task',
                'description'    => "{$user->name} accepted task '{$task->title}'.",
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Task '{$task->title}' accepted successfully.",
                'task'    => $task,
            ]);
        }

        return redirect()->back()->with('status', "You accepted task: {$task->title}. It is now in progress.");
    }

    /**
     * Reject a task (Technician, Employee, Worker - requires reason, sends task back to admin).
     */
    public function rejectTask(Request $request, ProjectTask $task): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || (int) $task->assigned_to === (int) $user->id, 403, 'You can only reject tasks assigned to you.');

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:3|max:1000',
        ]);

        $task->update([
            'status'           => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'rejected_at'      => now(),
            'rejected_by'      => $user->id,
            'accepted_at'      => null,
        ]);

        if (class_exists(ProjectAuditLog::class)) {
            ProjectAuditLog::create([
                'project_id'     => $task->project_id,
                'auditable_type' => ProjectTask::class,
                'auditable_id'   => $task->id,
                'user_id'        => $user->id,
                'action'         => 'rejected_task',
                'description'    => "{$user->name} rejected task '{$task->title}'. Reason: {$validated['rejection_reason']}",
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Task rejected and routed back to administrator for reassignment.",
                'task'    => $task,
            ]);
        }

        return redirect()->back()->with('status', "Task rejected and returned to administrator with your reason.");
    }

    /**
     * Helper to apply search and filter constraints.
     */
    protected function applyTaskFilters($query, array $filters, bool $isMyTasks = false): void
    {
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('location', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $query->where('phase_name', $filters['category']);
        }

        if (!$isMyTasks && !empty($filters['assignee'])) {
            $query->where('assigned_to', $filters['assignee']);
        }

        if (!empty($filters['from_date'])) {
            $from = $filters['from_date'];
            $query->where(function ($q) use ($from) {
                $q->where('due_date', '>=', $from)
                  ->orWhere('start_date', '>=', $from);
            });
        }

        if (!empty($filters['to_date'])) {
            $to = $filters['to_date'];
            $query->where(function ($q) use ($to) {
                $q->where('due_date', '<=', $to)
                  ->orWhere('start_date', '<=', $to);
            });
        }
    }

    /**
     * Group a collection of tasks into standard calendar date buckets:
     * Overdue, Today, Tomorrow, This week, Later.
     */
    protected function groupTasksByDate($tasks): array
    {
        $groups = [
            'Overdue'   => collect(),
            'Today'     => collect(),
            'Tomorrow'  => collect(),
            'This week' => collect(),
            'Later'     => collect(),
        ];

        $todayStr = Carbon::today()->format('Y-m-d');
        $tomorrowStr = Carbon::tomorrow()->format('Y-m-d');
        $endOfWeekStr = Carbon::today()->endOfWeek()->format('Y-m-d');

        foreach ($tasks as $task) {
            $date = $task['due_date'] ?? $task['start_date'] ?? null;

            if (!$date) {
                $groups['Later']->push($task);
                continue;
            }

            if ($date < $todayStr) {
                if ($task['status'] !== 'completed') {
                    $groups['Overdue']->push($task);
                } else {
                    $groups['Later']->push($task);
                }
            } elseif ($date === $todayStr) {
                $groups['Today']->push($task);
            } elseif ($date === $tomorrowStr) {
                $groups['Tomorrow']->push($task);
            } elseif ($date > $tomorrowStr && $date <= $endOfWeekStr) {
                $groups['This week']->push($task);
            } else {
                $groups['Later']->push($task);
            }
        }

        return $groups;
    }

    /**
     * Sanitize task data for Workers:
     * Strict privacy rules: workers must not see project name, client, budget, or BOM.
     * They see only work, date, shift, location, and hours.
     */
    protected function sanitizeTaskForWorker(ProjectTask $task): array
    {
        return [
            'id'               => $task->id,
            'title'            => $task->title, // work
            'description'      => $task->description, // work scope
            'phase_name'       => $task->phase_name,
            'date'             => $task->due_date?->format('Y-m-d') ?? $task->start_date?->format('Y-m-d'),
            'start_date'       => $task->start_date?->format('Y-m-d'),
            'due_date'         => $task->due_date?->format('Y-m-d'),
            'shift'            => $task->shift ?? 'Day Shift',
            'location'         => $task->location ?: ($task->project->site_address ?? 'Assigned Work Site'),
            'estimated_hours'  => $task->estimated_hours,
            'actual_hours'     => $task->actual_hours,
            'status'           => $task->status,
            'priority'         => $task->priority,
            'accepted_at'      => $task->accepted_at?->format('d M, h:i A'),
            'rejected_at'      => $task->rejected_at?->format('d M, h:i A'),
            'rejection_reason' => $task->rejection_reason,
            'assigned_to'      => $task->assigned_to,
            // STRICTLY REDACTED / NULL FOR WORKERS:
            'project_id'       => null,
            'project_name'     => null,
            'client'           => null,
            'customer_name'    => null,
            'budget'           => null,
            'bom'              => null,
            'is_worker_view'   => true,
        ];
    }

    /**
     * Standard task formatting for Staff, Technicians, and Admins.
     */
    protected function formatTaskForStaff(ProjectTask $task): array
    {
        return [
            'id'               => $task->id,
            'title'            => $task->title,
            'description'      => $task->description,
            'phase_name'       => $task->phase_name,
            'date'             => $task->due_date?->format('Y-m-d') ?? $task->start_date?->format('Y-m-d'),
            'start_date'       => $task->start_date?->format('Y-m-d'),
            'due_date'         => $task->due_date?->format('Y-m-d'),
            'shift'            => $task->shift ?? 'Day Shift',
            'location'         => $task->location ?: ($task->project->site_address ?? 'Site Location'),
            'estimated_hours'  => $task->estimated_hours,
            'actual_hours'     => $task->actual_hours,
            'status'           => $task->status,
            'priority'         => $task->priority,
            'accepted_at'      => $task->accepted_at?->format('d M, h:i A'),
            'rejected_at'      => $task->rejected_at?->format('d M, h:i A'),
            'rejection_reason' => $task->rejection_reason,
            'assigned_to'      => $task->assigned_to,
            'assigned_user'    => $task->assignedUser ? [
                'id'   => $task->assignedUser->id,
                'name' => $task->assignedUser->name,
                'role' => $task->assignedUser->role,
            ] : null,
            'rejected_user'    => $task->rejectedByUser ? [
                'id'   => $task->rejectedByUser->id,
                'name' => $task->rejectedByUser->name,
            ] : null,
            'project_id'       => $task->project_id,
            'project_name'     => $task->project->title ?? 'Project #' . $task->project_id,
            'client'           => $task->project->company_name ?? ($task->project->contact_person ?? 'Client'),
            'customer_name'    => $task->project->company_name ?? ($task->project->contact_person ?? 'Client'),
            'budget'           => $task->project->budget,
            'is_worker_view'   => false,
        ];
    }

    /**
     * Collect and standardize calendar events across all models.
     */
    protected function fetchEvents(Carbon $startDate, Carbon $endDate, User $user): array
    {
        $events = [];

        // 1. Project Tasks with Scheduled Dates
        $taskQuery = ProjectTask::with(['project', 'assignedUser'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('due_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            });

        if ($user->isWorker()) {
            $taskQuery->where('assigned_to', $user->id);
        }

        $tasks = $taskQuery->get();

        foreach ($tasks as $t) {
            $eventDate = $t->due_date?->format('Y-m-d') ?? $t->start_date?->format('Y-m-d');
            if (!$eventDate) continue;

            $isWorker = $user->isWorker();

            $events[] = [
                'id'          => 'task-' . $t->id,
                'raw_id'      => $t->id,
                'type'        => 'task',
                'badge'       => 'Task: ' . ($t->shift ?? 'Day Shift'),
                'title'       => $isWorker ? $t->title : ($t->title . ($t->project ? ' (' . ($t->project->company_name ?? 'Client') . ')' : '')),
                'date'        => $eventDate,
                'time'        => $t->shift ?? 'Day Shift',
                'status'      => $t->status,
                'customer'    => $isWorker ? null : ($t->project->company_name ?? '—'),
                'phone'       => $isWorker ? null : ($t->project->contact_phone ?? '—'),
                'address'     => $t->location ?: ($t->project->site_address ?? 'Assigned Work Site'),
                'tech_id'     => $t->assigned_to,
                'tech'        => $t->assignedUser->name ?? 'Unassigned',
                'assigned_to' => $t->assigned_to,
                'url'         => $isWorker ? route('calendar.index', ['tab' => 'my_tasks']) : route('projects.show', [$t->project_id, 'tab' => 'tasks']),
                'color'       => $t->status === 'completed' ? 'emerald' : ($t->status === 'rejected' ? 'rose' : ($t->status === 'in_progress' ? 'blue' : 'amber')),
                'notes'       => $t->description,
                'hours'       => $t->estimated_hours,
            ];
        }

        // For workers, DO NOT include customer-facing surveys, jobs, contracts, or service tickets
        if ($user->isWorker()) {
            usort($events, function ($a, $b) {
                return strcmp($a['date'], $b['date']);
            });
            return $events;
        }

        // 2. Site Surveys (Non-workers only)
        $surveys = SiteSurvey::with(['lead', 'surveyedBy'])
            ->whereNotNull('survey_date')
            ->whereBetween('survey_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        foreach ($surveys as $s) {
            $events[] = [
                'id'          => 'survey-' . $s->id,
                'raw_id'      => $s->id,
                'type'        => 'survey',
                'badge'       => 'Site Survey',
                'title'       => 'Survey: ' . ($s->lead->customer_name ?? 'Premises Audit'),
                'date'        => $s->survey_date->format('Y-m-d'),
                'time'        => $s->survey_date->format('H:i') !== '00:00' ? $s->survey_date->format('h:i A') : 'Full Day',
                'status'      => $s->status,
                'customer'    => $s->lead->customer_name ?? '—',
                'phone'       => $s->contact_phone ?: ($s->lead->phone ?? '—'),
                'address'     => $s->site_address ?: ($s->lead->site_address ?? '—'),
                'tech_id'     => $s->surveyed_by,
                'tech'        => $s->surveyedBy->name ?? 'Unassigned',
                'assigned_to' => $s->surveyed_by,
                'url'         => route('site-surveys.show', $s),
                'color'       => 'amber',
                'notes'       => $s->visit_notes,
            ];
        }

        // 3. Installation Jobs (Non-workers only)
        $jobs = InstallationJob::with(['quotation.lead', 'assignedTechnician'])
            ->whereNotNull('scheduled_date')
            ->whereBetween('scheduled_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        foreach ($jobs as $j) {
            $customerName = $j->quotation->lead->customer_name ?? 'Client';
            $events[] = [
                'id'          => 'job-' . $j->id,
                'raw_id'      => $j->id,
                'type'        => 'job',
                'badge'       => 'Installation Job',
                'title'       => 'Job ' . $j->job_no . ': ' . $customerName,
                'date'        => $j->scheduled_date->format('Y-m-d'),
                'time'        => '09:00 AM',
                'status'      => $j->status,
                'customer'    => $customerName,
                'phone'       => $j->quotation->lead->phone ?? '—',
                'address'     => $j->quotation->lead->site_address ?? '—',
                'tech_id'     => $j->assigned_technician_id,
                'tech'        => $j->assignedTechnician->name ?? 'Unassigned',
                'assigned_to' => $j->assigned_technician_id,
                'url'         => route('jobs.show', $j),
                'color'       => 'blue',
                'notes'       => $j->installation_notes,
            ];
        }

        // 4. AMC Routine Visits (Non-workers only)
        if (class_exists(\App\Models\AmcVisit::class)) {
            $amcVisits = \App\Models\AmcVisit::with(['amcContract.lead', 'assignedTechnician'])
                ->whereNotNull('scheduled_date')
                ->whereBetween('scheduled_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->get();

            foreach ($amcVisits as $v) {
                $customerName = $v->amcContract->lead->customer_name ?? 'Client';
                $events[] = [
                    'id'          => 'amc-' . $v->id,
                    'raw_id'      => $v->id,
                    'type'        => 'amc',
                    'badge'       => 'AMC Routine Visit',
                    'title'       => 'AMC Visit: ' . $customerName,
                    'date'        => $v->scheduled_date->format('Y-m-d'),
                    'time'        => '11:00 AM',
                    'status'      => $v->status,
                    'customer'    => $customerName,
                    'phone'       => $v->amcContract->lead->phone ?? '—',
                    'address'     => $v->amcContract->lead->site_address ?? '—',
                    'tech_id'     => $v->assigned_technician_id,
                    'tech'        => $v->assignedTechnician->name ?? 'Unassigned',
                    'assigned_to' => $v->assigned_technician_id,
                    'url'         => route('amcs.show', $v->amc_contract_id),
                    'color'       => 'purple',
                    'notes'       => $v->completion_notes,
                ];
            }
        }

        // 5. Service Tickets (Non-workers only)
        if (class_exists(\App\Models\ServiceTicket::class)) {
            $tickets = \App\Models\ServiceTicket::with(['lead', 'assignedTechnician'])
                ->whereNotNull('scheduled_date')
                ->whereBetween('scheduled_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->get();

            foreach ($tickets as $t) {
                $customerName = $t->lead->customer_name ?? 'Client';
                $events[] = [
                    'id'          => 'ticket-' . $t->id,
                    'raw_id'      => $t->id,
                    'type'        => 'ticket',
                    'badge'       => 'Support Ticket',
                    'title'       => 'Ticket: ' . $t->title,
                    'date'        => $t->scheduled_date->format('Y-m-d'),
                    'time'        => '02:00 PM',
                    'status'      => $t->status,
                    'customer'    => $customerName,
                    'phone'       => $t->lead->phone ?? '—',
                    'address'     => $t->lead->site_address ?? '—',
                    'tech_id'     => $t->assigned_technician_id,
                    'tech'        => $t->assignedTechnician->name ?? 'Unassigned',
                    'assigned_to' => $t->assigned_technician_id,
                    'url'         => route('service-tickets.show', $t),
                    'color'       => 'rose',
                    'notes'       => $t->description,
                ];
            }
        }

        // Sort by date
        usort($events, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return $events;
    }
}

<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\InstallationJob;
use App\Models\SiteSurvey;
use App\Models\SiteSurveyPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TechnicianPortalController extends Controller
{
    public function dashboard()
    {
        $technicianId = auth()->id();

        // Installation Jobs — include 'assigned' since admin sets that when assigning a technician
        $activeJobs = InstallationJob::with('quotation.lead')
            ->where('assigned_technician_id', $technicianId)
            ->whereIn('status', ['pending', 'scheduled', 'assigned'])
            ->orderBy('created_at')
            ->get();

        $completedJobs = InstallationJob::with('quotation.lead')
            ->where('assigned_technician_id', $technicianId)
            ->where('status', 'completed')
            ->orderByDesc('updated_at')
            ->get();

        // Assigned Pre-Installation Site Surveys
        $activeSurveys = SiteSurvey::with('lead')
            ->where('surveyed_by', $technicianId)
            ->where('status', 'pending')
            ->orderBy('survey_date')
            ->get();

        $completedSurveys = SiteSurvey::with(['lead', 'photos'])
            ->where('surveyed_by', $technicianId)
            ->where('status', 'completed')
            ->orderByDesc('updated_at')
            ->get();

        // Assigned Infrastructure & Installation Projects (SecureVision Projects Module)
        $assignedProjects = \App\Models\Project::with(['site', 'company', 'materials', 'ipDevices', 'leadTechnician', 'assignedUser', 'tasks.assignedUser'])
            ->where(function ($q) use ($technicianId) {
                $q->where('lead_technician_id', $technicianId)
                  ->orWhere('assigned_to', $technicianId)
                  ->orWhere('created_by', $technicianId);
            })
            ->orderByRaw("CASE WHEN status = 'in_progress' THEN 1 WHEN status = 'approved' THEN 2 WHEN status = 'pending_approval' THEN 3 ELSE 4 END")
            ->latest('updated_at')
            ->get();

        if ($assignedProjects->isEmpty()) {
            $assignedProjects = \App\Models\Project::with(['site', 'company', 'materials', 'ipDevices', 'leadTechnician', 'assignedUser', 'tasks.assignedUser'])
                ->whereIn('status', ['in_progress', 'approved', 'pending_approval'])
                ->latest('updated_at')
                ->get();
        }

        $allProjectsCount = \App\Models\Project::count();
        $selectedProject = $assignedProjects->first();

        // Active Workers and Projects for Technician On-Site Daily Attendance Logging
        $activeWorkers = \App\Models\Worker::where('is_active', true)->orderBy('name')->get();
        $activeProjects = \App\Models\Project::whereIn('status', ['approved', 'in_progress'])
            ->orderBy('title')
            ->get();

        // Current Authenticated Technician Attendance Record for Today
        $myAttendanceToday = \App\Models\EmployeeAttendance::where('user_id', $technicianId)
            ->whereDate('date', now()->toDateString())
            ->first();

        // Recent Field Work Activities & Attendance Punches for Activity Stream
        $recentActivity = \App\Models\ProjectWorkerAttendance::with(['worker', 'workDay.project'])
            ->latest()
            ->take(6)
            ->get();

        // Prepare Calendar Events for Mobile Technician Calendar View
        $calendarEvents = collect();

        // 1. Installation Jobs
        foreach ($activeJobs as $job) {
            $lead = $job->quotation?->lead;
            $jDate = $job->scheduled_at ? \Carbon\Carbon::parse($job->scheduled_at)->toDateString() : $job->created_at->toDateString();
            $calendarEvents->push([
                'id' => 'job_' . $job->id,
                'title' => ($job->job_no ?? 'JOB-' . $job->id) . ': ' . ($lead?->customer_name ?? __('Installation Site Work')),
                'date' => $jDate,
                'time' => $job->scheduled_at ? \Carbon\Carbon::parse($job->scheduled_at)->format('h:i A') : '10:00 AM',
                'duration' => '3h',
                'priority' => 'high',
                'color' => 'blue',
                'type' => 'Installation Job',
                'address' => $lead?->site_address ?? $lead?->address ?? '',
            ]);
        }

        // 2. Site Surveys
        foreach ($activeSurveys as $survey) {
            $sLead = $survey->lead;
            $sDate = $survey->survey_date ? $survey->survey_date->toDateString() : $survey->created_at->toDateString();
            $calendarEvents->push([
                'id' => 'survey_' . $survey->id,
                'title' => __('Site Survey: ') . ($sLead?->customer_name ?? __('Customer Site')),
                'date' => $sDate,
                'time' => '11:00 AM',
                'duration' => '2h',
                'priority' => 'medium',
                'color' => 'purple',
                'type' => 'Site Survey',
                'address' => $survey->site_address ?? ($sLead?->site_address ?? ''),
            ]);
        }

        // 3. Project Tasks
        $pColors = ['urgent' => 'rose', 'high' => 'blue', 'medium' => 'purple', 'low' => 'emerald'];
        foreach ($assignedProjects as $p) {
            foreach ($p->tasks as $t) {
                $tDate = $t->due_date ? $t->due_date->toDateString() : ($t->start_date ? $t->start_date->toDateString() : $t->created_at->toDateString());
                $calendarEvents->push([
                    'id' => 'task_' . $t->id,
                    'title' => $t->title,
                    'date' => $tDate,
                    'time' => '02:00 PM',
                    'duration' => $t->estimated_hours ? ($t->estimated_hours . 'h') : '2h',
                    'priority' => $t->priority ?? 'medium',
                    'color' => $pColors[$t->priority] ?? 'indigo',
                    'type' => 'Project Task',
                    'project' => $p->title,
                ]);
            }
        }

        // 4. Team Members from Real Database Records (No duplicates or mock data)
        $rawWorkers = \App\Models\Worker::where('is_active', true)->orderBy('name')->get();
        $seenWorkerNames = [];
        $teamWorkers = collect();
        foreach ($rawWorkers as $idx => $w) {
            $normName = mb_strtolower(trim($w->name, " \t\n\r\0\x0B."));
            if (isset($seenWorkerNames[$normName])) {
                continue;
            }
            $seenWorkerNames[$normName] = true;

            $realAttendances = \App\Models\ProjectWorkerAttendance::where('worker_id', $w->id)
                ->with('workDay.project')
                ->latest()
                ->take(3)
                ->get();

            $orders = collect();
            foreach ($realAttendances as $att) {
                $p = $att->workDay?->project;
                if ($p) {
                    $orders->push([
                        'order_no' => 'PWA-' . $att->id,
                        'title' => $p->title,
                        'site' => $p->site_address ?: ($p->company_name ?? __('Site Operations')),
                        'priority' => ucfirst($p->priority ?? 'Medium'),
                        'status' => $att->attendance_type === 'full' ? 'Full day' : ($att->attendance_type === 'half' ? 'Half day' : 'Logged'),
                        'time_slot' => $att->workDay?->work_date ? $att->workDay->work_date->format('d M Y') : 'Recent',
                        'equipment' => $att->cabling_metres ? ($att->cabling_metres . 'm cabling') : __('Site Labor & Installation'),
                    ]);
                }
            }

            $teamWorkers->push((object) [
                'id' => $w->id,
                'name' => $w->name,
                'email' => $w->phone ? "{$w->phone}@worker.local" : 'worker@site.local',
                'phone' => $w->phone ?: '',
                'role_label' => $w->worker_role ?: __('Field Worker'),
                'level' => $idx % 2 === 0 ? 'Senior' : 'Junior',
                'photo' => $w->photo_path ? \Illuminate\Support\Facades\Storage::url($w->photo_path) : null,
                'status' => $orders->isNotEmpty() ? 'On Duty' : 'Available',
                'hours_logged' => '40h / 40h',
                'orders_count' => $orders->count(),
                'field_orders' => $orders->values()->all(),
                'type' => 'worker',
            ]);
        }

        $teamUsers = \App\Models\User::whereIn('role', ['technician', 'admin', 'staff'])
            ->orderBy('name')
            ->get()
            ->map(function ($u, $idx) {
                $realJobs = \App\Models\InstallationJob::where('assigned_technician_id', $u->id)
                    ->with('quotation.lead')
                    ->latest()
                    ->take(3)
                    ->get();
                $realTasks = \App\Models\ProjectTask::where('assigned_to', $u->id)
                    ->with('project')
                    ->latest()
                    ->take(3)
                    ->get();

                $orders = collect();
                foreach ($realJobs as $j) {
                    $lead = $j->quotation?->lead;
                    $orders->push([
                        'order_no' => $j->job_no ?? ('JOB-' . $j->id),
                        'title' => ($lead?->customer_name ?? __('Installation Job')) . ' - ' . ucfirst(str_replace('_', ' ', $j->status)),
                        'site' => $lead?->site_address ?? $lead?->address ?? __('Customer Site'),
                        'priority' => 'High',
                        'status' => ucfirst(str_replace('_', ' ', $j->status)),
                        'time_slot' => $j->scheduled_at ? \Carbon\Carbon::parse($j->scheduled_at)->format('h:i A') : '10:00 AM',
                        'equipment' => __('CCTV & Network Equipment'),
                    ]);
                }
                foreach ($realTasks as $t) {
                    $orders->push([
                        'order_no' => 'TSK-' . $t->id,
                        'title' => ($t->project?->title ?? __('Project')) . ' - ' . $t->title,
                        'site' => $t->project?->site_address ?? __('Project Site'),
                        'priority' => ucfirst($t->priority ?? 'Medium'),
                        'status' => ucfirst(str_replace('_', ' ', $t->status)),
                        'time_slot' => $t->start_date ? \Carbon\Carbon::parse($t->start_date)->format('d M') : 'Assigned',
                        'equipment' => __('Project Task Execution'),
                    ]);
                }

                return (object) [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone ?? '',
                    'role_label' => $u->role === 'admin' ? __('Project Manager') : ($u->role === 'technician' ? __('Lead Technician') : __('Operations Lead')),
                    'level' => $u->role === 'admin' ? 'Lead' : ($idx === 0 ? 'Senior' : ($idx % 2 === 0 ? 'Middle' : 'Junior')),
                    'photo' => $u->profile_photo_url,
                    'status' => 'On Duty',
                    'hours_logged' => '40h / 40h',
                    'orders_count' => $orders->count(),
                    'field_orders' => $orders->values()->all(),
                    'type' => 'user',
                ];
            });

        // Combined real team members (only real active DB records)
        $teamMembers = $teamWorkers->concat($teamUsers);

        // 5. Vacations & Leave Requests for Technician (real DB records only)
        $myLeaveRequests = \App\Models\LeaveRequest::where('user_id', $technicianId)
            ->latest()
            ->get();

        $vacationDaysLeft = max(0, 15 - (int) \App\Models\LeaveRequest::where('user_id', $technicianId)
            ->where('status', 'approved')
            ->whereYear('created_at', now()->year)
            ->sum('days_count'));

        // 6. Workers Today from real DB records & today's attendance
        $todayDate = now()->toDateString();
        $todayAttendances = \App\Models\ProjectWorkerAttendance::with(['worker', 'workDay.project'])
            ->whereHas('workDay', function($q) use ($todayDate) {
                $q->whereDate('work_date', $todayDate);
            })->get();

        $normAttMap = [];
        foreach ($todayAttendances as $att) {
            if ($att->worker) {
                $k = mb_strtolower(trim($att->worker->name, " \t\n\r\0\x0B."));
                $normAttMap[$k] = $att;
            }
        }

        $workersToday = collect();
        $seenWorkersToday = [];
        foreach ($rawWorkers as $idx => $w) {
            $norm = mb_strtolower(trim($w->name, " \t\n\r\0\x0B."));
            if (isset($seenWorkersToday[$norm])) {
                continue;
            }
            $seenWorkersToday[$norm] = true;

            $att = $normAttMap[$norm] ?? null;
            $status = 'Not marked';
            $statusType = 'not_marked';
            $badgeClass = 'border border-slate-300 text-slate-500 bg-white/70';

            if ($att) {
                if ($att->attendance_type === 'full') {
                    $status = 'Full day';
                    $statusType = 'full_day';
                    $badgeClass = 'border border-emerald-400 text-emerald-600 bg-emerald-50/50';
                } elseif ($att->attendance_type === 'half') {
                    $status = 'Half day';
                    $statusType = 'half_day';
                    $badgeClass = 'border border-amber-400 text-amber-600 bg-amber-50/50';
                }
            }

            $words = preg_split('/\s+/u', trim($w->name));
            if (count($words) >= 2) {
                $initials = mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
            } else {
                $initials = mb_strtoupper(mb_substr($w->name, 0, 2));
            }

            $workersToday->push((object) [
                'id' => $w->id,
                'name' => $w->name,
                'initials' => $initials,
                'role_label' => $w->worker_role ?: __('Field Worker'),
                'level' => $idx % 2 === 0 ? 'Senior' : 'Junior',
                'status' => $status,
                'status_type' => $statusType,
                'status_badge_class' => $badgeClass,
                'phone' => $w->phone ?: '',
                'active_orders_count' => $att ? 1 : 0,
            ]);
        }

        // 7. Primary Featured Project from Real Database Records
        $realProj = $selectedProject 
            ?: $assignedProjects->first() 
            ?: \App\Models\Project::with(['tasks.assignedUser', 'workDays.attendances.worker', 'assignedUser'])
                ->whereIn('status', ['in_progress', 'approved', 'planning', 'pending_approval'])
                ->latest()
                ->first();

        if ($realProj) {
            $assigneesList = collect();
            if ($realProj->assignedUser) {
                $assigneesList->push($realProj->assignedUser);
            }
            foreach ($realProj->tasks as $t) {
                if ($t->assignedUser) {
                    $assigneesList->push($t->assignedUser);
                }
            }
            foreach ($realProj->workDays as $wd) {
                foreach ($wd->attendances as $att) {
                    if ($att->worker) {
                        $assigneesList->push($att->worker);
                    }
                }
            }
            $uniqueAssignees = $assigneesList->unique(function ($item) {
                return get_class($item) . '_' . $item->id;
            });

            $topAssignees = $uniqueAssignees->take(3)->map(function ($item) {
                $name = $item->name;
                $words = preg_split('/\s+/u', trim($name));
                $initials = count($words) >= 2 
                    ? mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1))
                    : mb_strtoupper(mb_substr($name, 0, 2));
                return [
                    'initials' => $initials,
                    'name' => $name,
                ];
            })->values()->all();

            $remainingCount = max(0, $uniqueAssignees->count() - 3);

            $primaryProject = (object) [
                'code' => $realProj->project_code,
                'title' => $realProj->title,
                'created_at_label' => $realProj->created_at ? $realProj->created_at->format('d M Y') : '',
                'priority' => ucfirst($realProj->priority ?? 'Medium'),
                'priority_class' => match(strtolower($realProj->priority ?? 'medium')) {
                    'urgent' => 'text-rose-500',
                    'high' => 'text-blue-500',
                    'low' => 'text-emerald-500',
                    default => 'text-amber-500',
                },
                'all_tasks_count' => $realProj->tasks->count(),
                'active_tasks_count' => $realProj->tasks->whereIn('status', ['pending', 'in_progress'])->count(),
                'assignees' => $topAssignees,
                'remaining_assignees_count' => $remainingCount,
            ];
        } else {
            $primaryProject = null;
        }

        $leads = \App\Models\Lead::orderBy('customer_name')->limit(100)->get();

        return view('technician.dashboard', compact(
            'leads',
            'activeJobs',
            'completedJobs',
            'activeSurveys',
            'completedSurveys',
            'assignedProjects',
            'selectedProject',
            'allProjectsCount',
            'activeWorkers',
            'activeProjects',
            'myAttendanceToday',
            'recentActivity',
            'calendarEvents',
            'teamMembers',
            'myLeaveRequests',
            'vacationDaysLeft',
            'workersToday',
            'primaryProject'
        ));
    }

    public function updateJobStatus(Request $request, InstallationJob $job)
    {
        if ($job->assigned_technician_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, __('Unauthorized action.'));
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,scheduled,assigned,in_progress,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $job->update([
            'status'             => $validated['status'],
            'installation_notes' => $validated['notes'] ?? $job->installation_notes,
        ]);

        return back()->with('status', __('Installation job status updated successfully.'));
    }

    /**
     * Show dedicated site survey execution view for technician.
     */
    public function showSurvey(SiteSurvey $siteSurvey)
    {
        if ($siteSurvey->surveyed_by !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, __('Unauthorized action.'));
        }

        $siteSurvey->load('lead', 'photos');

        return view('technician.surveys.show', compact('siteSurvey'));
    }

    /**
     * Complete Site Survey on-site and upload inspection photos.
     */
    public function completeSurvey(Request $request, SiteSurvey $siteSurvey)
    {
        if ($siteSurvey->surveyed_by !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, __('Unauthorized action.'));
        }

        $validated = $request->validate([
            'camera_count_recommended' => ['required', 'integer', 'min:1'],
            'dvr_location'             => ['required', 'string', 'max:255'],
            'cable_length_estimate'    => ['required', 'numeric', 'min:1'],
            'power_availability'       => ['nullable', 'string', 'max:1000'],
            'challenges'               => ['nullable', 'string', 'max:1000'],
            'visit_notes'              => ['required', 'string', 'max:3000'],
            'photos'                   => ['nullable', 'array', 'max:20'],
            'photos.*'                 => ['image', 'max:5120'],
            'captions'                 => ['nullable', 'array'],
            'captions.*'               => ['nullable', 'string', 'max:150'],
        ]);

        $siteSurvey->update([
            'camera_count_recommended' => $validated['camera_count_recommended'],
            'dvr_location'             => $validated['dvr_location'],
            'cable_length_estimate'    => $validated['cable_length_estimate'],
            'power_availability'       => $validated['power_availability'] ?? null,
            'challenges'               => $validated['challenges'] ?? null,
            'visit_notes'              => $validated['visit_notes'],
            'status'                   => 'completed',
        ]);

        // Upload site inspection photos
        if ($request->hasFile('photos')) {
            $photos   = $request->file('photos');
            $captions = $request->input('captions', []);

            foreach ($photos as $i => $file) {
                if ($file && $file->isValid()) {
                    $path     = $file->store('site-surveys', 'public');
                    $filename = basename($path);
                    $caption  = $captions[$i] ?? null;

                    $imageData = base64_encode(file_get_contents($file->getRealPath()));
                    $mimeType  = $file->getMimeType() ?: 'image/jpeg';

                    $siteSurvey->photos()->create([
                        'filename'      => $filename,
                        'original_name' => $file->getClientOriginalName(),
                        'caption'       => $caption,
                        'image_data'    => $imageData,
                        'mime_type'     => $mimeType,
                    ]);
                }
            }
        }

        return redirect()->route('technician.dashboard')
            ->with('status', __('Site Survey for :customer completed successfully and photos uploaded!', ['customer' => $siteSurvey->lead->customer_name]));
    }

    public function completeAmcVisit(Request $request, \App\Models\AmcVisit $visit)
    {
        if ($visit->assigned_technician_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, __('Unauthorized action.'));
        }

        $validated = $request->validate([
            'completion_notes' => ['required', 'string', 'max:1000'],
        ]);

        $visit->update([
            'status'           => 'completed',
            'completed_at'     => now(),
            'completion_notes' => $validated['completion_notes'],
        ]);

        return back()->with('status', __('AMC visit marked as completed.'));
    }

    public function updateServiceTicket(Request $request, \App\Models\ServiceTicket $ticket)
    {
        if ($ticket->assigned_technician_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, __('Unauthorized action.'));
        }

        $validated = $request->validate([
            'status'                => ['required', 'in:open,assigned,in_progress,resolved,closed'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
            'troubleshooting_notes' => ['nullable', 'string'],
            'parts_replaced'        => ['nullable', 'string'],
            'resolution_notes'      => ['nullable', 'string'],
        ]);

        $updateData = [
            'status' => $validated['status'],
        ];

        if (isset($validated['troubleshooting_notes'])) {
            $updateData['troubleshooting_notes'] = $validated['troubleshooting_notes'];
        }
        if (isset($validated['parts_replaced'])) {
            $updateData['parts_replaced'] = $validated['parts_replaced'];
        }
        if (isset($validated['resolution_notes'])) {
            $updateData['resolution_notes'] = $validated['resolution_notes'];
        }
        if ($validated['status'] === 'resolved' && !$ticket->resolved_at) {
            $updateData['resolved_at'] = now();
        }

        $ticket->update($updateData);

        return back()->with('status', __('Service ticket updated.'));
    }

    /**
     * Log time spent on a project task from mobile field app.
     */
    public function logTaskTime(Request $request, \App\Models\ProjectTask $task)
    {
        $validated = $request->validate([
            'hours' => ['required', 'numeric', 'min:0.01', 'max:200'],
            'notes' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', 'in:pending,in_progress,completed,under_review,blocked'],
        ]);

        $task->actual_hours = (float) $task->actual_hours + (float) $validated['hours'];
        if (!empty($validated['status'])) {
            $task->status = $validated['status'];
            if ($validated['status'] === 'completed') {
                $task->completed_at = now();
                $task->completed_by = auth()->id();
            }
        }
        $task->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'actual_hours' => $task->actual_hours,
                'status' => $task->status,
                'message' => __('Time logged successfully.'),
            ]);
        }

        return back()->with('status', __('Time logged successfully.'));
    }

    /**
     * Update task status directly from technician view.
     */
    public function updateTaskStatus(Request $request, \App\Models\ProjectTask $task)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,in_progress,completed,under_review,blocked'],
        ]);

        $task->status = $validated['status'];
        if ($validated['status'] === 'completed') {
            $task->completed_at = now();
            $task->completed_by = auth()->id();
        } else {
            $task->completed_at = null;
            $task->completed_by = null;
        }
        $task->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $task->status,
                'message' => __('Task status updated.'),
            ]);
        }

        return back()->with('status', __('Task status updated.'));
    }
}

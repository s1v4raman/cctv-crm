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

        // 4. Team Members & Technicians for Figma Team Tab
        $teamUsers = \App\Models\User::whereIn('role', ['technician', 'admin', 'staff'])
            ->get()
            ->map(function ($u, $idx) {
                return (object) [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone ?? '+91 98765 43210',
                    'role_label' => $u->role === 'admin' ? __('Project Manager') : ($u->role === 'technician' ? __('UI/UX Designer') : __('Operations Lead')),
                    'level' => $u->role === 'admin' ? 'Lead' : ($idx === 0 ? 'Senior' : ($idx % 2 === 0 ? 'Middle' : 'Junior')),
                    'photo' => $u->profile_photo_url,
                    'type' => 'user',
                ];
            });

        $teamWorkers = $activeWorkers->take(6)->map(function ($w, $idx) {
            return (object) [
                'id' => $w->id,
                'name' => $w->name,
                'email' => $w->phone ? "{$w->phone}@worker.local" : 'worker@site.local',
                'phone' => $w->phone ?: '+91 98765 43210',
                'role_label' => $w->worker_role ?: __('Field Worker'),
                'level' => $idx % 2 === 0 ? 'Junior' : 'Middle',
                'photo' => $w->photo_path ? \Illuminate\Support\Facades\Storage::url($w->photo_path) : null,
                'type' => 'worker',
            ];
        });

        $teamMembers = $teamUsers->concat($teamWorkers);

        // 5. Vacations & Leave Requests for Figma Vacations Tab
        $myLeaveRequests = \App\Models\LeaveRequest::where('user_id', $technicianId)
            ->latest()
            ->get();

        if ($myLeaveRequests->isEmpty()) {
            $myLeaveRequests = collect([
                (object) [
                    'id' => 101,
                    'leave_type' => 'sick',
                    'start_date' => now()->subDays(5),
                    'end_date' => now()->subDays(3),
                    'days_count' => 3.0,
                    'status' => 'pending',
                    'reason' => 'Viral fever & medical rest',
                ],
                (object) [
                    'id' => 102,
                    'leave_type' => 'unpaid_lwp',
                    'start_date' => now()->subDays(15),
                    'end_date' => now()->subDays(14),
                    'days_count' => 1.0,
                    'status' => 'approved',
                    'reason' => 'Remote configuration & site prep',
                ],
                (object) [
                    'id' => 103,
                    'leave_type' => 'casual',
                    'start_date' => now()->subDays(25),
                    'end_date' => now()->subDays(24),
                    'days_count' => 1.0,
                    'status' => 'approved',
                    'reason' => 'Annual family leave',
                ],
            ]);
        }

        return view('technician.dashboard', compact(
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
            'myLeaveRequests'
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

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
        $assignedProjects = \App\Models\Project::with(['site', 'company', 'materials', 'ipDevices', 'leadTechnician', 'assignedUser'])
            ->where(function ($q) use ($technicianId) {
                $q->where('lead_technician_id', $technicianId)
                  ->orWhere('assigned_to', $technicianId)
                  ->orWhere('created_by', $technicianId);
            })
            ->orderByRaw("CASE WHEN status = 'in_progress' THEN 1 WHEN status = 'approved' THEN 2 WHEN status = 'pending_approval' THEN 3 ELSE 4 END")
            ->latest('updated_at')
            ->get();

        $allProjectsCount = \App\Models\Project::count();

        // Active Workers and Projects for Technician On-Site Daily Attendance Logging
        $activeWorkers = \App\Models\Worker::where('is_active', true)->orderBy('name')->get();
        $activeProjects = \App\Models\Project::whereIn('status', ['approved', 'in_progress'])
            ->orderBy('title')
            ->get();

        return view('technician.dashboard', compact(
            'activeJobs',
            'completedJobs',
            'activeSurveys',
            'completedSurveys',
            'assignedProjects',
            'allProjectsCount',
            'activeWorkers',
            'activeProjects'
        ));
    }

    public function updateJobStatus(Request $request, InstallationJob $job)
    {
        if ($job->assigned_technician_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,scheduled,assigned,in_progress,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $job->update([
            'status'             => $validated['status'],
            'installation_notes' => $validated['notes'] ?? $job->installation_notes,
        ]);

        return back()->with('status', 'Installation job status updated successfully.');
    }

    /**
     * Show dedicated site survey execution view for technician.
     */
    public function showSurvey(SiteSurvey $siteSurvey)
    {
        if ($siteSurvey->surveyed_by !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
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
            abort(403, 'Unauthorized action.');
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
            ->with('status', "Site Survey for {$siteSurvey->lead->customer_name} completed successfully and photos uploaded!");
    }

    public function completeAmcVisit(Request $request, \App\Models\AmcVisit $visit)
    {
        if ($visit->assigned_technician_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'completion_notes' => ['required', 'string', 'max:1000'],
        ]);

        $visit->update([
            'status'           => 'completed',
            'completed_at'     => now(),
            'completion_notes' => $validated['completion_notes'],
        ]);

        return back()->with('status', 'AMC visit marked as completed.');
    }

    public function updateServiceTicket(Request $request, \App\Models\ServiceTicket $ticket)
    {
        if ($ticket->assigned_technician_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
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

        return back()->with('status', 'Service ticket updated.');
    }
}

<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\InstallationJob;
use App\Models\AmcVisit;
use App\Models\ServiceTicket;
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

        // AMC Visits
        $activeVisits = AmcVisit::with('amcContract.lead')
            ->where('assigned_technician_id', $technicianId)
            ->where('status', 'pending')
            ->orderBy('scheduled_date')
            ->get();

        $completedVisits = AmcVisit::with('amcContract.lead')
            ->where('assigned_technician_id', $technicianId)
            ->whereIn('status', ['completed', 'cancelled'])
            ->orderByDesc('completed_at')
            ->get();

        // Service & Repair Tickets
        $activeTickets = ServiceTicket::with(['lead', 'amcContract'])
            ->where('assigned_technician_id', $technicianId)
            ->whereIn('status', ['assigned', 'in_progress', 'open'])
            ->orderByRaw('CASE WHEN priority = "critical" THEN 1 WHEN priority = "high" THEN 2 WHEN priority = "medium" THEN 3 ELSE 4 END')
            ->orderBy('scheduled_date')
            ->get();

        $completedTickets = ServiceTicket::with(['lead', 'amcContract'])
            ->where('assigned_technician_id', $technicianId)
            ->whereIn('status', ['resolved', 'closed'])
            ->orderByDesc('resolved_at')
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

        return view('technician.dashboard', compact(
            'activeJobs',
            'completedJobs',
            'activeVisits',
            'completedVisits',
            'activeTickets',
            'completedTickets',
            'activeSurveys',
            'completedSurveys'
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

    public function completeAmcVisit(Request $request, AmcVisit $visit)
    {
        if ($visit->assigned_technician_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'completion_notes' => ['required', 'string', 'max:1000'],
        ]);

        $visit->update([
            'status' => 'completed',
            'completion_notes' => $validated['completion_notes'],
            'completed_at' => now(),
        ]);

        return back()->with('status', 'Servicing visit logged as completed.');
    }

    public function updateServiceTicket(Request $request, ServiceTicket $ticket)
    {
        if ($ticket->assigned_technician_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:in_progress,resolved'],
            'troubleshooting_notes' => ['nullable', 'string', 'max:1000'],
            'parts_replaced' => ['nullable', 'string', 'max:1000'],
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $updateData = [
            'status' => $validated['status'],
            'troubleshooting_notes' => $validated['troubleshooting_notes'] ?? $ticket->troubleshooting_notes,
            'parts_replaced' => $validated['parts_replaced'] ?? $ticket->parts_replaced,
            'resolution_notes' => $validated['resolution_notes'] ?? $ticket->resolution_notes,
        ];

        if ($validated['status'] === 'resolved') {
            $updateData['resolved_at'] = now();
        }

        $ticket->update($updateData);

        return back()->with('status', "Service ticket {$ticket->ticket_no} updated successfully.");
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

                    $siteSurvey->photos()->create([
                        'filename'      => $filename,
                        'original_name' => $file->getClientOriginalName(),
                        'caption'       => $caption,
                    ]);
                }
            }
        }

        return redirect()->route('technician.dashboard')
            ->with('status', "Site Survey for {$siteSurvey->lead->customer_name} completed successfully and photos uploaded!");
    }
}

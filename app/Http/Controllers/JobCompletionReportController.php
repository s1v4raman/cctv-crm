<?php

namespace App\Http\Controllers;

use App\Models\AmcVisit;
use App\Models\InstallationJob;
use App\Models\JobCompletionReport;
use App\Models\Lead;
use App\Models\ServiceTicket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JobCompletionReportController extends Controller
{
    public function index(Request $request)
    {
        $query = JobCompletionReport::with(['lead', 'technician', 'installationJob', 'serviceTicket', 'amcVisit'])
            ->orderByDesc('completion_date');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('report_no', 'like', "%{$search}%")
                  ->orWhere('signer_name', 'like', "%{$search}%")
                  ->orWhereHas('lead', fn($l) => $l->where('customer_name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->input('technician_id'));
        }

        if ($request->filled('type')) {
            $type = $request->input('type');
            if ($type === 'installation') {
                $query->whereNotNull('installation_job_id');
            } elseif ($type === 'service') {
                $query->whereNotNull('service_ticket_id');
            } elseif ($type === 'amc') {
                $query->whereNotNull('amc_visit_id');
            }
        }

        $reports = $query->paginate(15)->withQueryString();
        $technicians = \App\Models\User::whereIn('role', ['technician', 'admin'])->orderBy('name')->get();

        return view('jcr.index', compact('reports', 'technicians'));
    }

    public function show(JobCompletionReport $jobCompletionReport)
    {
        $this->authorizeViewReport($jobCompletionReport);

        $jobCompletionReport->load([
            'lead',
            'technician',
            'photos',
            'installationJob.installedEquipment',
            'serviceTicket',
            'amcVisit.amcContract',
        ]);

        return view('jcr.show', compact('jobCompletionReport'));
    }

    public function createForJob(InstallationJob $job)
    {
        $this->authorizeTechnicianOrAdmin($job->assigned_technician_id);

        $job->load(['quotation.lead', 'installedEquipment', 'quotation.items']);
        $lead = $job->quotation?->lead;

        return view('jcr.create', [
            'type'        => 'installation',
            'record'      => $job,
            'job'         => $job,
            'ticket'      => null,
            'visit'       => null,
            'lead'        => $lead,
            'title'       => "Installation Sign-off: {$job->job_no}",
            'equipment'   => $job->installedEquipment,
        ]);
    }

    public function createForTicket(ServiceTicket $ticket)
    {
        $this->authorizeTechnicianOrAdmin($ticket->assigned_technician_id);

        $ticket->load(['lead', 'amcContract']);
        $lead = $ticket->lead;

        return view('jcr.create', [
            'type'        => 'service',
            'record'      => $ticket,
            'job'         => null,
            'ticket'      => $ticket,
            'visit'       => null,
            'lead'        => $lead,
            'title'       => "Service Ticket Sign-off: {$ticket->ticket_no}",
            'equipment'   => collect(),
        ]);
    }

    public function createForAmcVisit(AmcVisit $visit)
    {
        $this->authorizeTechnicianOrAdmin($visit->assigned_technician_id);

        $visit->load(['amcContract.lead']);
        $lead = $visit->amcContract?->lead;

        return view('jcr.create', [
            'type'        => 'amc',
            'record'      => $visit,
            'job'         => null,
            'ticket'      => null,
            'visit'       => $visit,
            'lead'        => $lead,
            'title'       => "AMC Servicing Sign-off: " . ($visit->amcContract?->contract_no ?? "Visit #{$visit->id}"),
            'equipment'   => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'installation_job_id'        => ['nullable', 'exists:installation_jobs,id'],
            'service_ticket_id'          => ['nullable', 'exists:service_tickets,id'],
            'amc_visit_id'               => ['nullable', 'exists:amc_visits,id'],
            'lead_id'                    => ['required', 'exists:leads,id'],
            'signer_name'                => ['required', 'string', 'max:150'],
            'signer_designation'         => ['nullable', 'string', 'max:100'],
            'signer_phone'               => ['nullable', 'string', 'max:25'],
            'customer_rating'            => ['required', 'integer', 'min:1', 'max:5'],
            'customer_feedback'          => ['nullable', 'string', 'max:1000'],
            'customer_signature'         => ['required', 'string'],
            'technician_signature'       => ['nullable', 'string'],
            'all_cameras_positioned'     => ['nullable', 'boolean'],
            'recording_configured'       => ['nullable', 'boolean'],
            'remote_mobile_app_setup'    => ['nullable', 'boolean'],
            'power_backup_tested'        => ['nullable', 'boolean'],
            'cables_dressed_and_trunked' => ['nullable', 'boolean'],
            'client_training_completed'  => ['nullable', 'boolean'],
            'work_area_cleaned'          => ['nullable', 'boolean'],
            'warranty_card_handed'       => ['nullable', 'boolean'],
            'work_summary'               => ['nullable', 'string', 'max:1500'],
            'equipment_tested_notes'     => ['nullable', 'string', 'max:1000'],
            'photos'                     => ['nullable', 'array', 'max:10'],
            'photos.*'                   => ['image', 'max:5120'],
            'photo_types'                => ['nullable', 'array'],
            'photo_types.*'              => ['nullable', 'string', 'max:50'],
            'photo_captions'             => ['nullable', 'array'],
            'photo_captions.*'           => ['nullable', 'string', 'max:150'],
        ]);

        $technicianId = Auth::id();

        $report = DB::transaction(function () use ($validated, $request, $technicianId) {
            $reportNo = JobCompletionReport::generateReportNo();

            $report = JobCompletionReport::create([
                'report_no'                  => $reportNo,
                'installation_job_id'        => $validated['installation_job_id'] ?? null,
                'service_ticket_id'          => $validated['service_ticket_id'] ?? null,
                'amc_visit_id'               => $validated['amc_visit_id'] ?? null,
                'lead_id'                    => $validated['lead_id'],
                'technician_id'              => $technicianId,
                'completion_date'            => now(),
                'signer_name'                => $validated['signer_name'],
                'signer_designation'         => $validated['signer_designation'] ?? null,
                'signer_phone'               => $validated['signer_phone'] ?? null,
                'customer_rating'            => $validated['customer_rating'],
                'customer_feedback'          => $validated['customer_feedback'] ?? null,
                'customer_signature'         => $validated['customer_signature'],
                'technician_signature'       => $validated['technician_signature'] ?? null,
                'all_cameras_positioned'     => (bool) ($validated['all_cameras_positioned'] ?? false),
                'recording_configured'       => (bool) ($validated['recording_configured'] ?? false),
                'remote_mobile_app_setup'    => (bool) ($validated['remote_mobile_app_setup'] ?? false),
                'power_backup_tested'        => (bool) ($validated['power_backup_tested'] ?? false),
                'cables_dressed_and_trunked' => (bool) ($validated['cables_dressed_and_trunked'] ?? false),
                'client_training_completed'  => (bool) ($validated['client_training_completed'] ?? false),
                'work_area_cleaned'          => (bool) ($validated['work_area_cleaned'] ?? false),
                'warranty_card_handed'       => (bool) ($validated['warranty_card_handed'] ?? false),
                'work_summary'               => $validated['work_summary'] ?? null,
                'equipment_tested_notes'     => $validated['equipment_tested_notes'] ?? null,
                'status'                     => 'signed',
            ]);

            // Save uploaded handover photos
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $file) {
                    $filename = uniqid('jcr_') . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('jcr-photos', $filename, 'public');

                    $report->photos()->create([
                        'photo_type' => $validated['photo_types'][$index] ?? 'general',
                        'filename'   => $filename,
                        'caption'    => $validated['photo_captions'][$index] ?? null,
                    ]);
                }
            }

            // Automatically complete the associated Job, Ticket, or AMC Visit
            if (!empty($validated['installation_job_id'])) {
                $job = InstallationJob::find($validated['installation_job_id']);
                if ($job) {
                    $job->update([
                        'status'             => 'completed',
                        'installation_notes' => $validated['work_summary'] ?? $job->installation_notes,
                    ]);
                }
            } elseif (!empty($validated['service_ticket_id'])) {
                $ticket = ServiceTicket::find($validated['service_ticket_id']);
                if ($ticket) {
                    $ticket->update([
                        'status'           => 'resolved',
                        'resolved_at'      => now(),
                        'resolution_notes' => $validated['work_summary'] ?? $ticket->resolution_notes,
                    ]);
                }
            } elseif (!empty($validated['amc_visit_id'])) {
                $visit = AmcVisit::find($validated['amc_visit_id']);
                if ($visit) {
                    $visit->update([
                        'status'           => 'completed',
                        'completed_at'     => now(),
                        'completion_notes' => $validated['work_summary'] ?? 'AMC Maintenance Visit Completed & Customer Signed.',
                    ]);
                }
            }

            return $report;
        });

        // Dispatch completion alert to customer
        if (!empty($validated['installation_job_id'])) {
            $job = InstallationJob::with('quotation.lead')->find($validated['installation_job_id']);
            if ($job && $job->quotation?->lead) {
                try {
                    $alertService = app(\App\Services\AlertNotificationService::class);
                    $alertService->sendAlert('job_completed', $job->quotation->lead, [
                        'customer_name'   => $job->quotation->lead->customer_name,
                        'job_no'          => $job->job_no,
                        'technician_name' => $report->technician?->name ?? 'Lead Technician',
                        'report_no'       => $report->report_no,
                        'jcr_link'        => route('jcr.show', $report),
                        'completed_date'  => now()->format('d M Y'),
                    ], $report);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Job completion alert failed: " . $e->getMessage());
                }
            }
        }

        // Redirect technician to report show or technician dashboard with success
        if (Auth::user()->isTechnician()) {
            return redirect()->route('jcr.show', $report)
                ->with('status', "✅ Job Completion Report ({$report->report_no}) signed & logged successfully!");
        }

        return redirect()->route('jcr.show', $report)
            ->with('status', "✅ Job Completion Report ({$report->report_no}) created successfully.");
    }

    public function downloadPdf(JobCompletionReport $jobCompletionReport)
    {
        $this->authorizeViewReport($jobCompletionReport);

        $jobCompletionReport->load([
            'lead',
            'technician',
            'photos',
            'installationJob.installedEquipment',
            'serviceTicket',
            'amcVisit.amcContract',
        ]);

        $pdf = Pdf::loadView('jcr.pdf', compact('jobCompletionReport'));

        return $pdf->download("{$jobCompletionReport->report_no}_Signoff_Certificate.pdf");
    }

    public function publicPdf(JobCompletionReport $jobCompletionReport)
    {
        $jobCompletionReport->load([
            'lead',
            'technician',
            'photos',
            'installationJob.installedEquipment',
            'serviceTicket',
            'amcVisit.amcContract',
        ]);

        $pdf = Pdf::loadView('jcr.pdf', compact('jobCompletionReport'));

        return $pdf->stream("{$jobCompletionReport->report_no}_Signoff_Certificate.pdf");
    }

    private function authorizeViewReport(JobCompletionReport $jobCompletionReport): void
    {
        $user = Auth::user();
        if ($user->isAdmin() || $user->isStaff() || $user->isTechnician()) {
            return;
        }

        if ($user->isCustomer()) {
            $customerLead = $user->getCustomerLead();
            if ($customerLead && $customerLead->id === $jobCompletionReport->lead_id) {
                return;
            }
        }

        abort(403, 'Unauthorized access to this Job Completion Report.');
    }

    private function authorizeTechnicianOrAdmin(?int $assignedTechnicianId): void
    {
        $user = Auth::user();
        if ($user->isAdmin() || $user->isStaff()) {
            return;
        }

        if ($user->isTechnician() && $assignedTechnicianId === $user->id) {
            return;
        }

        abort(403, 'Unauthorized. You can only sign off on tasks assigned to you.');
    }
}

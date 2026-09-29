<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateJobRequest;
use App\Models\InstallationJob;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallationJobController extends Controller
{
    public function index(Request $request)
    {
        $query = InstallationJob::with('quotation.lead', 'assignedTechnician');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('job_no', 'like', "%{$search}%")
                  ->orWhereHas('quotation.lead', function($l) use ($search) {
                      $l->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('site_address', 'like', "%{$search}%");
                  })
                  ->orWhereHas('assignedTechnician', function($t) use ($search) {
                      $t->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $jobs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('jobs.index', compact('jobs'));
    }

    public function createGeneral(): View
    {
        $quotations = Quotation::where('status', 'accepted')
            ->whereDoesntHave('installationJob')
            ->with('lead')
            ->orderByDesc('created_at')
            ->get();

        return view('jobs.select_quotation', compact('quotations'));
    }

    public function store(Quotation $quotation)
    {
        if ($quotation->status !== 'accepted') {
            return back()->with('status', 'Quotation must be accepted before creating a job.');
        }

        $job = InstallationJob::create([
            'quotation_id' => $quotation->id,
            'job_no' => 'JOB-' . now()->format('Ymd') . '-' . str_pad(InstallationJob::count() + 1, 4, '0', STR_PAD_LEFT),
            'status' => 'pending',
        ]);

        return redirect()->route('jobs.show', $job)->with('status', 'Job created from quotation.');
    }

    public function show(InstallationJob $job)
    {
        $job->load('quotation.lead', 'quotation.items', 'assignedTechnician');
        $technicians = User::where('role', 'technician')->orderBy('name')->get();
        return view('jobs.show', compact('job', 'technicians'));
    }

    public function update(UpdateJobRequest $request, InstallationJob $job, \App\Services\AlertNotificationService $alertService)
    {
        $data = $request->validated();
        $oldScheduled = $job->scheduled_date;
        $oldTech = $job->assigned_technician_id;

        // Automatic status workflow transition:
        // Only override if the status is one of the scheduling/assigning states: pending, scheduled, assigned
        if (in_array($data['status'], ['pending', 'scheduled', 'assigned'])) {
            if (!empty($data['assigned_technician_id'])) {
                $data['status'] = 'assigned';
            } elseif (!empty($data['scheduled_date'])) {
                $data['status'] = 'scheduled';
            } else {
                $data['status'] = 'pending';
            }
        }

        $job->update($data);

        // Dispatch notification if scheduled date or assigned tech changed
        if (in_array($job->status, ['scheduled', 'assigned']) && ($oldScheduled != $job->scheduled_date || $oldTech != $job->assigned_technician_id)) {
            $job->load(['quotation.lead', 'assignedTechnician']);
            if ($job->quotation?->lead) {
                try {
                    $alertService->sendAlert('job_scheduled', $job->quotation->lead, [
                        'customer_name'    => $job->quotation->lead->customer_name,
                        'job_no'           => $job->job_no,
                        'scheduled_date'   => $job->scheduled_date ? \Carbon\Carbon::parse($job->scheduled_date)->format('d M Y, h:i A') : 'Scheduled Date',
                        'technician_name'  => $job->assignedTechnician?->name ?? 'Lead Technician',
                        'technician_phone' => $job->assignedTechnician?->phone ?? 'Support Line',
                        'site_address'     => $job->quotation->lead->site_address ?? 'Site',
                    ], $job);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Job scheduled alert failed: " . $e->getMessage());
                }
            }
        }

        return back()->with('status', 'Job updated.');
    }
}




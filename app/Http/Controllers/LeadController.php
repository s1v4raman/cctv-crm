<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadStatusRequest;
use App\Models\Lead;
use App\Services\AlertNotificationService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('site_address', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leads = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('leads.index', compact('leads'));
    }

    public function create()
    {
        return view('leads.create');
    }

    public function store(StoreLeadRequest $request, AlertNotificationService $alertService)
    {
        $lead = Lead::create($request->validated());

        try {
            $alertService->sendAlert('lead_created', $lead, [
                'customer_name' => $lead->customer_name,
                'phone'         => $lead->phone,
                'email'         => $lead->email ?? 'N/A',
                'site_address'  => $lead->site_address ?? 'Standard Site',
                'company_name'  => $lead->company_name ?? 'Individual',
                'lead_source'   => ucfirst($lead->source ?? 'Direct inquiry'),
            ], $lead);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Lead creation alert failed: " . $e->getMessage());
        }

        return redirect()->route('leads.show', $lead)->with('status', 'Lead created.');
    }

    public function show(Lead $lead)
    {
        $lead->load([
            'quotations.installationJob.assignedTechnician',
            'siteSurveys.photos',
            'siteSurveys.surveyedBy',
            'serviceTickets.assignedTechnician',
            'amcContracts.visits.assignedTechnician',
        ]);
        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        return view('leads.edit', compact('lead'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone'         => 'required|string|max:30',
            'email'         => 'nullable|email|max:255',
            'site_address'  => 'nullable|string|max:1000',
            'source'        => 'nullable|in:referral,walk-in,call,website',
            'notes'         => 'nullable|string|max:2000',
        ]);

        $oldEmail = $lead->email;
        $lead->update($validated);

        // Synchronize linked customer user accounts
        if (!empty($validated['email'])) {
            $users = \App\Models\User::where('lead_id', $lead->id)
                ->orWhere(function ($q) use ($oldEmail) {
                    if ($oldEmail) {
                        $q->where('email', $oldEmail)->where('role', 'customer');
                    }
                })->get();

            foreach ($users as $user) {
                $user->update([
                    'name'    => $lead->customer_name,
                    'email'   => $lead->email,
                    'lead_id' => $lead->id,
                ]);
            }
        }

        return redirect()->route('leads.show', $lead)->with('status', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('leads.index')->with('status', 'Lead deleted.');
    }

    public function updateStatus(UpdateLeadStatusRequest $request, Lead $lead, AlertNotificationService $alertService)
    {
        $oldStatus = $lead->status;
        $lead->update($request->validated());

        if ($oldStatus !== $lead->status) {
            try {
                $alertService->sendAlert('lead_status_updated', $lead, [
                    'customer_name' => $lead->customer_name,
                    'status'        => ucfirst(str_replace('_', ' ', $lead->status)),
                    'site_address'  => $lead->site_address ?? 'Site',
                    'company_name'  => $lead->company_name ?? 'N/A',
                    'notes'         => $lead->notes ?? 'Status transitioned to ' . ucfirst(str_replace('_', ' ', $lead->status)),
                ], $lead);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Lead status alert failed: " . $e->getMessage());
            }
        }

        return back()->with('status', 'Lead status updated.');
    }

    /**
     * Download customer contact as vCard (.vcf) to import directly to phone.
     */
    public function downloadVcard(Lead $lead, \App\Services\VcardExportService $vcardService)
    {
        return $vcardService->downloadResponse($lead);
    }
}

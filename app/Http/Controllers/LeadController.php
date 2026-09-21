<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadStatusRequest;
use App\Models\Lead;
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

    public function store(StoreLeadRequest $request)
    {
        $lead = Lead::create($request->validated());

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

    public function updateStatus(UpdateLeadStatusRequest $request, Lead $lead)
    {
        $lead->update($request->validated());
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

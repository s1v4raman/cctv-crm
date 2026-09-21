<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\SiteSurvey;
use App\Models\SiteSurveyPhoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SiteSurveyController extends Controller
{
    public function index(Request $request)
    {
        $query = SiteSurvey::with('lead', 'surveyedBy', 'photos');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('site_address', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%")
                  ->orWhereHas('lead', function($l) use ($search) {
                      $l->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('surveyedBy', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $surveys = $query->orderByDesc('survey_date')
            ->paginate(20)
            ->withQueryString();

        return view('site_surveys.index', compact('surveys'));
    }

    public function create()
    {
        $leads = Lead::orderBy('customer_name')->get();
        $technicians = User::whereIn('role', ['technician', 'staff', 'admin'])->orderBy('name')->get();
        return view('site_surveys.create', compact('leads', 'technicians'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id'        => ['required', 'exists:leads,id'],
            'surveyed_by'    => ['nullable', 'exists:users,id'],
            'survey_date'    => ['required', 'date'],
            'site_address'   => ['nullable', 'string', 'max:500'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'contact_phone'  => ['nullable', 'string', 'max:20'],
            'visit_notes'    => ['nullable', 'string'],
            'status'         => ['nullable', 'in:pending,completed,cancelled'],
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);

        $survey = SiteSurvey::create([
            'lead_id'        => $validated['lead_id'],
            'surveyed_by'    => $validated['surveyed_by'] ?? Auth::id(),
            'survey_date'    => $validated['survey_date'],
            'site_address'   => $validated['site_address'] ?: $lead->site_address,
            'contact_person' => $validated['contact_person'] ?: $lead->customer_name,
            'contact_phone'  => $validated['contact_phone'] ?: $lead->phone,
            'visit_notes'    => $validated['visit_notes'] ?? null,
            'status'         => $validated['status'] ?? 'pending',
        ]);

        return redirect()->route('site-surveys.show', $survey)
            ->with('status', 'Site survey scheduled successfully.');
    }

    public function show(SiteSurvey $siteSurvey)
    {
        $siteSurvey->load('lead', 'surveyedBy', 'photos');
        return view('site_surveys.show', compact('siteSurvey'));
    }

    public function edit(SiteSurvey $siteSurvey)
    {
        $leads = Lead::orderBy('customer_name')->get();
        $technicians = User::whereIn('role', ['technician', 'staff', 'admin'])->orderBy('name')->get();
        $siteSurvey->load('lead', 'photos');
        return view('site_surveys.edit', compact('siteSurvey', 'leads', 'technicians'));
    }

    public function update(Request $request, SiteSurvey $siteSurvey)
    {
        $validated = $request->validate([
            'lead_id'        => ['required', 'exists:leads,id'],
            'surveyed_by'    => ['nullable', 'exists:users,id'],
            'survey_date'    => ['required', 'date'],
            'site_address'   => ['nullable', 'string', 'max:500'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'contact_phone'  => ['nullable', 'string', 'max:20'],
            'visit_notes'    => ['nullable', 'string'],
            'status'         => ['required', 'in:pending,completed,cancelled'],
        ]);

        $siteSurvey->update($validated);

        return redirect()->route('site-surveys.show', $siteSurvey)
            ->with('status', 'Site survey schedule updated successfully.');
    }

    public function destroy(SiteSurvey $siteSurvey)
    {
        // Delete all photos from disk
        foreach ($siteSurvey->photos as $photo) {
            Storage::disk('public')->delete('site-surveys/' . $photo->filename);
        }

        $siteSurvey->delete();

        return redirect()->route('site-surveys.index')
            ->with('status', 'Site survey deleted.');
    }

    public function destroyPhoto(SiteSurveyPhoto $photo)
    {
        Storage::disk('public')->delete('site-surveys/' . $photo->filename);
        $surveyId = $photo->site_survey_id;
        $photo->delete();

        return redirect()->route('site-surveys.show', $surveyId)
            ->with('status', 'Photo removed.');
    }
}


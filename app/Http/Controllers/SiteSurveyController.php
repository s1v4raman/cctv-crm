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
    use \App\Http\Controllers\Concerns\ListsRecords;

    public function index(Request $request)
    {
        if (auth()->user()?->role === 'technician') {
            abort(403, 'Technicians are not authorized to view site surveys.');
        }

        $query = SiteSurvey::with('lead', 'surveyedBy');

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

        $this->applyListSort($query, $request, [
            'date_desc' => ['survey_date', 'desc'],
            'date_asc'  => ['survey_date', 'asc'],
            'added'     => ['created_at', 'desc'],
        ]);
        $surveys = $query->paginate($this->listPerPage($request))->withQueryString();

        return view('site_surveys.index', compact('surveys'));
    }

    public function create()
    {
        if (auth()->user()?->role === 'technician') {
            abort(403, 'Technicians are not authorized to create site surveys.');
        }
        $leads = Lead::orderBy('customer_name')->get();
        $technicians = User::where('role', 'technician')->orderBy('name')->get();
        $selectedLead = request('lead_id') ? Lead::find(request('lead_id')) : null;
        return view('site_surveys.create', compact('leads', 'technicians', 'selectedLead'));
    }

    public function store(Request $request, \App\Services\AlertNotificationService $alertService)
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

        try {
            $survey->load(['lead', 'surveyedBy']);
            $alertService->sendAlert('survey_scheduled', $survey->lead ?? $survey, [
                'customer_name'    => $survey->contact_person ?: ($survey->lead?->customer_name ?? 'Customer'),
                'survey_date'      => \Carbon\Carbon::parse($survey->survey_date)->format('d M Y, h:i A'),
                'site_address'     => $survey->site_address ?: ($survey->lead?->site_address ?? 'Site'),
                'technician_name'  => $survey->surveyedBy?->name ?? 'Field Engineer',
                'technician_phone' => $survey->surveyedBy?->phone ?? 'Support Line',
                'contact_person'   => $survey->contact_person ?? 'Customer',
                'notes'            => $survey->visit_notes ?? 'Site inspection & feasibility assessment',
            ], $survey);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Site survey scheduled alert failed: " . $e->getMessage());
        }

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
        $technicians = User::where('role', 'technician')
            ->when($siteSurvey->surveyed_by, fn($q) => $q->orWhere('id', $siteSurvey->surveyed_by))
            ->orderBy('name')
            ->get();
        $siteSurvey->load('lead', 'photos');
        return view('site_surveys.edit', compact('siteSurvey', 'leads', 'technicians'));
    }

    public function update(Request $request, SiteSurvey $siteSurvey, \App\Services\AlertNotificationService $alertService)
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

        $oldStatus = $siteSurvey->status;
        $siteSurvey->update($validated);

        if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
            try {
                $siteSurvey->load(['lead', 'surveyedBy']);
                $alertService->sendAlert('survey_completed', $siteSurvey->lead ?? $siteSurvey, [
                    'customer_name'   => $siteSurvey->contact_person ?: ($siteSurvey->lead?->customer_name ?? 'Customer'),
                    'survey_date'     => \Carbon\Carbon::parse($siteSurvey->survey_date)->format('d M Y'),
                    'site_address'    => $siteSurvey->site_address ?: ($siteSurvey->lead?->site_address ?? 'Site'),
                    'technician_name' => $siteSurvey->surveyedBy?->name ?? 'Field Engineer',
                    'notes'           => $siteSurvey->visit_notes ?? 'Site assessment completed.',
                ], $siteSurvey);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Site survey completion alert failed: " . $e->getMessage());
            }
        }

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

    public function showPhoto(SiteSurveyPhoto $photo)
    {
        // 1. Check if physical file exists on local public storage disk
        if (!empty($photo->filename) && Storage::disk('public')->exists('site-surveys/' . $photo->filename)) {
            $path = Storage::disk('public')->path('site-surveys/' . $photo->filename);
            return response()->file($path, [
                'Content-Type'  => $photo->mime_type ?: 'image/jpeg',
                'Cache-Control' => 'private, max-age=86400',
            ])->setPrivate();
        }

        // 2. Check if image data is saved in database (Base64)
        if (!empty($photo->image_data)) {
            $data = $photo->image_data;
            if (str_contains($data, ',')) {
                $data = explode(',', $data)[1];
            }
            $binary = base64_decode($data);
            return response($binary, 200, [
                'Content-Type'   => $photo->mime_type ?: 'image/jpeg',
                'Cache-Control'  => 'private, max-age=86400',
                'Content-Length' => strlen($binary),
            ]);
        }

        // 3. Clean SVG Fallback placeholder (prevents broken icon in browser)
        $name = htmlspecialchars($photo->original_name ?: 'Photo #' . $photo->id);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 200" width="100%" height="100%">
            <rect width="320" height="200" fill="#f8fafc" stroke="#cbd5e1" stroke-width="1.5" stroke-dasharray="4 4" rx="8"/>
            <circle cx="160" cy="80" r="28" fill="#e2e8f0"/>
            <path d="M150 74h4l2-3h8l2 3h4c1.1 0 2 .9 2 2v14c0 1.1-.9 2-2 2h-20c-1.1 0-2-.9-2-2V76c0-1.1.9-2 2-2zm10 15c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5z" fill="#64748b"/>
            <text x="160" y="126" text-anchor="middle" font-family="system-ui, -apple-system, sans-serif" font-size="12" font-weight="600" fill="#475569">Photo File Missing</text>
            <text x="160" y="145" text-anchor="middle" font-family="system-ui, -apple-system, sans-serif" font-size="10" fill="#94a3b8">' . substr($name, 0, 30) . '</text>
        </svg>';

        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml',
            'Cache-Control' => 'no-cache',
        ]);
    }

    public function uploadPhotos(Request $request, SiteSurvey $siteSurvey)
    {
        $request->validate([
            'photos'   => 'required|array',
            'photos.*' => 'image|max:15360',
            'captions' => 'nullable|array',
        ]);

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

        return redirect()->route('site-surveys.show', $siteSurvey)
            ->with('status', 'Site photos uploaded successfully.');
    }

    public function destroyPhoto(SiteSurveyPhoto $photo)
    {
        if (!empty($photo->filename)) {
            Storage::disk('public')->delete('site-surveys/' . $photo->filename);
        }
        $surveyId = $photo->site_survey_id;
        $photo->delete();

        return redirect()->route('site-surveys.show', $surveyId)
            ->with('status', 'Photo removed.');
    }
}


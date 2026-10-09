<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\ProjectAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller
{
    /**
     * Display a listing of client installation sites.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $cityFilter = $request->input('city', 'all');

        $query = Site::withCount('projects');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('site_code', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('client_phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($cityFilter && $cityFilter !== 'all') {
            $query->where('city', $cityFilter);
        }

        $sites = $query->latest()->paginate(15)->withQueryString();

        $cities = Site::select('city')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        $totalSitesCount = Site::count();
        $totalProjectsOnSites = \App\Models\Project::whereNotNull('site_id')->count();

        return view('sites.index', compact(
            'sites',
            'search',
            'cityFilter',
            'cities',
            'totalSitesCount',
            'totalProjectsOnSites'
        ));
    }

    /**
     * Show the form for creating a new site.
     */
    public function create()
    {
        $nextCode = Site::generateSiteCode();
        return view('sites.create', compact('nextCode'));
    }

    /**
     * Store a newly created site in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_email' => 'nullable|email|max:150',
            'address' => 'required|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'google_maps_url' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:150',
            'contact_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['site_code'] = Site::generateSiteCode();

        // If Google Maps URL is provided and lat/lng are empty, attempt to parse coords
        if (!empty($validated['google_maps_url']) && (empty($validated['latitude']) || empty($validated['longitude']))) {
            $parsed = $this->extractCoordinatesFromUrl($validated['google_maps_url']);
            if ($parsed) {
                $validated['latitude'] = $parsed['lat'];
                $validated['longitude'] = $parsed['lng'];
            }
        }

        if (empty($validated['phone'])) {
            $validated['phone'] = $validated['client_phone'] ?? $validated['contact_phone'] ?? '-';
        }
        if (empty($validated['contact_person'])) {
            $validated['contact_person'] = $validated['client_name'] ?? 'Site Manager';
        }
        if (empty($validated['google_maps_link']) && !empty($validated['google_maps_url'])) {
            $validated['google_maps_link'] = $validated['google_maps_url'];
        }

        $site = Site::create($validated);

        ProjectAuditLog::logChange(
            $site,
            'created',
            null,
            null,
            "Site {$site->site_code} ({$site->name}) created"
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'site' => $site,
                'message' => "Site {$site->site_code} successfully registered."
            ]);
        }

        return redirect()->route('sites.show', $site)
            ->with('status', "Site #{$site->site_code} created successfully!");
    }

    /**
     * Display the specified site with map and associated projects.
     */
    public function show(Site $site)
    {
        $site->load(['projects.company', 'projects.leadTechnician']);

        return view('sites.show', compact('site'));
    }

    /**
     * Show the form for editing the specified site.
     */
    public function edit(Site $site)
    {
        return view('sites.edit', compact('site'));
    }

    /**
     * Update the specified site in storage.
     */
    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_email' => 'nullable|email|max:150',
            'address' => 'required|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'google_maps_url' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:150',
            'contact_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        if (!empty($validated['google_maps_url']) && (empty($validated['latitude']) || empty($validated['longitude']))) {
            $parsed = $this->extractCoordinatesFromUrl($validated['google_maps_url']);
            if ($parsed) {
                $validated['latitude'] = $parsed['lat'];
                $validated['longitude'] = $parsed['lng'];
            }
        }

        if (empty($validated['phone'])) {
            $validated['phone'] = $validated['client_phone'] ?? $validated['contact_phone'] ?? $site->phone ?? '-';
        }
        if (empty($validated['contact_person'])) {
            $validated['contact_person'] = $validated['client_name'] ?? $site->contact_person ?? 'Site Manager';
        }
        if (empty($validated['google_maps_link']) && !empty($validated['google_maps_url'])) {
            $validated['google_maps_link'] = $validated['google_maps_url'];
        }

        $site->update($validated);

        return redirect()->route('sites.show', $site)
            ->with('status', "Site #{$site->site_code} details updated successfully.");
    }

    /**
     * Remove the specified site from storage.
     */
    public function destroy(Site $site)
    {
        if ($site->projects()->exists()) {
            return back()->with('error', 'Cannot delete site because projects are already linked to it.');
        }

        $code = $site->site_code;
        $site->delete();

        return redirect()->route('sites.index')
            ->with('status', "Site #{$code} deleted.");
    }

    /**
     * JSON search API for Project Wizard Autocomplete.
     */
    public function search(Request $request)
    {
        $query = trim($request->input('q', ''));

        if (strlen($query) < 1) {
            $sites = Site::latest()->limit(10)->get();
        } else {
            $sites = Site::where('name', 'like', "%{$query}%")
                ->orWhere('site_code', 'like', "%{$query}%")
                ->orWhere('client_name', 'like', "%{$query}%")
                ->orWhere('client_phone', 'like', "%{$query}%")
                ->orWhere('address', 'like', "%{$query}%")
                ->limit(20)
                ->get();
        }

        return response()->json($sites);
    }

    /**
     * Check for duplicate sites by phone number or location proximity.
     */
    public function checkDuplicate(Request $request)
    {
        $phone = trim($request->input('phone', ''));
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $name = trim($request->input('name', ''));

        $duplicate = null;

        if (!empty($phone)) {
            $duplicate = Site::where('client_phone', $phone)
                ->orWhere('contact_phone', $phone)
                ->first();
        }

        if (!$duplicate && !empty($name)) {
            $duplicate = Site::where('name', 'like', "%{$name}%")->first();
        }

        if ($duplicate) {
            return response()->json([
                'duplicate' => true,
                'reason' => "Matching existing site found: {$duplicate->site_code} - {$duplicate->name} ({$duplicate->address})",
                'site' => $duplicate
            ]);
        }

        return response()->json(['duplicate' => false]);
    }

    /**
     * Helper to extract coordinates from Google Maps URLs.
     */
    protected function extractCoordinatesFromUrl(string $url): ?array
    {
        // Pattern 1: @lat,lng,zoom
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return ['lat' => (float) $matches[1], 'lng' => (float) $matches[2]];
        }

        // Pattern 2: ?q=lat,lng
        if (preg_match('/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return ['lat' => (float) $matches[1], 'lng' => (float) $matches[2]];
        }

        // Pattern 3: !3dlat!4dlng
        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $matches)) {
            return ['lat' => (float) $matches[1], 'lng' => (float) $matches[2]];
        }

        return null;
    }
}

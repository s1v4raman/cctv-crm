<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadStatusRequest;
use App\Models\Lead;
use App\Services\AlertNotificationService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /** Saved views shown in the view menu, and what each one keeps. */
    public const VIEWS = [
        'all'  => 'All Leads',
        'open' => 'Open Leads',
        'won'  => 'Won Leads',
        'lost' => 'Lost Leads',
    ];

    /** Quick filters in the filter panel's first section. */
    public const QUICK_FILTERS = [
        'recent'     => 'Added in last 7 days',
        'untouched'  => 'Untouched for 30 days',
        'quoted'     => 'Has a quotation',
        'not_quoted' => 'No quotation yet',
        'business'   => 'Business (has GSTIN)',
    ];

    public const SORTS = [
        'date_desc'   => 'Newest first',
        'date_asc'    => 'Oldest first',
        'name_asc'    => 'Lead name, A to Z',
        'name_desc'   => 'Lead name, Z to A',
        'company_asc' => 'Company, A to Z',
    ];

    public function index(Request $request)
    {
        $view = array_key_exists($request->get('view'), self::VIEWS) ? $request->get('view') : 'all';
        $sort = array_key_exists($request->get('sort'), self::SORTS) ? $request->get('sort') : 'date_desc';
        $layout = $request->get('layout') === 'board' ? 'board' : 'list';
        $perPage = in_array((int) $request->get('per_page'), [10, 20, 50, 100], true) ? (int) $request->get('per_page') : 20;

        $query = $this->filteredQuery($request);
        $leads = (clone $query)->paginate($perPage)->withQueryString();

        // The board groups everything that matches (up to 200) by status, not just one page
        $board = $layout === 'board'
            ? (clone $query)->limit(200)->get()->groupBy('status')
            : collect();

        $statusCounts = Lead::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status');
        $sources = Lead::whereNotNull('source')->where('source', '!=', '')
            ->distinct()->orderBy('source')->pluck('source');

        return view('leads.index', compact('leads', 'board', 'statusCounts', 'sources', 'view', 'sort', 'layout', 'perPage'));
    }

    /** The current list as a CSV file, with the same filters and order as the screen. */
    public function export(Request $request)
    {
        $leads = $this->filteredQuery($request)->get();
        $filename = 'leads-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($leads) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Lead name', 'Company', 'Email', 'Phone', 'Lead source', 'Status', 'GSTIN', 'Site address', 'Added on']);
            foreach ($leads as $lead) {
                fputcsv($out, [
                    $lead->customer_name, $lead->company_name, $lead->email, $lead->phone, $lead->source,
                    ucfirst($lead->status), $lead->gstin, $lead->site_address, $lead->created_at?->format('Y-m-d'),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /** Change the status of, or (admins only) delete, the leads ticked in the list. */
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids'    => 'required|array|min:1|max:500',
            'ids.*'  => 'integer|exists:leads,id',
            'action' => 'required|in:status,delete',
            'status' => 'required_if:action,status|nullable|in:new,contacted,quoted,won,lost',
        ]);

        $leads = Lead::whereIn('id', $data['ids']);
        $count = count($data['ids']);

        if ($data['action'] === 'delete') {
            abort_unless($request->user()->isAdmin(), 403);
            $leads->get()->each->delete();
            return back()->with('status', $count . ' ' . str('lead')->plural($count) . ' deleted.');
        }

        $leads->update(['status' => $data['status']]);
        return back()->with('status', $count . ' ' . str('lead')->plural($count) . ' marked ' . $data['status'] . '.');
    }

    /** Search, view, quick filters, field filters, first letter and sort, shared by the list and the export. */
    private function filteredQuery(Request $request)
    {
        $query = Lead::query();
        $like = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search, $like) {
                $q->where('customer_name', $like, "%{$search}%")
                  ->orWhere('phone', $like, "%{$search}%")
                  ->orWhere('email', $like, "%{$search}%")
                  ->orWhere('site_address', $like, "%{$search}%")
                  ->orWhere('company_legal_name', $like, "%{$search}%");
            });
        }

        match ($request->get('view')) {
            'open'  => $query->whereIn('status', ['new', 'contacted', 'quoted']),
            'won'   => $query->where('status', 'won'),
            'lost'  => $query->where('status', 'lost'),
            default => null,
        };

        foreach ((array) $request->get('only', []) as $quick) {
            match ($quick) {
                'recent'     => $query->where('created_at', '>=', now()->subDays(7)),
                'untouched'  => $query->where('updated_at', '<', now()->subDays(30)),
                'quoted'     => $query->has('quotations'),
                'not_quoted' => $query->doesntHave('quotations'),
                'business'   => $query->whereNotNull('gstin')->where('gstin', '!=', ''),
                default      => null,
            };
        }

        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->status);
        }
        if ($request->filled('source')) {
            $query->whereIn('source', (array) $request->source);
        }
        if (preg_match('/^[A-Z]$/', (string) $request->get('letter'))) {
            $query->where('customer_name', $like, $request->get('letter') . '%');
        }

        match ($request->get('sort')) {
            'name_asc'    => $query->orderBy('customer_name'),
            'name_desc'   => $query->orderByDesc('customer_name'),
            'date_asc'    => $query->orderBy('created_at'),
            'company_asc' => $query->orderBy('company_legal_name')->orderBy('customer_name'),
            default       => $query->orderByDesc('created_at'),
        };

        return $query->orderByDesc('id');
    }

    public function create()
    {
        return view('leads.create');
    }

    public function store(StoreLeadRequest $request, AlertNotificationService $alertService)
    {
        $data = $request->safe()->except(['for_quote', 'return_to']);
        $lead = Lead::create($data);

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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'lead'    => [
                    'id'            => $lead->id,
                    'customer_name' => $lead->customer_name,
                    'company_name'  => $lead->company_name,
                    'phone'         => $lead->phone,
                    'email'         => $lead->email,
                    'site_address'  => $lead->site_address,
                    'notes'         => $lead->notes,
                ],
                'message' => "Customer '{$lead->customer_name}' registered successfully.",
            ]);
        }

        if ($request->boolean('for_quote') || $request->input('return_to') === 'quotation') {
            return redirect()->route('quotations.create', $lead)
                ->with('status', "Customer '{$lead->customer_name}' added. You can now create their quotation.");
        }

        if ($request->input('return_to') === 'survey') {
            return redirect()->route('site-surveys.create', ['lead_id' => $lead->id])
                ->with('status', "Customer '{$lead->customer_name}' added. You can now schedule their site survey.");
        }

        if ($request->input('return_to') === 'equipment') {
            return redirect()->route('equipment.create', ['lead_id' => $lead->id])
                ->with('status', "Customer '{$lead->customer_name}' added. You can now register their equipment.");
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

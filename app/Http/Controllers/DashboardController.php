<?php

namespace App\Http\Controllers;

use App\Models\InstallationJob;
use App\Models\Lead;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
{
    return view('dashboard');
}

public function data(): JsonResponse
{
    $now = now();

    $wonLeads = Lead::where('status', 'won')->count();
    $lostLeads = Lead::where('status', 'lost')->count();
    $decidedLeads = $wonLeads + $lostLeads;

    $stats = [
        'total_leads' => Lead::count(),

        'leads_this_month' => Lead::query()
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count(),

        'won_leads' => $wonLeads,

        'lost_leads' => $lostLeads,

        'conversion_rate' => $decidedLeads > 0
            ? round(($wonLeads / $decidedLeads) * 100, 1)
            : 0,

        'revenue_accepted' => (float) Quotation::query()
            ->where('status', 'accepted')
            ->sum('total'),

        'revenue_this_month' => (float) Quotation::query()
            ->where('status', 'accepted')
            ->whereMonth('accepted_at', $now->month)
            ->whereYear('accepted_at', $now->year)
            ->sum('total'),

        'draft_quotations' => Quotation::where('status', 'draft')->count(),

        'sent_quotations' => Quotation::where('status', 'sent')->count(),

        'accepted_quotations' => Quotation::where('status', 'accepted')->count(),

        'rejected_quotations' => Quotation::where('status', 'rejected')->count(),

        'expired_quotations' => Quotation::where('status', 'expired')->count(),

        'open_jobs' => InstallationJob::query()
            ->whereIn('status', [
                'pending',
                'scheduled',
                'assigned',
                'in_progress',
            ])
            ->count(),

        'in_progress_jobs' => InstallationJob::query()
            ->whereIn('status', [
                'assigned',
                'in_progress',
            ])
            ->count(),

        'completed_jobs' => InstallationJob::query()
            ->where('status', 'completed')
            ->count(),
    ];

    $leadStatuses = [
        'new',
        'contacted',
        'quoted',
        'won',
        'lost',
    ];

    $quotationStatuses = [
        'draft',
        'sent',
        'accepted',
        'rejected',
        'expired',
    ];

    $jobStatuses = [
        'pending',
        'scheduled',
        'assigned',
        'in_progress',
        'completed',
        'cancelled',
    ];

    $leadStatusCounts = Lead::query()
        ->select('status', DB::raw('count(*) as total'))
        ->groupBy('status')
        ->pluck('total', 'status');

    $quotationStatusCounts = Quotation::query()
        ->select('status', DB::raw('count(*) as total'))
        ->groupBy('status')
        ->pluck('total', 'status');

    $jobStatusCounts = InstallationJob::query()
        ->select('status', DB::raw('count(*) as total'))
        ->groupBy('status')
        ->pluck('total', 'status');

    $months = collect(range(5, 0))
        ->map(fn ($monthOffset) => now()->subMonths($monthOffset));

    $revenueTrend = $months->map(function ($month) {
        return (float) Quotation::query()
            ->where('status', 'accepted')
            ->whereMonth('accepted_at', $month->month)
            ->whereYear('accepted_at', $month->year)
            ->sum('total');
    });

    return response()->json([
        'stats' => $stats,

        'leadChart' => [
            'labels' => collect($leadStatuses)
                ->map(fn ($status) => ucfirst($status))
                ->values(),

            'data' => collect($leadStatuses)
                ->map(fn ($status) => $leadStatusCounts[$status] ?? 0)
                ->values(),
        ],

        'quotationChart' => [
            'labels' => collect($quotationStatuses)
                ->map(fn ($status) => ucfirst($status))
                ->values(),

            'data' => collect($quotationStatuses)
                ->map(fn ($status) => $quotationStatusCounts[$status] ?? 0)
                ->values(),
        ],

        'jobChart' => [
            'labels' => collect($jobStatuses)
                ->map(fn ($status) => ucfirst(str_replace('_', ' ', $status)))
                ->values(),

            'data' => collect($jobStatuses)
                ->map(fn ($status) => $jobStatusCounts[$status] ?? 0)
                ->values(),
        ],

        'revenueChart' => [
            'labels' => $months
                ->map(fn ($month) => $month->format('M Y'))
                ->values(),

            'data' => $revenueTrend->values(),
        ],

        'recentLeads' => Lead::query()
            ->latest()
            ->limit(6)
            ->get([
                'id',
                'customer_name',
                'phone',
                'status',
                'created_at',
            ]),

        'recentQuotations' => Quotation::query()
            ->with('lead:id,customer_name')
            ->latest()
            ->limit(6)
            ->get([
                'id',
                'quotation_no',
                'lead_id',
                'total',
                'status',
                'created_at',
            ]),

        'upcomingJobs' => InstallationJob::query()
            ->with('quotation.lead:id,customer_name')
            ->whereIn('status', [
                'pending',
                'scheduled',
                'assigned',
                'in_progress',
            ])
            ->orderByRaw('scheduled_date IS NULL')
            ->orderBy('scheduled_date')
            ->limit(6)
            ->get([
                'id',
                'job_no',
                'quotation_id',
                'status',
                'scheduled_date',
            ]),
    ]);
}
}
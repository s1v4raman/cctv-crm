<?php

namespace App\Http\Controllers;

use App\Models\InstallationJob;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\ServiceTicket;
use App\Models\SiteSurvey;
use App\Models\EmployeeAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $isAdmin = $user ? $user->isAdmin() : false;
        
        $myAttendanceToday = $user ? EmployeeAttendance::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first() : null;

        return view('dashboard', compact('isAdmin', 'myAttendanceToday'));
    }

    public function data(): JsonResponse
    {
        $user = auth()->user();
        $isAdmin = $user ? $user->isAdmin() : false;
        $now = now();

        $jobStatuses = [
            'pending',
            'scheduled',
            'assigned',
            'in_progress',
            'completed',
            'cancelled',
        ];

        $jobStatusCounts = InstallationJob::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $jobChart = [
            'labels' => collect($jobStatuses)
                ->map(fn ($status) => ucfirst(str_replace('_', ' ', $status)))
                ->values(),
            'data' => collect($jobStatuses)
                ->map(fn ($status) => $jobStatusCounts[$status] ?? 0)
                ->values(),
        ];

        $upcomingJobs = InstallationJob::query()
            ->with(['quotation.lead:id,customer_name', 'technician:id,name'])
            ->whereIn('status', [
                'pending',
                'scheduled',
                'assigned',
                'in_progress',
            ])
            ->orderByRaw('scheduled_date IS NULL')
            ->orderBy('scheduled_date')
            ->limit(8)
            ->get([
                'id',
                'job_no',
                'quotation_id',
                'assigned_technician_id',
                'status',
                'scheduled_date',
            ]);

        // If user is non-admin (employee / technician), provide work-based data ONLY
        if (!$isAdmin) {
            $myAttendance = EmployeeAttendance::where('user_id', $user->id)
                ->whereDate('date', $now->toDateString())
                ->first();

            $openTickets = ServiceTicket::query()
                ->with(['lead:id,customer_name', 'technician:id,name'])
                ->whereIn('status', ['open', 'assigned', 'in_progress'])
                ->orderByRaw('CASE WHEN priority = "critical" THEN 1 WHEN priority = "high" THEN 2 WHEN priority = "medium" THEN 3 ELSE 4 END')
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get([
                    'id',
                    'ticket_no',
                    'lead_id',
                    'assigned_technician_id',
                    'title',
                    'priority',
                    'status',
                    'created_at',
                ]);

            $pendingSurveys = SiteSurvey::query()
                ->with('lead:id,customer_name')
                ->where('status', 'pending')
                ->orderBy('survey_date')
                ->limit(6)
                ->get(['id', 'lead_id', 'survey_date', 'status']);

            return response()->json([
                'is_admin' => false,
                'stats' => [
                    'open_jobs' => InstallationJob::whereIn('status', ['pending', 'scheduled', 'assigned', 'in_progress'])->count(),
                    'in_progress_jobs' => InstallationJob::whereIn('status', ['assigned', 'in_progress'])->count(),
                    'completed_jobs' => InstallationJob::where('status', 'completed')->count(),
                    'open_tickets' => ServiceTicket::whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
                    'critical_tickets' => ServiceTicket::whereIn('status', ['open', 'assigned', 'in_progress'])->where('priority', 'critical')->count(),
                    'pending_surveys' => SiteSurvey::where('status', 'pending')->count(),
                    'my_assigned_jobs' => InstallationJob::where('assigned_technician_id', $user->id)->whereIn('status', ['pending', 'scheduled', 'assigned', 'in_progress'])->count(),
                ],
                'jobChart' => $jobChart,
                'upcomingJobs' => $upcomingJobs,
                'recentTickets' => $openTickets,
                'pendingSurveys' => $pendingSurveys,
                'attendance' => [
                    'clocked_in' => $myAttendance && $myAttendance->clock_in !== null,
                    'clock_in' => $myAttendance ? $myAttendance->clock_in : null,
                    'clock_out' => $myAttendance ? $myAttendance->clock_out : null,
                    'status' => $myAttendance ? $myAttendance->status : 'unmarked',
                    'total_hours' => $myAttendance ? (float)$myAttendance->total_hours : 0.00,
                ],
            ]);
        }

        // Admin sees full executive analytics and financial data
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
            'open_tickets' => ServiceTicket::whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
            'total_projects' => \App\Models\Project::count(),
            'in_progress_projects' => \App\Models\Project::where('status', 'in_progress')->count(),
            'pending_approval_projects' => \App\Models\Project::where('status', 'pending_approval')->count(),
            'hardware_attendance_projects' => \App\Models\Project::where('project_type', 'hardware_attendance')->count(),
            'total_project_valuation' => (float) \App\Models\Project::sum('budget'),
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

        $leadStatusCounts = Lead::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $quotationStatusCounts = Quotation::query()
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
            'is_admin' => true,
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
            'jobChart' => $jobChart,
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
            'upcomingJobs' => $upcomingJobs,
        ]);
    }
}
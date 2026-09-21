<?php

namespace App\Http\Controllers;

use App\Models\AmcVisit;
use App\Models\InstallationJob;
use App\Models\ServiceTicket;
use App\Models\SiteSurvey;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Display the Operations Calendar.
     */
    public function index(Request $request)
    {
        $monthInput = $request->query('month', now()->format('Y-m'));
        try {
            $currentMonth = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Exception $e) {
            $currentMonth = now()->startOfMonth();
        }

        $startDate = $currentMonth->copy()->startOfMonth()->subDays(7);
        $endDate = $currentMonth->copy()->endOfMonth()->addDays(7);

        $events = $this->fetchEvents($startDate, $endDate);
        
        $todayDate = now()->format('Y-m-d');
        $todayEvents = collect($events)->where('date', $todayDate)->values();

        $technicians = User::whereIn('role', ['technician', 'admin', 'staff'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $stats = [
            'total'     => count($events),
            'surveys'   => collect($events)->where('type', 'survey')->count(),
            'jobs'      => collect($events)->where('type', 'job')->count(),
            'amcs'      => collect($events)->where('type', 'amc')->count(),
            'tickets'   => collect($events)->where('type', 'ticket')->count(),
            'today'     => count($todayEvents),
        ];

        return view('calendar.index', [
            'currentMonth' => $currentMonth,
            'events'       => $events,
            'todayEvents'  => $todayEvents,
            'technicians'  => $technicians,
            'stats'        => $stats,
        ]);
    }

    /**
     * JSON API Endpoint for Calendar Events.
     */
    public function events(Request $request)
    {
        $start = $request->query('start') ? Carbon::parse($request->query('start')) : now()->startOfMonth()->subDays(7);
        $end = $request->query('end') ? Carbon::parse($request->query('end')) : now()->endOfMonth()->addDays(7);

        $events = $this->fetchEvents($start, $end);

        return response()->json($events);
    }

    /**
     * Collect and standardize events across all models.
     */
    protected function fetchEvents(Carbon $startDate, Carbon $endDate): array
    {
        $events = [];

        // 1. Site Surveys
        $surveys = SiteSurvey::with(['lead', 'surveyedBy'])
            ->whereNotNull('survey_date')
            ->whereBetween('survey_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        foreach ($surveys as $s) {
            $events[] = [
                'id'          => 'survey-' . $s->id,
                'raw_id'      => $s->id,
                'type'        => 'survey',
                'badge'       => 'Site Survey',
                'title'       => 'Survey: ' . ($s->lead->customer_name ?? 'Premises Audit'),
                'date'        => $s->survey_date->format('Y-m-d'),
                'time'        => $s->survey_date->format('H:i') !== '00:00' ? $s->survey_date->format('h:i A') : 'Full Day',
                'status'      => $s->status,
                'customer'    => $s->lead->customer_name ?? '—',
                'phone'       => $s->contact_phone ?: ($s->lead->phone ?? '—'),
                'address'     => $s->site_address ?: ($s->lead->site_address ?? '—'),
                'tech_id'     => $s->surveyed_by,
                'tech'        => $s->surveyedBy->name ?? 'Unassigned',
                'url'         => route('site-surveys.show', $s),
                'color'       => 'amber',
                'notes'       => $s->visit_notes,
            ];
        }

        // 2. Installation Jobs
        $jobs = InstallationJob::with(['quotation.lead', 'assignedTechnician'])
            ->whereNotNull('scheduled_date')
            ->whereBetween('scheduled_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        foreach ($jobs as $j) {
            $customerName = $j->quotation->lead->customer_name ?? 'Client';
            $events[] = [
                'id'          => 'job-' . $j->id,
                'raw_id'      => $j->id,
                'type'        => 'job',
                'badge'       => 'Installation Job',
                'title'       => 'Job ' . $j->job_no . ': ' . $customerName,
                'date'        => $j->scheduled_date->format('Y-m-d'),
                'time'        => '09:00 AM',
                'status'      => $j->status,
                'customer'    => $customerName,
                'phone'       => $j->quotation->lead->phone ?? '—',
                'address'     => $j->quotation->lead->site_address ?? '—',
                'tech_id'     => $j->assigned_technician_id,
                'tech'        => $j->assignedTechnician->name ?? 'Unassigned',
                'url'         => route('jobs.show', $j),
                'color'       => 'blue',
                'notes'       => $j->installation_notes,
            ];
        }

        // 3. AMC Routine Visits
        $visits = AmcVisit::with(['amcContract.lead', 'assignedTechnician'])
            ->whereNotNull('scheduled_date')
            ->whereBetween('scheduled_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        foreach ($visits as $v) {
            $contract = $v->amcContract;
            $customerName = $contract->lead->customer_name ?? 'Client';
            $events[] = [
                'id'          => 'amc-' . $v->id,
                'raw_id'      => $v->id,
                'type'        => 'amc',
                'badge'       => 'AMC Routine Visit',
                'title'       => 'AMC #' . ($contract->contract_no ?? $v->amc_contract_id) . ' (' . ucfirst($contract->frequency ?? 'Quarterly') . ')',
                'date'        => $v->scheduled_date->format('Y-m-d'),
                'time'        => '10:30 AM',
                'status'      => $v->status,
                'customer'    => $customerName,
                'phone'       => $contract->lead->phone ?? '—',
                'address'     => $contract->lead->site_address ?? '—',
                'tech_id'     => $v->assigned_technician_id,
                'tech'        => $v->assignedTechnician->name ?? 'Unassigned',
                'url'         => route('amcs.show', $v->amc_contract_id),
                'color'       => 'emerald',
                'notes'       => $v->completion_notes,
            ];
        }

        // 4. Service Tickets
        $tickets = ServiceTicket::with(['lead', 'assignedTechnician'])
            ->whereNotNull('scheduled_date')
            ->whereBetween('scheduled_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        foreach ($tickets as $t) {
            $customerName = $t->lead->customer_name ?? 'Client';
            $events[] = [
                'id'          => 'ticket-' . $t->id,
                'raw_id'      => $t->id,
                'type'        => 'ticket',
                'badge'       => 'Service Ticket',
                'title'       => 'Ticket ' . $t->ticket_no . ': ' . ($t->title ?: $t->issue_type_label),
                'date'        => $t->scheduled_date->format('Y-m-d'),
                'time'        => '02:00 PM',
                'status'      => $t->status,
                'priority'    => $t->priority,
                'customer'    => $customerName,
                'phone'       => $t->lead->phone ?? '—',
                'address'     => $t->lead->site_address ?? '—',
                'tech_id'     => $t->assigned_technician_id,
                'tech'        => $t->assignedTechnician->name ?? 'Unassigned',
                'url'         => route('service-tickets.show', $t),
                'color'       => 'rose',
                'notes'       => $t->description,
            ];
        }

        // Sort by date and time
        usort($events, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return $events;
    }
}

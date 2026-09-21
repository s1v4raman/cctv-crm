<?php

namespace App\Http\Controllers;

use App\Models\AmcContract;
use App\Models\Lead;
use App\Models\ServiceTicket;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServiceTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceTicket::with(['lead', 'amcContract', 'assignedTechnician', 'createdBy']);

        // Tab filter
        $activeTab = $request->query('tab', 'all');
        if ($activeTab === 'critical') {
            $query->where('priority', 'critical')->whereIn('status', ['open', 'assigned', 'in_progress']);
        } elseif ($activeTab === 'open') {
            $query->whereIn('status', ['open', 'assigned']);
        } elseif ($activeTab === 'in_progress') {
            $query->where('status', 'in_progress');
        } elseif ($activeTab === 'resolved') {
            $query->where('status', 'resolved');
        } elseif ($activeTab === 'closed') {
            $query->where('status', 'closed');
        }

        // Additional Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('technician_id')) {
            $query->where('assigned_technician_id', $request->technician_id);
        }

        if ($request->filled('issue_type')) {
            $query->where('issue_type', $request->issue_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('lead', function ($leadQuery) use ($search) {
                      $leadQuery->where('customer_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('site_address', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();

        // Metrics & Tab Counts
        $tabCounts = [
            'all' => ServiceTicket::count(),
            'critical' => ServiceTicket::where('priority', 'critical')->whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
            'open' => ServiceTicket::whereIn('status', ['open', 'assigned'])->count(),
            'in_progress' => ServiceTicket::where('status', 'in_progress')->count(),
            'resolved' => ServiceTicket::where('status', 'resolved')->count(),
            'closed' => ServiceTicket::where('status', 'closed')->count(),
        ];

        // Issue Category Distribution Statistics
        $issueCategoryCounts = ServiceTicket::selectRaw('issue_type, count(*) as count')
            ->groupBy('issue_type')
            ->pluck('count', 'issue_type')
            ->toArray();

        $metrics = [
            'total' => $tabCounts['all'],
            'open' => ServiceTicket::whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
            'critical' => $tabCounts['critical'],
            'resolved' => $tabCounts['resolved'] + $tabCounts['closed'],
            'overdue' => ServiceTicket::whereIn('status', ['open', 'assigned', 'in_progress'])
                ->where('created_at', '<', now()->subHours(48))
                ->count(),
        ];

        $technicians = User::where('role', 'technician')->orderBy('name')->get();

        return view('service_tickets.index', compact('tickets', 'metrics', 'technicians', 'tabCounts', 'activeTab', 'issueCategoryCounts'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = ServiceTicket::with(['lead', 'amcContract', 'assignedTechnician', 'createdBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('technician_id')) {
            $query->where('assigned_technician_id', $request->technician_id);
        }
        if ($request->filled('issue_type')) {
            $query->where('issue_type', $request->issue_type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('lead', function ($leadQuery) use ($search) {
                      $leadQuery->where('customer_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->latest()->get();

        $filename = 'cctv-service-tickets-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($tickets) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 Byte Order Mark (BOM) so Excel/spreadsheet viewers open it cleanly without encoding or corruption errors
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Ticket No',
                'Customer Name',
                'Phone',
                'Site Address',
                'Issue Category',
                'Title',
                'Priority',
                'Status',
                'Billing Type',
                'Cost (INR)',
                'Assigned Technician',
                'Scheduled Date',
                'Troubleshooting Notes',
                'Parts Replaced',
                'Resolution Notes',
                'Created At',
                'Resolved At',
            ]);

            foreach ($tickets as $ticket) {
                fputcsv($handle, [
                    $ticket->ticket_no ?? '',
                    $ticket->lead?->customer_name ?? 'N/A',
                    $ticket->lead?->phone ?? '',
                    $ticket->lead?->site_address ?? '',
                    $ticket->issue_type_label ?? $ticket->issue_type,
                    $ticket->title ?? '',
                    $ticket->priority_label ?? $ticket->priority,
                    $ticket->status_label ?? $ticket->status,
                    $ticket->billing_type ? ucwords(str_replace('_', ' ', $ticket->billing_type)) : '',
                    $ticket->cost !== null ? number_format((float) $ticket->cost, 2, '.', '') : '0.00',
                    $ticket->assignedTechnician?->name ?? 'Unassigned',
                    $ticket->scheduled_date ? Carbon::parse($ticket->scheduled_date)->format('Y-m-d') : '',
                    $ticket->troubleshooting_notes ?? '',
                    $ticket->parts_replaced ?? '',
                    $ticket->resolution_notes ?? '',
                    $ticket->created_at ? Carbon::parse($ticket->created_at)->format('Y-m-d H:i:s') : '',
                    $ticket->resolved_at ? Carbon::parse($ticket->resolved_at)->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $query = ServiceTicket::with(['lead', 'amcContract', 'assignedTechnician', 'createdBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('technician_id')) {
            $query->where('assigned_technician_id', $request->technician_id);
        }
        if ($request->filled('issue_type')) {
            $query->where('issue_type', $request->issue_type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('lead', function ($leadQuery) use ($search) {
                      $leadQuery->where('customer_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->latest()->get();

        $pdf = Pdf::loadView('service_tickets.pdf.report', compact('tickets'))
            ->setPaper('a4', 'landscape');

        $filename = 'cctv-service-tickets-' . now()->format('Y-m-d-His') . '.pdf';

        return $pdf->download($filename);
    }

    public function getLeadAmc(Lead $lead)
    {
        $activeAmc = $lead->amcContracts()
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->latest('end_date')
            ->first();

        if (!$activeAmc) {
            return response()->json(['has_active_amc' => false]);
        }

        return response()->json([
            'has_active_amc' => true,
            'amc_contract_id' => $activeAmc->id,
            'contract_no' => $activeAmc->contract_no,
            'end_date' => $activeAmc->end_date->format('d M Y'),
            'frequency' => ucfirst(str_replace('_', ' ', $activeAmc->frequency)),
        ]);
    }

    public function create(Request $request)
    {
        $leads = Lead::with('activeAmcContract')->orderBy('customer_name')->get();
        $technicians = User::where('role', 'technician')->orderBy('name')->get();
        $amcContracts = AmcContract::with('lead')->where('status', 'active')->get();

        $selectedLeadId = $request->query('lead_id');
        $selectedAmcId = $request->query('amc_contract_id');

        if ($selectedLeadId && !$selectedAmcId) {
            $selectedLead = Lead::find($selectedLeadId);
            if ($selectedLead && $selectedLead->activeAmcContract) {
                $selectedAmcId = $selectedLead->activeAmcContract->id;
            }
        }

        return view('service_tickets.create', compact('leads', 'technicians', 'amcContracts', 'selectedLeadId', 'selectedAmcId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => ['required', 'exists:leads,id'],
            'amc_contract_id' => ['nullable', 'exists:amc_contracts,id'],
            'assigned_technician_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'issue_type' => ['required', Rule::in(array_keys(ServiceTicket::issueTypeOptions()))],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'description' => ['required', 'string'],
            'scheduled_date' => ['nullable', 'date'],
            'billing_type' => ['required', Rule::in(['warranty_amc', 'billable', 'free_courtesy'])],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Auto generate Ticket No TCK-YYYYMM-XXXX
        $yearMonth = date('Ym');
        $lastTicket = ServiceTicket::where('ticket_no', 'like', "TCK-{$yearMonth}-%")->latest('id')->first();
        $seq = 1;
        if ($lastTicket) {
            $parts = explode('-', $lastTicket->ticket_no);
            $seq = isset($parts[2]) ? ((int) $parts[2]) + 1 : 1;
        }
        $ticketNo = sprintf('TCK-%s-%04d', $yearMonth, $seq);

        $status = 'open';
        if (!empty($validated['assigned_technician_id'])) {
            $status = 'assigned';
        }

        $ticket = ServiceTicket::create([
            'ticket_no' => $ticketNo,
            'lead_id' => $validated['lead_id'],
            'amc_contract_id' => $validated['amc_contract_id'] ?? null,
            'assigned_technician_id' => $validated['assigned_technician_id'] ?? null,
            'created_by_id' => auth()->id(),
            'title' => $validated['title'],
            'issue_type' => $validated['issue_type'],
            'priority' => $validated['priority'],
            'status' => $status,
            'description' => $validated['description'],
            'scheduled_date' => $validated['scheduled_date'] ?? null,
            'billing_type' => $validated['billing_type'],
            'cost' => $validated['cost'] ?? null,
        ]);

        // Dispatch Real-Time Alert to Customer via SMS/WhatsApp/Email
        if ($ticket->lead) {
            try {
                $alertService = app(\App\Services\AlertNotificationService::class);
                $alertService->sendAlert('ticket_created', $ticket->lead, [
                    'customer_name' => $ticket->lead->customer_name,
                    'ticket_no'     => $ticket->ticket_no,
                    'title'         => $ticket->title,
                    'priority'      => ucfirst($ticket->priority),
                ], $ticket);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Ticket created alert dispatch failed: " . $e->getMessage());
            }
        }

        return redirect()->route('service-tickets.show', $ticket)
            ->with('success', "Service Ticket {$ticket->ticket_no} logged successfully.");
    }

    public function show(ServiceTicket $serviceTicket)
    {
        $serviceTicket->load(['lead', 'amcContract', 'assignedTechnician', 'createdBy']);
        $technicians = User::where('role', 'technician')->orderBy('name')->get();

        return view('service_tickets.show', [
            'ticket' => $serviceTicket,
            'technicians' => $technicians,
        ]);
    }

    public function edit(ServiceTicket $serviceTicket)
    {
        $leads = Lead::orderBy('customer_name')->get();
        $technicians = User::where('role', 'technician')->orderBy('name')->get();
        $amcContracts = AmcContract::with('lead')->get();

        return view('service_tickets.edit', [
            'ticket' => $serviceTicket,
            'leads' => $leads,
            'technicians' => $technicians,
            'amcContracts' => $amcContracts,
        ]);
    }

    public function update(Request $request, ServiceTicket $serviceTicket)
    {
        $validated = $request->validate([
            'lead_id' => ['required', 'exists:leads,id'],
            'amc_contract_id' => ['nullable', 'exists:amc_contracts,id'],
            'assigned_technician_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'issue_type' => ['required', Rule::in(array_keys(ServiceTicket::issueTypeOptions()))],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'status' => ['required', Rule::in(array_keys(ServiceTicket::statusOptions()))],
            'description' => ['required', 'string'],
            'scheduled_date' => ['nullable', 'date'],
            'troubleshooting_notes' => ['nullable', 'string'],
            'parts_replaced' => ['nullable', 'string'],
            'resolution_notes' => ['nullable', 'string'],
            'billing_type' => ['required', Rule::in(['warranty_amc', 'billable', 'free_courtesy'])],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $resolvedAt = $serviceTicket->resolved_at;
        if (in_array($validated['status'], ['resolved', 'closed']) && !$resolvedAt) {
            $resolvedAt = now();
        } elseif (!in_array($validated['status'], ['resolved', 'closed'])) {
            $resolvedAt = null;
        }

        $closedAt = $serviceTicket->closed_at;
        if ($validated['status'] === 'closed' && !$closedAt) {
            $closedAt = now();
        } elseif ($validated['status'] !== 'closed') {
            $closedAt = null;
        }

        $serviceTicket->update(array_merge($validated, [
            'resolved_at' => $resolvedAt,
            'closed_at' => $closedAt,
        ]));

        return redirect()->route('service-tickets.show', $serviceTicket)
            ->with('success', "Service Ticket {$serviceTicket->ticket_no} updated successfully.");
    }

    public function updateStatus(Request $request, ServiceTicket $serviceTicket)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(ServiceTicket::statusOptions()))],
            'troubleshooting_notes' => ['nullable', 'string'],
            'parts_replaced' => ['nullable', 'string'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        $resolvedAt = $serviceTicket->resolved_at;
        if (in_array($validated['status'], ['resolved', 'closed']) && !$resolvedAt) {
            $resolvedAt = now();
        }

        $closedAt = $serviceTicket->closed_at;
        if ($validated['status'] === 'closed' && !$closedAt) {
            $closedAt = now();
        }

        $serviceTicket->update([
            'status' => $validated['status'],
            'troubleshooting_notes' => $validated['troubleshooting_notes'] ?? $serviceTicket->troubleshooting_notes,
            'parts_replaced' => $validated['parts_replaced'] ?? $serviceTicket->parts_replaced,
            'resolution_notes' => $validated['resolution_notes'] ?? $serviceTicket->resolution_notes,
            'resolved_at' => $resolvedAt,
            'closed_at' => $closedAt,
        ]);

        // Dispatch status update alert to customer
        if ($serviceTicket->lead) {
            try {
                $alertService = app(\App\Services\AlertNotificationService::class);
                if ($serviceTicket->status === 'resolved' || $serviceTicket->status === 'closed') {
                    $alertService->sendAlert('ticket_resolved', $serviceTicket->lead, [
                        'customer_name'    => $serviceTicket->lead->customer_name,
                        'ticket_no'        => $serviceTicket->ticket_no,
                        'resolution_notes' => $serviceTicket->resolution_notes ?: 'Service completed successfully.',
                        'jcr_link'         => route('portal.tickets'),
                    ], $serviceTicket);
                } else {
                    $alertService->sendAlert('ticket_status_updated', $serviceTicket->lead, [
                        'customer_name'   => $serviceTicket->lead->customer_name,
                        'ticket_no'       => $serviceTicket->ticket_no,
                        'status'          => $serviceTicket->status_label,
                        'technician_name' => $serviceTicket->assignedTechnician?->name ?? 'Assigned Engineer',
                        'notes'           => $serviceTicket->resolution_notes ?: ($serviceTicket->troubleshooting_notes ?: 'Status updated.'),
                        'ticket_link'     => route('portal.tickets'),
                    ], $serviceTicket);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Ticket status alert dispatch failed: " . $e->getMessage());
            }
        }

        return back()->with('success', "Ticket status updated to " . $serviceTicket->status_label);
    }

    public function assignTechnician(Request $request, ServiceTicket $serviceTicket)
    {
        $validated = $request->validate([
            'assigned_technician_id' => ['required', 'exists:users,id'],
            'scheduled_date' => ['nullable', 'date'],
        ]);

        $status = $serviceTicket->status === 'open' ? 'assigned' : $serviceTicket->status;

        $serviceTicket->update([
            'assigned_technician_id' => $validated['assigned_technician_id'],
            'scheduled_date' => $validated['scheduled_date'] ?? $serviceTicket->scheduled_date,
            'status' => $status,
        ]);

        // Dispatch alert to customer about technician assignment
        if ($serviceTicket->lead) {
            try {
                $serviceTicket->load('assignedTechnician');
                $alertService = app(\App\Services\AlertNotificationService::class);
                $alertService->sendAlert('ticket_status_updated', $serviceTicket->lead, [
                    'customer_name'   => $serviceTicket->lead->customer_name,
                    'ticket_no'       => $serviceTicket->ticket_no,
                    'status'          => 'Assigned to Technician',
                    'technician_name' => $serviceTicket->assignedTechnician?->name ?? 'Service Engineer',
                    'notes'           => 'Technician assigned for scheduled service visit.',
                    'ticket_link'     => route('portal.tickets'),
                ], $serviceTicket);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Technician assign alert failed: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Technician assigned successfully.');
    }

    public function acceptRequest(Request $request, ServiceTicket $serviceTicket)
    {
        $validated = $request->validate([
            'assigned_technician_id' => ['nullable', 'exists:users,id'],
            'scheduled_date'         => ['nullable', 'date'],
            'admin_notes'            => ['nullable', 'string', 'max:1000'],
        ]);

        $status = !empty($validated['assigned_technician_id']) ? 'assigned' : 'in_progress';

        $notes = $serviceTicket->resolution_notes;
        if (!empty($validated['admin_notes'])) {
            $notes = ($notes ? $notes . "\n\n" : "") . "Admin Approval Note: " . $validated['admin_notes'];
        }

        $serviceTicket->update([
            'status'                 => $status,
            'assigned_technician_id' => $validated['assigned_technician_id'] ?? $serviceTicket->assigned_technician_id,
            'scheduled_date'         => $validated['scheduled_date'] ?? $serviceTicket->scheduled_date,
            'resolution_notes'       => $notes,
        ]);

        return back()->with('success', "Work Request #{$serviceTicket->ticket_no} has been APPROVED and updated to {$serviceTicket->status_label}.");
    }

    public function rejectRequest(Request $request, ServiceTicket $serviceTicket)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $notes = "DECLINED BY ADMIN: " . $validated['rejection_reason'];
        if (!empty($serviceTicket->resolution_notes)) {
            $notes .= "\n\n" . $serviceTicket->resolution_notes;
        }

        $serviceTicket->update([
            'status'           => 'cancelled',
            'resolution_notes' => $notes,
            'closed_at'        => now(),
        ]);

        return back()->with('success', "Work Request #{$serviceTicket->ticket_no} has been DECLINED / REJECTED.");
    }

    public function destroy(ServiceTicket $serviceTicket)
    {
        $ticketNo = $serviceTicket->ticket_no;
        $serviceTicket->delete();

        return redirect()->route('service-tickets.index')
            ->with('success', "Service Ticket {$ticketNo} deleted successfully.");
    }
}

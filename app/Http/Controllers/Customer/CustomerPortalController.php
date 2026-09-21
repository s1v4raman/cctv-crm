<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AmcContract;
use App\Models\InstalledEquipment;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\ServiceTicket;
use App\Models\SiteSurvey;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    /**
     * Resolve the active Lead (Customer Site) record for the authenticated customer.
     */
    protected function getCustomerLead(): ?Lead
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        return $user->getCustomerLead();
    }

    /**
     * Customer Portal Dashboard overview.
     */
    public function dashboard(): View
    {
        $lead = $this->getCustomerLead();

        if (!$lead) {
            return view('customer.portal.dashboard', [
                'hasLead' => false,
                'lead' => null,
            ]);
        }

        // Equipment metrics
        $equipment = $lead->installedEquipment()->with('product')->get();
        $totalEquipment = $equipment->count();
        $activeWarrantyCount = $equipment->filter(fn($e) => $e->mfg_warranty_status === 'active' || $e->service_warranty_status === 'active')->count();
        $expiringSoonCount = $equipment->filter(fn($e) => $e->mfg_warranty_status === 'expiring_soon' || $e->service_warranty_status === 'expiring_soon')->count();
        $expiredCount = $equipment->filter(fn($e) => $e->mfg_warranty_status === 'expired' && $e->service_warranty_status === 'expired')->count();

        // AMC Contract
        $activeAmc = $lead->activeAmcContract()->with('visits.assignedTechnician')->first();
        $nextVisit = null;
        if ($activeAmc) {
            $nextVisit = $activeAmc->visits()
                ->where('status', 'pending')
                ->where('scheduled_date', '>=', now()->toDateString())
                ->orderBy('scheduled_date')
                ->first();
        }

        // Service Tickets
        $recentTickets = $lead->serviceTickets()->with('assignedTechnician')->latest()->limit(5)->get();
        $openTicketsCount = $lead->serviceTickets()->whereIn('status', ['open', 'assigned', 'in_progress'])->count();

        // Invoices
        $invoices = Invoice::where(function ($query) use ($lead) {
            $query->whereHas('quotation', fn($q) => $q->where('lead_id', $lead->id))
                  ->orWhereHas('installationJob.quotation', fn($q) => $q->where('lead_id', $lead->id));
        })->with('payments')->latest('invoice_date')->get();

        $unpaidInvoices = $invoices->filter(fn($inv) => in_array($inv->status, ['unpaid', 'partially_paid', 'overdue']));
        $totalDue = $unpaidInvoices->sum(fn($inv) => $inv->balanceDue());

        // Pending Quotations
        $pendingQuotations = $lead->quotations()->where('status', 'sent')->latest()->get();

        // Site Surveys & Feasibility Reports
        $siteSurveys = $lead->siteSurveys()->with(['surveyedBy', 'photos'])->latest('survey_date')->get();

        // Latest work / service request ticket for the customer
        $latestWorkTicket = $lead->serviceTickets()->latest()->first();

        // Check if admin has accepted customer request / activated site operations
        $isRequestAccepted = $totalEquipment > 0 
            || $activeAmc !== null 
            || in_array($lead->status, ['won', 'survey_scheduled', 'survey_completed', 'quotation_sent'])
            || $lead->serviceTickets()->whereIn('status', ['assigned', 'in_progress', 'resolved', 'closed'])->exists()
            || $lead->quotations()->whereIn('status', ['sent', 'accepted'])->exists()
            || $siteSurveys->isNotEmpty();

        // Company Completed Projects Portfolio
        $companyProjects = [
            [
                'title' => 'Prestige Tech Park – 128 Camera AI Smart Campus',
                'category' => 'Commercial IT Campus',
                'image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=600&q=80',
                'badge' => '128 AI Cameras',
                'tech' => 'Hikvision 4K AcuSense AI • 10G Fiber Backbone • ANPR Plate Recognition',
                'description' => 'End-to-end multi-building IP surveillance network with centralized command center and automated vehicle gate access.',
                'rating' => '5.0 ★★★★★',
            ],
            [
                'title' => 'Godrej Palm Villa Residency – 24-Unit 4K ColorVu Setup',
                'category' => 'Gated Luxury Residential',
                'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=600&q=80',
                'badge' => '24 ColorVu Cams',
                'tech' => '24/7 Full Color Night Vision • Two-Way Audio • iOS/Android Cloud Stream',
                'description' => 'Complete perimeter and driveway surveillance with vivid color recording in pitch dark and instant mobile intrusion alerts.',
                'rating' => '5.0 ★★★★★',
            ],
            [
                'title' => 'Apex Logistics & Freight Hub – Industrial Perimeter',
                'category' => 'Industrial Warehousing',
                'image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=600&q=80',
                'badge' => '64 Heavy-Duty Cams',
                'tech' => 'PTZ 360° 15x Zoom • Virtual Line Cross Alarms • 90-Day RAID 5 Storage',
                'description' => 'Heavy-duty IP67 weatherproof cameras monitoring loading bays, perimeter fences, and high-value cargo staging zones.',
                'rating' => '4.9 ★★★★★',
            ],
            [
                'title' => 'Metro Retail Mart – Loss Prevention & Footfall Analytics',
                'category' => 'Retail Superstore',
                'image' => 'https://images.unsplash.com/photo-1555421689-491a97ff2040?auto=format&fit=crop&w=600&q=80',
                'badge' => '36 Ceiling Domes',
                'tech' => '360° Fisheye Domes • Customer Heatmap • Cash Counter Macro Zoom',
                'description' => 'Smart retail analytics setup providing store management with customer traffic patterns, POS transaction zoom, and theft deterrent.',
                'rating' => '5.0 ★★★★★',
            ],
        ];

        // Cutting-Edge Technologies Used
        $technologies = [
            [
                'icon' => '🤖',
                'title' => 'AI Human & Vehicle Classification',
                'desc' => 'AcuSense deep learning algorithms filter out 95% of false alarms caused by rain, leaves, or pets, alerting you only to real security threats.',
                'badge' => 'Smart AI',
            ],
            [
                'icon' => '🌙',
                'title' => 'ColorVu 24/7 Full-Color Night Vision',
                'desc' => 'Advanced F1.0 super-aperture lenses capture vivid, true-color HD video in complete darkness without requiring bright external spotlights.',
                'badge' => 'Ultra Clarity',
            ],
            [
                'icon' => '🚗',
                'title' => 'ANPR Vehicle Number Plate Capture',
                'desc' => 'High-speed OCR optical recognition cameras automatically log license plates and trigger automated boom barrier gates.',
                'badge' => 'Gate Automation',
            ],
            [
                'icon' => '📱',
                'title' => 'Instant Mobile Cloud Streaming',
                'desc' => 'Encrypted live streaming and instant push notifications directly on your smartphone via official Hik-Connect & DMSS mobile apps.',
                'badge' => 'Remote View',
            ],
            [
                'icon' => '🔥',
                'title' => 'Thermal & Fire Anomaly Sensing',
                'desc' => 'Bi-spectrum thermal sensors detect overheating server racks, warehouse electrical fire risks, and long-range perimeter intrusion.',
                'badge' => 'Industrial Safety',
            ],
            [
                'icon' => '💾',
                'title' => 'RAID Failover & Cloud Retention',
                'desc' => 'Enterprise-grade NVR storage configurations ensuring 30 to 90 days of continuous recording with zero data loss.',
                'badge' => 'Data Protection',
            ],
        ];

        // Verified Customer Reviews & Star Ratings
        $customerReviews = [
            [
                'name' => 'Rajesh Sharma',
                'role' => 'Facility Operations Manager',
                'org' => 'Infotech Global Tower',
                'rating' => 5,
                'comment' => 'Exceptional CCTV installation! 64 IP cameras deployed in just 3 days with zero downtime to our office. The mobile app feeds and clarity are remarkable.',
                'verified' => 'Verified Enterprise Client',
            ],
            [
                'name' => 'Ananya Deshmukh',
                'role' => 'Resident Association Secretary',
                'org' => 'Green Valley Luxury Residency',
                'rating' => 5,
                'comment' => 'We transitioned our society AMC to CCTV CRM. Their quarterly maintenance visits, lens cleaning, and 2-hour breakdown turnaround are top notch!',
                'verified' => 'Verified Residential Client',
            ],
            [
                'name' => 'Vikram Malhotra',
                'role' => 'Logistics Director',
                'org' => 'Apex Cargo & Logistics Hub',
                'rating' => 5,
                'comment' => 'The ANPR license plate cameras automated our entire truck yard entrance. Engineering team was extremely punctual and skilled.',
                'verified' => 'Verified Commercial Client',
            ],
            [
                'name' => 'Dr. Suresh Kumar',
                'role' => 'Chief Administrator',
                'org' => 'Kaveri Multi-Specialty Hospital',
                'rating' => 5,
                'comment' => 'Superb 4K night vision clarity across our emergency entrance and parking areas. Digital quotation approval and online receipts made approval easy.',
                'verified' => 'Verified Healthcare Client',
            ],
        ];

        return view('customer.portal.dashboard', [
            'hasLead' => true,
            'lead' => $lead,
            'totalEquipment' => $totalEquipment,
            'activeWarrantyCount' => $activeWarrantyCount,
            'expiringSoonCount' => $expiringSoonCount,
            'expiredCount' => $expiredCount,
            'recentEquipment' => $equipment->take(6),
            'activeAmc' => $activeAmc,
            'nextVisit' => $nextVisit,
            'recentTickets' => $recentTickets,
            'openTicketsCount' => $openTicketsCount,
            'invoices' => $invoices->take(5),
            'unpaidInvoices' => $unpaidInvoices,
            'totalDue' => $totalDue,
            'pendingQuotations' => $pendingQuotations,
            'siteSurveys' => $siteSurveys,
            'isRequestAccepted' => $isRequestAccepted,
            'latestWorkTicket' => $latestWorkTicket,
            'companyProjects' => $companyProjects,
            'technologies' => $technologies,
            'customerReviews' => $customerReviews,
        ]);
    }

    /**
     * Customer Site Surveys & Feasibility Reports list.
     */
    public function surveys(): View
    {
        $lead = $this->getCustomerLead();
        if (!$lead) {
            return view('customer.portal.surveys.index', ['hasLead' => false, 'lead' => null, 'surveys' => collect()]);
        }

        $surveys = $lead->siteSurveys()->with(['surveyedBy', 'photos'])->latest('survey_date')->paginate(10);
        return view('customer.portal.surveys.index', ['hasLead' => true, 'lead' => $lead, 'surveys' => $surveys]);
    }

    /**
     * Customer Site Survey & Photo Inspection Report detail view.
     */
    public function showSurvey(SiteSurvey $siteSurvey): View
    {
        $lead = $this->getCustomerLead();
        if (!$lead || $siteSurvey->lead_id !== $lead->id) {
            abort(403, 'Unauthorized action.');
        }

        $siteSurvey->load(['surveyedBy', 'photos', 'lead']);

        return view('customer.portal.surveys.show', compact('siteSurvey', 'lead'));
    }

    /**
     * Customer Equipment & CCTV Cameras list.
     */
    public function equipment(Request $request): View
    {
        $lead = $this->getCustomerLead();
        if (!$lead) {
            $equipment = InstalledEquipment::whereRaw('1 = 0')->paginate(15);
            return view('customer.portal.equipment', ['hasLead' => false, 'lead' => null, 'equipment' => $equipment]);
        }

        $query = $lead->installedEquipment()->with('product');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('equipment_name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('mac_address', 'like', "%{$search}%")
                  ->orWhere('location_tag', 'like', "%{$search}%");
            });
        }

        $equipment = $query->latest('installation_date')->paginate(15)->withQueryString();

        return view('customer.portal.equipment', [
            'hasLead' => true,
            'lead' => $lead,
            'equipment' => $equipment,
        ]);
    }

    /**
     * Customer AMC & Maintenance tracking.
     */
    public function amc(): View
    {
        $lead = $this->getCustomerLead();
        if (!$lead) {
            return view('customer.portal.amc', ['hasLead' => false, 'lead' => null, 'contracts' => collect(), 'activeAmc' => null]);
        }

        $contracts = $lead->amcContracts()
            ->with(['visits.assignedTechnician'])
            ->latest('start_date')
            ->get();

        $activeAmc = $contracts->firstWhere('status', 'active');

        return view('customer.portal.amc', [
            'hasLead' => true,
            'lead' => $lead,
            'contracts' => $contracts,
            'activeAmc' => $activeAmc,
        ]);
    }

    /**
     * Customer request AMC renewal.
     */
    public function requestAmcRenewal(Request $request): RedirectResponse
    {
        $lead = $this->getCustomerLead();
        if (!$lead) {
            return back()->with('error', 'Customer profile is not linked.');
        }

        $yearMonth = date('Ym');
        $lastTicket = ServiceTicket::where('ticket_no', 'like', "TCK-{$yearMonth}-%")->latest('id')->first();
        $seq = 1;
        if ($lastTicket) {
            $parts = explode('-', $lastTicket->ticket_no);
            $seq = isset($parts[2]) ? ((int) $parts[2]) + 1 : 1;
        }
        $ticketNo = sprintf('TCK-%s-%04d', $yearMonth, $seq);

        $activeAmc = $lead->activeAmcContract()->first();

        ServiceTicket::create([
            'ticket_no' => $ticketNo,
            'lead_id' => $lead->id,
            'amc_contract_id' => $activeAmc?->id,
            'created_by_id' => Auth::id(),
            'title' => 'AMC Contract Renewal Request',
            'issue_type' => 'other',
            'priority' => 'medium',
            'status' => 'open',
            'description' => 'Customer requested an AMC renewal quote via the Customer Self-Service Portal. ' . ($request->input('notes') ?? ''),
            'billing_type' => 'billable',
        ]);

        return back()->with('status', 'Your AMC renewal request has been received! Our support team will reach out shortly with a renewal quote.');
    }

    /**
     * Customer Service & Breakdown Tickets.
     */
    public function tickets(Request $request): View
    {
        $lead = $this->getCustomerLead();
        $statusFilter = $request->query('status', 'all');

        if (!$lead) {
            $tickets = ServiceTicket::whereRaw('1 = 0')->paginate(10);
            return view('customer.portal.tickets.index', [
                'hasLead' => false,
                'lead' => null,
                'tickets' => $tickets,
                'statusFilter' => $statusFilter,
            ]);
        }

        $query = $lead->serviceTickets()->with(['assignedTechnician']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tickets = $query->latest()->paginate(10)->withQueryString();

        return view('customer.portal.tickets.index', [
            'hasLead' => true,
            'lead' => $lead,
            'tickets' => $tickets,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Show form to raise a new service ticket.
     */
    public function createTicket(Request $request): View
    {
        $lead = $this->getCustomerLead();
        if (!$lead) {
            abort(403, 'Customer account is not linked to a site profile.');
        }

        $equipmentList = $lead->installedEquipment()->get(['id', 'equipment_name', 'location_tag', 'serial_number']);
        $preselectedEquipmentId = $request->query('equipment_id');

        return view('customer.portal.tickets.create', [
            'lead' => $lead,
            'equipmentList' => $equipmentList,
            'preselectedEquipmentId' => $preselectedEquipmentId,
        ]);
    }

    /**
     * Store a newly created service ticket from the customer.
     */
    public function storeTicket(Request $request): RedirectResponse
    {
        $lead = $this->getCustomerLead();
        if (!$lead) {
            abort(403, 'Customer account is not linked to a site profile.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'issue_type' => ['required', Rule::in(array_keys(ServiceTicket::issueTypeOptions()))],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'equipment_id' => ['nullable', 'exists:installed_equipment,id'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $yearMonth = date('Ym');
        $lastTicket = ServiceTicket::where('ticket_no', 'like', "TCK-{$yearMonth}-%")->latest('id')->first();
        $seq = 1;
        if ($lastTicket) {
            $parts = explode('-', $lastTicket->ticket_no);
            $seq = isset($parts[2]) ? ((int) $parts[2]) + 1 : 1;
        }
        $ticketNo = sprintf('TCK-%s-%04d', $yearMonth, $seq);

        $activeAmc = $lead->activeAmcContract()->first();
        $billingType = $activeAmc ? 'warranty_amc' : 'billable';

        // Prepend equipment information to description if selected
        $fullDescription = $validated['description'];
        if (!empty($validated['equipment_id'])) {
            $eq = InstalledEquipment::find($validated['equipment_id']);
            if ($eq) {
                $fullDescription = "[Affected Equipment: {$eq->equipment_name} (S/N: {$eq->serial_number}, Loc: {$eq->location_tag})]\n\n" . $fullDescription;
            }
        }

        $ticket = ServiceTicket::create([
            'ticket_no' => $ticketNo,
            'lead_id' => $lead->id,
            'amc_contract_id' => $activeAmc?->id,
            'created_by_id' => Auth::id(),
            'title' => $validated['title'],
            'issue_type' => $validated['issue_type'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'description' => $fullDescription,
            'billing_type' => $billingType,
        ]);

        return redirect()->route('portal.tickets.show', $ticket)
            ->with('status', "Service Ticket #{$ticket->ticket_no} submitted successfully. Our engineers have been alerted!");
    }

    /**
     * Show ticket details and live status to the customer.
     */
    public function showTicket(ServiceTicket $ticket): View
    {
        $lead = $this->getCustomerLead();
        if (!$lead || $ticket->lead_id !== $lead->id) {
            abort(403, 'Unauthorized access to this service ticket.');
        }

        $ticket->load(['lead', 'amcContract', 'assignedTechnician']);

        return view('customer.portal.tickets.show', [
            'ticket' => $ticket,
            'lead' => $lead,
        ]);
    }

    /**
     * Invoices and payment history.
     */
    public function invoices(): View
    {
        $lead = $this->getCustomerLead();
        if (!$lead) {
            $invoices = Invoice::whereRaw('1 = 0')->paginate(10);
            return view('customer.portal.invoices', ['hasLead' => false, 'lead' => null, 'invoices' => $invoices]);
        }

        $invoices = Invoice::where(function ($query) use ($lead) {
            $query->whereHas('quotation', fn($q) => $q->where('lead_id', $lead->id))
                  ->orWhereHas('installationJob.quotation', fn($q) => $q->where('lead_id', $lead->id));
        })->with(['payments', 'quotation', 'installationJob'])->latest('invoice_date')->paginate(10);

        $pendingQuotations = $lead->quotations()
            ->whereIn('status', ['draft', 'sent', 'accepted'])
            ->with(['items'])
            ->latest()
            ->get();

        return view('customer.portal.invoices', [
            'hasLead' => true,
            'lead' => $lead,
            'invoices' => $invoices,
            'pendingQuotations' => $pendingQuotations,
        ]);
    }

    /**
     * Quotations and Estimates review.
     */
    public function quotations(): View
    {
        $lead = $this->getCustomerLead();
        if (!$lead) {
            $quotations = Quotation::whereRaw('1 = 0')->paginate(10);
            return view('customer.portal.quotations', ['hasLead' => false, 'lead' => null, 'quotations' => $quotations]);
        }

        $quotations = $lead->quotations()->with('items')->latest()->paginate(10);

        return view('customer.portal.quotations', [
            'hasLead' => true,
            'lead' => $lead,
            'quotations' => $quotations,
        ]);
    }

    /**
     * Accept a quotation directly from customer portal.
     */
    public function acceptQuotation(Quotation $quotation): RedirectResponse
    {
        $lead = $this->getCustomerLead();
        if (!$lead || $quotation->lead_id !== $lead->id) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array($quotation->status, ['draft', 'sent'])) {
            return back()->with('error', 'This quotation has already been processed.');
        }

        $oldStatus = $quotation->status;

        DB::transaction(function () use ($quotation, $oldStatus) {
            $quotation->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            $quotation->lead->update([
                'status' => 'won',
            ]);

            $quotation->statusHistories()->create([
                'changed_by' => Auth::id(),
                'from_status' => $oldStatus,
                'to_status' => 'accepted',
                'notes' => 'Customer approved and accepted quotation online via Customer Portal.',
            ]);
        });

        return back()->with('status', "Quotation #{$quotation->quotation_no} accepted successfully! We will initiate your project workflow.");
    }

    /**
     * Decline a quotation with reason.
     */
    public function rejectQuotation(Request $request, Quotation $quotation): RedirectResponse
    {
        $lead = $this->getCustomerLead();
        if (!$lead || $quotation->lead_id !== $lead->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!in_array($quotation->status, ['draft', 'sent'])) {
            return back()->with('error', 'This quotation has already been processed.');
        }

        $oldStatus = $quotation->status;

        DB::transaction(function () use ($quotation, $oldStatus, $validated) {
            $quotation->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => $validated['rejection_reason'] ?? 'Declined by customer via online portal.',
            ]);

            $quotation->statusHistories()->create([
                'changed_by' => Auth::id(),
                'from_status' => $oldStatus,
                'to_status' => 'rejected',
                'notes' => 'Customer declined quotation: ' . ($validated['rejection_reason'] ?? 'No reason specified.'),
            ]);
        });

        return back()->with('status', "Quotation #{$quotation->quotation_no} declined. Thank you for your feedback.");
    }

    /**
     * Handle Customer Site Setup & Work / Support Request submission.
     */
    public function submitSiteWorkRequest(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $lead = $this->getCustomerLead();

        $validated = $request->validate([
            'customer_name'  => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255'],
            'phone'          => ['required', 'string', 'max:30'],
            'site_address'   => ['required', 'string', 'max:1000'],
            'work_type'      => ['required', 'string', 'max:100'],
            'description'    => ['required', 'string', 'max:3000'],
            'preferred_date' => ['nullable', 'date'],
        ]);

        // 1. Update User and Lead Profile
        $user->update([
            'name' => $validated['customer_name'],
        ]);

        if ($lead) {
            $lead->update([
                'customer_name' => $validated['customer_name'],
                'email'         => $validated['email'],
                'phone'         => $validated['phone'],
                'site_address'  => $validated['site_address'],
            ]);
        }

        // 2. Generate Ticket Number TCK-YYYYMM-XXXX
        $yearMonth = date('Ym');
        $lastTicket = ServiceTicket::where('ticket_no', 'like', "TCK-{$yearMonth}-%")->latest('id')->first();
        $seq = 1;
        if ($lastTicket) {
            $parts = explode('-', $lastTicket->ticket_no);
            $seq = isset($parts[2]) ? ((int) $parts[2]) + 1 : 1;
        }
        $ticketNo = sprintf('TCK-%s-%04d', $yearMonth, $seq);

        $activeAmc = $lead ? $lead->activeAmcContract()->first() : null;
        $billingType = $activeAmc ? 'warranty_amc' : 'billable';

        // 3. Classify issue type based on work_type
        $issueType = match ($validated['work_type']) {
            'cctv_installation' => 'other',
            'maintenance_amc'   => 'other',
            'camera_offline'    => 'camera_offline',
            'recording_issue'   => 'recording_failure',
            'power_issue'       => 'power_supply_issue',
            'network_issue'     => 'network_issue',
            'cable_wiring'      => 'cable_damaged',
            default             => 'other',
        };

        $workTitle = match ($validated['work_type']) {
            'cctv_installation' => 'New CCTV Installation & Site Survey Request',
            'maintenance_amc'   => 'CCTV Maintenance & AMC Service Request',
            'camera_offline'    => 'Camera Offline / Signal Breakdown Request',
            'recording_issue'   => 'NVR / Storage Recording Issue',
            'power_issue'       => 'Power Supply / PoE Issue',
            'network_issue'     => 'Remote Mobile View Network Setup',
            'cable_wiring'      => 'Cable Damage / Rewiring Support',
            default             => 'Customer Work & Service Request',
        };

        $fullDescription = "--- Customer Work & Support Request ---\n"
            . "Requested By: " . $validated['customer_name'] . " (" . $validated['email'] . ")\n"
            . "Contact Number: " . $validated['phone'] . "\n"
            . "Installation Address: " . $validated['site_address'] . "\n"
            . "Work Category: " . $workTitle . "\n\n"
            . "Work Scope & Requirements:\n" . $validated['description'];

        // 4. Create Service Ticket
        $ticket = ServiceTicket::create([
            'ticket_no'       => $ticketNo,
            'lead_id'         => $lead->id,
            'amc_contract_id' => $activeAmc?->id,
            'created_by_id'   => $user->id,
            'title'           => $workTitle,
            'issue_type'      => $issueType,
            'priority'        => 'high',
            'status'          => 'open',
            'description'     => $fullDescription,
            'billing_type'    => $billingType,
        ]);

        // 5. If CCTV installation or Free Site Survey is requested, auto-create Site Survey record
        if ($lead && ($validated['work_type'] === 'cctv_installation' || $request->filled('preferred_date'))) {
            $surveyDate = $request->input('preferred_date') ?: Carbon::tomorrow()->toDateString();
            $adminUser = \App\Models\User::where('role', 'admin')->first() ?? $user;

            SiteSurvey::create([
                'lead_id'        => $lead->id,
                'surveyed_by'    => $adminUser->id,
                'survey_date'    => $surveyDate,
                'site_address'   => $validated['site_address'],
                'contact_person' => $validated['customer_name'],
                'contact_phone'  => $validated['phone'],
                'visit_notes'    => $validated['description'],
                'status'         => 'pending',
            ]);

            if ($lead->status === 'new') {
                $lead->update(['status' => 'contacted']);
            }
        }

        // 6. If submitted via Instant Calculator ("SUBMIT AS CUSTOM QUOTATION REQUEST"), create formal Quotation with BOM Line Items
        $quotation = null;
        if ($lead && ($request->filled('is_calculator_quote') || str_contains($validated['description'], 'Customer Customized CCTV Package Estimate'))) {
            $camRates = [
                '2mp'         => ['name' => '2MP 1080p HD IR', 'rate' => 1650, 'res' => '1080p HD'],
                '4mp'         => ['name' => '4MP QHD Crystal', 'rate' => 2850, 'res' => '2K QHD'],
                '5mp_colorvu' => ['name' => '5MP ColorVu 24/7', 'rate' => 3950, 'res' => '3K Super HD'],
                '4k_ai'       => ['name' => '4K AI Smart Tracking', 'rate' => 6800, 'res' => '4K Ultra HD'],
            ];

            $camCount = max(1, (int) ($request->input('camera_count') ?: 8));
            $techKey = $request->input('tech_type', '5mp_colorvu');
            $tech = $camRates[$techKey] ?? $camRates['5mp_colorvu'];
            $storageDays = (int) ($request->input('storage_days') ?: 30);
            $wiringType = $request->input('wiring_type', 'pvc');

            // 1. Camera units
            $camLineTotal = $camCount * $tech['rate'];

            // 2. NVR
            if ($camCount <= 4) { $nvrName = '4-Channel 4K NVR'; $nvrCost = 4500; }
            elseif ($camCount <= 8) { $nvrName = '8-Channel 4K NVR'; $nvrCost = 7500; }
            elseif ($camCount <= 16) { $nvrName = '16-Channel 4K NVR'; $nvrCost = 14000; }
            elseif ($camCount <= 32) { $nvrName = '32-Channel Enterprise NVR'; $nvrCost = 24000; }
            else { $nvrName = '64-Channel Server NVR'; $nvrCost = 48000; }

            // 3. HDD
            if ($storageDays === 15) {
                $tb = $camCount <= 8 ? 1 : ($camCount <= 16 ? 2 : ($camCount <= 32 ? 4 : 8));
            } elseif ($storageDays === 30) {
                $tb = $camCount <= 4 ? 1 : ($camCount <= 8 ? 2 : ($camCount <= 16 ? 4 : ($camCount <= 32 ? 8 : 16)));
            } else {
                $tb = $camCount <= 4 ? 2 : ($camCount <= 8 ? 4 : ($camCount <= 16 ? 8 : ($camCount <= 32 ? 16 : 24)));
            }
            $hddCost = $tb * 3200;
            $hddName = "{$tb}TB Surveillance HDD (24/7 WD Purple)";

            // 4. PoE
            $poePorts = $camCount <= 4 ? 4 : ($camCount <= 8 ? 8 : ($camCount <= 16 ? 16 : ($camCount <= 32 ? 24 : 48)));
            $poeCost = $camCount <= 4 ? 1800 : ($camCount <= 8 ? 3200 : ($camCount <= 16 ? 6500 : ($camCount <= 32 ? 12000 : 22000)));
            $poeName = "{$poePorts}-Port Gigabit PoE Network Switch";

            // 5. Cabling
            $perPoint = $wiringType === 'pvc' ? 750 : 1350;
            $cablingCost = $camCount * $perPoint;
            $cablingName = $wiringType === 'pvc'
                ? "Standard Heavy-Duty PVC Conduit + CAT6 Cable Run ({$camCount} Points)"
                : "Industrial Metal GI Casing + Armored CAT6 Run ({$camCount} Points)";

            // 6. Labor
            $laborCost = $camCount * 450;

            // Totals
            $subtotal = $camLineTotal + $nvrCost + $hddCost + $poeCost + $cablingCost + $laborCost;
            $taxAmount = round($subtotal * 0.18, 2);
            $grandTotal = $subtotal + $taxAmount;

            $quotationNo = 'QT-' . now()->format('Ymd') . '-' . str_pad((string) (Quotation::count() + 1), 4, '0', STR_PAD_LEFT);

            $quotation = Quotation::create([
                'lead_id'        => $lead->id,
                'quotation_no'   => $quotationNo,
                'quotation_date' => now()->toDateString(),
                'valid_until'    => now()->addDays(15)->toDateString(),
                'subtotal'       => $subtotal,
                'discount'       => 0,
                'tax_percent'    => 18,
                'tax_amount'     => $taxAmount,
                'total'          => $grandTotal,
                'status'         => 'sent',
                'sent_at'        => now(),
                'notes'          => "Custom turnkey package generated from Customer Instant Pricing Calculator.",
            ]);

            // Create Quotation line items
            $quotation->items()->createMany([
                [
                    'item_name'   => "{$camCount}x {$tech['name']}",
                    'description' => "{$tech['res']} Optics with night vision and smart monitoring",
                    'quantity'    => $camCount,
                    'unit'        => 'Nos',
                    'unit_price'  => $tech['rate'],
                    'total'       => $camLineTotal,
                ],
                [
                    'item_name'   => $nvrName,
                    'description' => "Network Video Recorder with 4K HDMI Output and remote viewing support",
                    'quantity'    => 1,
                    'unit'        => 'Nos',
                    'unit_price'  => $nvrCost,
                    'total'       => $nvrCost,
                ],
                [
                    'item_name'   => $hddName,
                    'description' => "Dedicated 24/7 surveillance hard disk for {$storageDays} days retention",
                    'quantity'    => 1,
                    'unit'        => 'Nos',
                    'unit_price'  => $hddCost,
                    'total'       => $hddCost,
                ],
                [
                    'item_name'   => $poeName,
                    'description' => "Gigabit Power-over-Ethernet switch for camera network and data transmission",
                    'quantity'    => 1,
                    'unit'        => 'Nos',
                    'unit_price'  => $poeCost,
                    'total'       => $poeCost,
                ],
                [
                    'item_name'   => $cablingName,
                    'description' => "Pure copper CAT6 network cabling with certified conduit casing",
                    'quantity'    => $camCount,
                    'unit'        => 'Points',
                    'unit_price'  => $perPoint,
                    'total'       => $cablingCost,
                ],
                [
                    'item_name'   => 'Professional Field Installation & Commissioning',
                    'description' => 'Certified CCTV technician setup, angle tuning, NVR programming & mobile app onboarding',
                    'quantity'    => $camCount,
                    'unit'        => 'Points',
                    'unit_price'  => 450,
                    'total'       => $laborCost,
                ],
                [
                    'item_name'   => '1-Year Comprehensive Warranty & AMC Support',
                    'description' => 'Complimentary 1-year equipment maintenance coverage and priority support',
                    'quantity'    => 1,
                    'unit'        => 'Year',
                    'unit_price'  => 0,
                    'total'       => 0,
                ],
            ]);

            $quotation->statusHistories()->create([
                'changed_by'  => $user->id,
                'from_status' => null,
                'to_status'   => 'sent',
                'notes'       => 'Custom quotation requested by customer via Instant Estimate Calculator.',
            ]);

            $lead->update(['status' => 'quoted']);
        }

        $successMsg = $quotation
            ? "Custom Quotation #{$quotation->quotation_no} (₹" . number_format($quotation->total, 2) . ") created successfully! Our sales desk and engineers have been dispatched."
            : "Work & Support Request logged successfully as Ticket #{$ticket->ticket_no}! Our engineering team will contact you at {$validated['phone']}.";

        return redirect()->route('portal.dashboard')->with('status', $successMsg);
    }
}

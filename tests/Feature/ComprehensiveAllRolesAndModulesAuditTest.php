<?php

namespace Tests\Feature;

use App\Models\AmcContract;
use App\Models\AmcVisit;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalary;
use App\Models\InstallationJob;
use App\Models\InstalledEquipment;
use App\Models\Invoice;
use App\Models\JobCompletionReport;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\RmaClaim;
use App\Models\ServiceTicket;
use App\Models\SiteSurvey;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ComprehensiveAllRolesAndModulesAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $technician;
    private User $customerUser;
    private Lead $lead;
    private Product $product;
    private Quotation $quotation;
    private InstallationJob $job;
    private Invoice $invoice;
    private Payment $payment;
    private AmcContract $amc;
    private AmcVisit $visit;
    private SiteSurvey $survey;
    private ServiceTicket $ticket;
    private InstalledEquipment $equipment;
    private Supplier $supplier;
    private PurchaseOrder $purchaseOrder;
    private RmaClaim $rmaClaim;
    private JobCompletionReport $jcr;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Core Users Setup
        $this->admin = User::factory()->create([
            'name' => 'Admin Controller',
            'email' => 'admin@cctvops.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->staff = User::factory()->create([
            'name' => 'kesavan',
            'email' => 'kesavan@gmail.com',
            'password' => Hash::make('kesavan123'),
            'role' => 'staff',
        ]);

        $this->technician = User::factory()->create([
            'name' => 'Bob Miller',
            'email' => 'bob@cctvops.com',
            'password' => Hash::make('password123'),
            'role' => 'technician',
        ]);

        // 2. Primary CRM Entities
        $this->lead = Lead::create([
            'customer_name' => 'Metro Cyber Park',
            'email' => 'facilities@metrocyber.com',
            'phone' => '8789076658',
            'site_address' => 'Tower A, Ring Road, Bangalore',
            'status' => 'won',
            'source' => 'website',
            'notes' => '16-Channel AI CCTV System with ANPR and Facial Recognition.',
        ]);

        $this->customerUser = User::factory()->create([
            'name' => 'Srinithish P',
            'email' => 'facilities@metrocyber.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'lead_id' => $this->lead->id,
        ]);

        $this->product = Product::create([
            'name' => 'Hikvision 4K AcuSense Bullet Camera',
            'category' => 'Camera',
            'brand' => 'Hikvision',
            'model_number' => 'DS-2CD2T87G2-LSU/SL',
            'unit_price' => 8500,
            'cost_price' => 6200,
            'stock_quantity' => 40,
            'minimum_stock' => 10,
        ]);

        $this->quotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'quotation_no' => 'QT-2026-AUDIT01',
            'subtotal' => 68000,
            'tax_percent' => 18,
            'tax_amount' => 12240,
            'total' => 80240,
            'status' => 'accepted',
            'notes' => 'Includes 8x 4K Cameras, 16CH NVR, 4TB HDD, Installation.',
        ]);

        QuotationItem::create([
            'quotation_id' => $this->quotation->id,
            'product_id' => $this->product->id,
            'item_name' => 'Hikvision 4K AcuSense Bullet Camera',
            'quantity' => 8,
            'unit_price' => 8500,
        ]);

        $this->job = InstallationJob::create([
            'quotation_id' => $this->quotation->id,
            'job_no' => 'JOB-2026-AUDIT01',
            'assigned_technician_id' => $this->technician->id,
            'status' => 'completed',
            'scheduled_date' => now()->toDateString(),
            'installation_notes' => 'All cameras deployed and network configured.',
        ]);

        $this->invoice = Invoice::create([
            'installation_job_id' => $this->job->id,
            'quotation_id' => $this->quotation->id,
            'invoice_no' => 'INV-2026-AUDIT01',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal' => 68000,
            'tax_percent' => 18,
            'tax_amount' => 12240,
            'total' => 80240,
            'amount_paid' => 80240,
            'status' => 'paid',
        ]);

        $this->payment = Payment::create([
            'invoice_id' => $this->invoice->id,
            'reference_no' => 'PAY-2026-AUDIT01',
            'amount' => 80240,
            'paid_on' => now()->toDateString(),
            'method' => 'bank_transfer',
            'recorded_by' => $this->admin->id,
        ]);

        $this->amc = AmcContract::create([
            'lead_id' => $this->lead->id,
            'contract_no' => 'AMC-2026-AUDIT01',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'frequency' => 'quarterly',
            'value' => 24000,
            'status' => 'active',
            'notes' => 'Comprehensive 24/7 breakdown support.',
        ]);

        $this->visit = AmcVisit::create([
            'amc_contract_id' => $this->amc->id,
            'assigned_technician_id' => $this->technician->id,
            'scheduled_date' => now()->addMonth()->toDateString(),
            'status' => 'pending',
            'completion_notes' => 'Q1 Scheduled Preventive Maintenance',
        ]);

        $this->survey = SiteSurvey::create([
            'lead_id' => $this->lead->id,
            'surveyed_by' => $this->technician->id,
            'survey_date' => now()->toDateString(),
            'site_address' => 'Tower A, Ring Road, Bangalore',
            'contact_person' => 'Metro Cyber Facilities',
            'contact_phone' => '8789076658',
            'camera_count_recommended' => 16,
            'status' => 'completed',
        ]);

        $this->ticket = ServiceTicket::create([
            'ticket_no' => 'TKT-2026-AUDIT01',
            'lead_id' => $this->lead->id,
            'amc_contract_id' => $this->amc->id,
            'assigned_technician_id' => $this->technician->id,
            'created_by_id' => $this->customerUser->id,
            'title' => 'Main Gate ANPR Camera Calibration',
            'issue_type' => 'blurry_feed',
            'priority' => 'high',
            'status' => 'in_progress',
            'billing_type' => 'warranty_amc',
            'description' => 'Needs refocusing and night vision tuning.',
        ]);

        $this->equipment = InstalledEquipment::create([
            'lead_id' => $this->lead->id,
            'installation_job_id' => $this->job->id,
            'product_id' => $this->product->id,
            'equipment_name' => 'Hikvision 4K AcuSense Bullet Camera',
            'serial_number' => 'HKV-4K-AUDIT-9901',
            'mac_address' => 'BC:92:68:5A:99:01',
            'location_tag' => 'North Gate Entry',
            'installation_date' => now()->toDateString(),
            'manufacturer_warranty_expiry' => now()->addYears(3)->toDateString(),
            'service_warranty_expiry' => now()->addYear()->toDateString(),
            'status' => 'active',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Hikvision Direct Distributor',
            'contact_person' => 'Vikas Malhotra',
            'phone' => '9888877777',
            'email' => 'vikas@hikdistributor.com',
            'city' => 'Bangalore',
            'status' => 'active',
        ]);

        $this->purchaseOrder = PurchaseOrder::create([
            'po_number' => 'PO-2026-AUDIT01',
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'subtotal' => 62000,
            'tax_percent' => 18,
            'tax_amount' => 11160,
            'total' => 73160,
            'status' => 'ordered',
            'payment_status' => 'unpaid',
            'created_by' => $this->admin->id,
        ]);

        $this->rmaClaim = RmaClaim::create([
            'rma_no' => 'RMA-2026-AUDIT01',
            'installed_equipment_id' => $this->equipment->id,
            'lead_id' => $this->lead->id,
            'supplier_id' => $this->supplier->id,
            'faulty_serial_number' => 'HKV-4K-AUDIT-9901',
            'issue_description' => 'Sensor white balance drift in low light',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        $this->jcr = JobCompletionReport::create([
            'report_no' => 'JCR-2026-AUDIT01',
            'lead_id' => $this->lead->id,
            'installation_job_id' => $this->job->id,
            'technician_id' => $this->technician->id,
            'completion_date' => now()->toDateString(),
            'signer_name' => 'Metro Cyber Facilities',
            'signer_phone' => '8789076658',
            'customer_rating' => 5,
            'customer_feedback' => 'Flawless camera installation and clear mobile view.',
            'customer_signature' => 'data:image/svg+xml;utf8,<svg><text>Metro</text></svg>',
            'technician_signature' => 'data:image/svg+xml;utf8,<svg><text>Bob</text></svg>',
            'work_summary' => 'Mounted 8 bullet cameras, configured 4K NVR and mobile sync.',
            'status' => 'signed',
        ]);

        // 3. Employee Salary & Attendance records
        EmployeeSalary::updateOrCreate(
            ['user_id' => $this->staff->id],
            [
                'base_salary_monthly' => 30000,
                'daily_rate' => 1153.85,
                'payment_method' => 'bank_transfer',
                'notes' => 'Phone: 8789076658',
            ]
        );
    }

    /**
     * Audit 1A: Admin Leads, Quotations & Estimator Workflows.
     */
    public function test_admin_leads_quotations_and_estimator(): void
    {
        $this->actingAs($this->admin);

        // 1. Dashboard & Live API
        $this->get(route('dashboard'))->assertOk()->assertSee('Dashboard');
        $this->get(route('dashboard.data'))->assertOk()->assertJsonStructure(['stats', 'leadChart', 'revenueChart']);

        // 2. Calendar
        $this->get(route('calendar.index'))->assertOk();
        $this->get(route('calendar.events'))->assertOk();

        // 3. Leads CRUD & Status
        $this->get(route('leads.index'))->assertOk()->assertSee('Metro Cyber Park');
        $this->get(route('leads.create'))->assertOk();
        $this->get(route('leads.show', $this->lead))->assertOk();
        $this->get(route('leads.edit', $this->lead))->assertOk();
        $this->post(route('leads.status', $this->lead), ['status' => 'contacted'])->assertRedirect();
        $this->get(route('leads.vcard', $this->lead))->assertOk();

        // 4. Quotations Workflow
        $this->quotation->update(['status' => 'draft']);
        $this->get(route('quotations.index'))->assertOk()->assertSee('QT-2026-AUDIT01');
        $this->get(route('quotations.create-general'))->assertOk();
        $this->get(route('quotations.create', $this->lead))->assertOk();
        $this->get(route('quotations.show', $this->quotation))->assertOk();
        $this->get(route('quotations.edit', $this->quotation))->assertOk();
        $this->patch(route('quotations.markSent', $this->quotation))->assertRedirect();
        $this->post(route('quotations.accept', $this->quotation))->assertRedirect();
        $this->get(route('quotations.pdf', ['quotation' => $this->quotation, 'format' => 'compact']))->assertOk();
        $this->getJson(route('quotations.send-whatsapp', $this->quotation))->assertOk()->assertJsonStructure(['success', 'whatsapp_url']);

        // 5. Estimator & Conversion
        $this->get(route('estimator.index'))->assertOk();
        $estimatorResponse = $this->post(route('estimator.convert'), [
            'lead_id' => $this->lead->id,
            'camera_count' => 4,
            'recording_days' => 30,
            'resolution' => '4mp',
            'cable_length_meters' => 120,
            'storage_tb' => 2,
            'estimated_total' => 35000,
        ]);
        $estimatorResponse->assertRedirect();
    }

    /**
     * Audit 1B: Admin Jobs, Products, Inventory & Equipment.
     */
    public function test_admin_jobs_products_inventory_and_equipment(): void
    {
        $this->actingAs($this->admin);

        // 6. Installation Jobs
        $this->get(route('jobs.index'))->assertOk()->assertSee('JOB-2026-AUDIT01');
        $this->get(route('jobs.show', $this->job))->assertOk();
        $this->patch(route('jobs.update', $this->job), [
            'status' => 'completed',
            'installation_notes' => 'Testing audit updated notes.',
        ])->assertRedirect();

        // 7. Products Catalog
        $this->get(route('products.index'))->assertOk()->assertSee('Hikvision 4K AcuSense');
        $this->get(route('products.create'))->assertOk();
        $this->get(route('products.edit', $this->product))->assertOk();
        $this->get(route('products.search', ['query' => 'Hikvision']))->assertOk();

        // 8. Inventory & Stock Adjustments
        $this->get(route('inventory.index'))->assertOk();
        $this->get(route('inventory.movements'))->assertOk();
        $this->post(route('inventory.adjust', $this->product), [
            'type' => 'inward',
            'quantity' => 10,
            'reason' => 'Audit Stock replenishment',
        ])->assertRedirect();

        // 9. Installed Equipment Registry
        $this->get(route('equipment.index'))->assertOk()->assertSee('HKV-4K-AUDIT-9901');
        $this->get(route('equipment.show', $this->equipment))->assertOk();
        $this->get(route('equipment.edit', $this->equipment))->assertOk();
        $this->get(route('api.equipment.lookup-serial', ['serial' => 'HKV-4K-AUDIT-9901']))->assertOk();
    }

    /**
     * Audit 1C: Admin RMA, Suppliers, Purchase Orders & Invoices.
     */
    public function test_admin_rma_suppliers_purchase_orders_and_invoices(): void
    {
        $this->actingAs($this->admin);

        // 10. RMA & Claims
        $this->get(route('rma.index'))->assertOk()->assertSee('RMA-2026-AUDIT01');
        $this->get(route('rma.create'))->assertOk();
        $this->get(route('rma.show', $this->rmaClaim))->assertOk();
        $this->get(route('rma.edit', $this->rmaClaim))->assertOk();
        $this->get(route('rma.dispatch-pdf', $this->rmaClaim))->assertOk();

        // 11. Suppliers & POs
        $this->get(route('suppliers.index'))->assertOk()->assertSee('Hikvision Direct');
        $this->get(route('suppliers.create'))->assertOk();
        $this->get(route('suppliers.show', $this->supplier))->assertOk();
        $this->get(route('suppliers.edit', $this->supplier))->assertOk();
        $this->get(route('purchase-orders.index'))->assertOk()->assertSee('PO-2026-AUDIT01');
        $this->get(route('purchase-orders.create'))->assertOk();
        $this->get(route('purchase-orders.show', $this->purchaseOrder))->assertOk();
        $this->get(route('purchase-orders.edit', $this->purchaseOrder))->assertRedirect(route('purchase-orders.show', $this->purchaseOrder));

        // 12. Invoices & Payments
        $this->get(route('invoices.index'))->assertOk()->assertSee('INV-2026-AUDIT01');
        $this->get(route('invoices.show', $this->invoice))->assertOk();
        $this->get(route('payments.receipt.pdf', $this->payment))->assertOk();
    }

    /**
     * Audit 1D: Admin AMC, Surveys, Service Tickets & JCR.
     */
    public function test_admin_amc_surveys_service_tickets_and_jcr(): void
    {
        $this->actingAs($this->admin);

        // 13. AMC Contracts & Visits
        $this->get(route('amcs.index'))->assertOk()->assertSee('AMC-2026-AUDIT01');
        $this->get(route('amcs.create'))->assertOk();
        $this->get(route('amcs.show', $this->amc))->assertOk();
        $this->patch(route('amcs.updateStatus', $this->amc), ['status' => 'active'])->assertRedirect();
        $this->post(route('amc-visits.assign', $this->visit), [
            'assigned_technician_id' => $this->technician->id,
            'scheduled_date' => now()->addMonth()->toDateString(),
        ])->assertRedirect();

        // 14. Site Surveys
        $this->get(route('site-surveys.index'))->assertOk();
        $this->get(route('site-surveys.create'))->assertOk();
        $this->get(route('site-surveys.show', $this->survey))->assertOk();
        $this->get(route('site-surveys.edit', $this->survey))->assertOk();

        // 15. Service Tickets
        $this->get(route('service-tickets.index'))->assertOk()->assertSee('TKT-2026-AUDIT01');
        $this->get(route('service-tickets.create'))->assertOk();
        $this->get(route('service-tickets.show', $this->ticket))->assertOk();
        $this->get(route('service-tickets.edit', $this->ticket))->assertOk();
        $this->get(route('service-tickets.export'))->assertOk();
        $this->get(route('service-tickets.export-pdf'))->assertOk();

        // 16. Job Completion Reports (JCR)
        $this->get(route('jcr.index'))->assertOk()->assertSee('JCR-2026-AUDIT01');
        $this->get(route('jcr.show', $this->jcr))->assertOk();
        $this->get(route('jcr.download-pdf', $this->jcr))->assertOk();
    }

    /**
     * Audit 1E: Admin Alerts, Finance, Analytics, Users & Omnisearch.
     */
    public function test_admin_alerts_finance_analytics_and_users(): void
    {
        $this->actingAs($this->admin);

        // 17. Automated Alerts Engine
        $this->get(route('alerts.index'))->assertOk();
        $this->get(route('alerts.templates'))->assertOk();
        $this->get(route('alerts.gateways'))->assertOk();
        $this->post(route('alerts.gateways.test-otp'), ['phone' => '8789076658'])->assertRedirect();
        $this->post(route('alerts.run-sweep'))->assertRedirect();

        // 18. Attendance & Payroll Hub
        $this->get(route('attendance.index'))->assertOk();
        $this->post(route('attendance.quick-mark'), [
            'user_id' => $this->staff->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ])->assertRedirect();

        $this->get(route('finance.salaries.index'))->assertOk();
        $this->get(route('finance.payroll.index'))->assertOk();
        $this->get(route('finance.analytics'))->assertOk();

        // 19. Executive Analytics
        $this->get(route('analytics.index'))->assertOk();
        $this->get(route('analytics.api'))->assertOk();
        $this->get(route('analytics.export-pdf'))->assertOk();
        $this->get(route('analytics.cost-profit'))->assertOk();
        $this->get(route('analytics.cost-profit.export-pdf'))->assertOk();
        $this->get(route('analytics.technicians'))->assertOk();
        $this->get(route('analytics.technicians.export-pdf'))->assertOk();
        $this->get(route('analytics.mrr-retention'))->assertOk();
        $this->get(route('analytics.mrr-retention.export-pdf'))->assertOk();

        // 20. Admin User Accounts Management
        $this->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.users.create'))->assertOk();
        $this->get(route('admin.users.edit', $this->staff))->assertOk();

        // 21. Omnisearch & Mobile Scanner
        $this->get(route('api.global-search', ['q' => 'Metro']))->assertOk();
        $this->get(route('mobile.scanner'))->assertOk();
        $this->get(route('api.mobile-scanner.lan-info'))->assertOk();
    }

    /**
     * Audit 2: Technician Portal Workstation, Survey & Service Dispatch Actions.
     */
    public function test_technician_portal_workflows_and_dispatches(): void
    {
        $this->actingAs($this->technician);

        // 1. Technician Dashboard
        $response = $this->get(route('technician.dashboard'));
        $response->assertOk();
        $response->assertSee('Field Engineer Workstation');
        $response->assertSee('Bob Miller');

        // 2. Technician Job Status Update
        $jobUpdateResponse = $this->post(route('technician.jobs.updateStatus', $this->job), [
            'status' => 'in_progress',
            'installation_notes' => 'Technician Bob working on cabling.',
        ]);
        $jobUpdateResponse->assertRedirect();
        $this->assertEquals('in_progress', $this->job->fresh()->status);

        // 3. Technician Service Ticket Resolution
        $ticketUpdateResponse = $this->post(route('technician.service-tickets.update', $this->ticket), [
            'status' => 'resolved',
            'troubleshooting_notes' => 'Cleaned optical lens and recalibrated focus.',
            'resolution_notes' => 'Live view 4K sharpness verified.',
        ]);
        $ticketUpdateResponse->assertRedirect();
        $this->assertEquals('resolved', $this->ticket->fresh()->status);

        // 4. Technician Site Survey Entry
        $surveyView = $this->get(route('technician.surveys.show', $this->survey));
        $surveyView->assertOk();

        $surveyComplete = $this->post(route('technician.surveys.complete', $this->survey), [
            'camera_count_recommended' => 12,
            'cable_length_estimate' => 250,
            'dvr_location' => 'Server Rack B',
            'power_availability' => '3kVA Online UPS',
            'visit_notes' => 'Site audited by Bob Miller.',
        ]);
        $surveyComplete->assertRedirect();
        $this->assertEquals('completed', $this->survey->fresh()->status);

        // 5. Security: Technician is blocked from Admin endpoints
        $this->get(route('admin.users.index'))->assertStatus(403);
        $this->get(route('analytics.index'))->assertStatus(403);
        $this->get(route('finance.salaries.index'))->assertStatus(403);
    }

    /**
     * Audit 3: Customer Client Portal Self-Service, Payments, AMC & Service Requests.
     */
    public function test_customer_client_portal_workflows_and_requests(): void
    {
        $this->actingAs($this->customerUser);

        // 1. Customer Portal Dashboard
        $response = $this->get(route('portal.dashboard'));
        $response->assertOk();
        $response->assertSee('Customer Portal');
        $response->assertSee('Srinithish P');

        // 2. Customer Equipment Registry
        $equipmentResponse = $this->get(route('portal.equipment'));
        $equipmentResponse->assertOk();
        $equipmentResponse->assertSee('HKV-4K-AUDIT-9901');

        // 3. Customer AMC Overview & Renewal Request
        $amcResponse = $this->get(route('portal.amc'));
        $amcResponse->assertOk();
        $amcResponse->assertSee('AMC-2026-AUDIT01');

        $renewalResponse = $this->post(route('portal.amc.renew'), [
            'notes' => 'Please extend AMC for another 2 years.',
        ]);
        $renewalResponse->assertRedirect();

        // 4. Customer Service Tickets Listing & Creation
        $ticketsResponse = $this->get(route('portal.tickets'));
        $ticketsResponse->assertOk();

        $createTicketPage = $this->get(route('portal.tickets.create'));
        $createTicketPage->assertOk();

        $storeTicketResponse = $this->post(route('portal.tickets.store'), [
            'title' => 'PTZ Camera Preset Shifted',
            'issue_type' => 'ptz_control_issue',
            'priority' => 'medium',
            'description' => 'Camera preset #4 pointing slightly upward.',
        ]);
        $storeTicketResponse->assertRedirect();
        $this->assertDatabaseHas('service_tickets', [
            'title' => 'PTZ Camera Preset Shifted',
            'created_by_id' => $this->customerUser->id,
        ]);

        // 5. Customer Invoices & Billing
        $invoicesResponse = $this->get(route('portal.invoices'));
        $invoicesResponse->assertOk();
        $invoicesResponse->assertSee('INV-2026-AUDIT01');

        // 6. Customer Quotations & Acceptance
        $quotationsResponse = $this->get(route('portal.quotations'));
        $quotationsResponse->assertOk();
        $quotationsResponse->assertSee('QT-2026-AUDIT01');

        // 7. Customer Site Surveys
        $surveysResponse = $this->get(route('portal.surveys'));
        $surveysResponse->assertOk();

        // 8. Customer Site Work Request
        $workRequest = $this->post(route('portal.site-work-request'), [
            'request_type' => 'new_installation',
            'title' => 'Warehouse CCTV Expansion',
            'details' => 'Need 6 additional weatherproof dome cameras.',
        ]);
        $workRequest->assertRedirect();

        // 9. Security: Customer is blocked from internal ERP
        $this->get(route('dashboard'))->assertStatus(403);
        $this->get(route('leads.index'))->assertStatus(403);
        $this->get(route('jobs.index'))->assertStatus(403);
        $this->get(route('admin.users.index'))->assertStatus(403);
    }

    /**
     * Audit 4: Public Storefront, Checkout Gateways, and PDF Viewers.
     */
    public function test_public_storefront_checkout_and_pdf_viewers(): void
    {
        // 1. Storefront Home
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertOk();
        $homeResponse->assertSee('Precision IT Systems');

        // 2. Public Store Inquiry Engine
        $inquiryResponse = $this->post(route('public.inquire'), [
            'customer_name' => 'Dr. Arvind Kumar',
            'phone' => '9845012345',
            'email' => 'arvind@hospital.org',
            'city' => 'Bangalore',
            'category' => 'Commercial Surveillance',
            'notes' => 'Hospital ICU and Reception CCTV audit requested.',
        ]);
        $inquiryResponse->assertRedirect();
        $this->assertDatabaseHas('leads', [
            'customer_name' => 'Dr. Arvind Kumar',
            'phone' => '9845012345',
        ]);

        // 3. Public Quotation PDF Viewer
        $quotePdfResponse = $this->get(route('quotations.public-pdf', $this->quotation));
        $quotePdfResponse->assertOk();

        // 4. Public JCR PDF Viewer
        $jcrPdfResponse = $this->get(route('jcr.public-pdf', $this->jcr));
        $jcrPdfResponse->assertOk();

        // 5. Public Online Payment Checkout
        $unpaidInvoice = Invoice::create([
            'installation_job_id' => $this->job->id,
            'quotation_id' => $this->quotation->id,
            'invoice_no' => 'INV-2026-CHECKOUT',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal' => 10000,
            'tax_percent' => 18,
            'tax_amount' => 1800,
            'total' => 11800,
            'amount_paid' => 0,
            'status' => 'unpaid',
        ]);

        $sentQuotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'quotation_no' => 'QT-2026-CHECKOUT',
            'subtotal' => 20000,
            'tax_percent' => 18,
            'tax_amount' => 3600,
            'total' => 23600,
            'status' => 'sent',
        ]);

        $invoiceCheckout = $this->get(route('payment.checkout.invoice', $unpaidInvoice));
        $invoiceCheckout->assertOk();

        $quoteCheckout = $this->get(route('payment.checkout.quotation', $sentQuotation));
        $quoteCheckout->assertOk();
    }
}

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
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\RmaClaim;
use App\Models\ServiceTicket;
use App\Models\SiteSurvey;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KesavanEmployeeModuleRealtimeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Lead $lead;
    private Product $product;
    private Quotation $quotation;
    private InstallationJob $job;
    private Invoice $invoice;
    private Payment $payment;
    private AmcContract $amc;
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

        // 1. Create Admin
        $this->admin = User::factory()->create([
            'name' => 'System Admin',
            'email' => 'admin@cctvcrm.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // 2. Setup standard CRM fixtures for module testing
        $this->lead = Lead::create([
            'customer_name' => 'Enterprise Client',
            'email' => 'client@enterprise.com',
            'phone' => '9876543210',
            'site_address' => '100 Tech Park, Bangalore',
            'status' => 'won',
            'source' => 'Direct Lead',
        ]);

        $this->product = Product::create([
            'name' => 'Hikvision 4MP Dome Camera',
            'category' => 'Camera',
            'brand' => 'Hikvision',
            'model_number' => 'DS-2CD2143G2-I',
            'unit_price' => 4500,
            'cost_price' => 3200,
            'stock_quantity' => 25,
            'minimum_stock' => 5,
        ]);

        $this->quotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'quotation_no' => 'QT-2026-TEST01',
            'subtotal' => 18000,
            'tax_percent' => 18,
            'tax_amount' => 3240,
            'total' => 21240,
            'status' => 'accepted',
        ]);

        $this->job = InstallationJob::create([
            'quotation_id' => $this->quotation->id,
            'job_no' => 'JOB-2026-TEST01',
            'status' => 'completed',
            'scheduled_date' => now()->toDateString(),
        ]);

        $this->invoice = Invoice::create([
            'installation_job_id' => $this->job->id,
            'quotation_id' => $this->quotation->id,
            'invoice_no' => 'INV-2026-TEST01',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'subtotal' => 18000,
            'tax_percent' => 18,
            'tax_amount' => 3240,
            'total' => 21240,
            'amount_paid' => 21240,
            'status' => 'paid',
        ]);

        $this->payment = Payment::create([
            'invoice_id' => $this->invoice->id,
            'reference_no' => 'RCPT-2026-001',
            'amount' => 21240,
            'paid_on' => now()->toDateString(),
            'method' => 'upi',
            'recorded_by' => $this->admin->id,
        ]);

        $this->amc = AmcContract::create([
            'lead_id' => $this->lead->id,
            'contract_no' => 'AMC-2026-TEST01',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'frequency' => 'quarterly',
            'value' => 8000,
            'status' => 'active',
        ]);

        $this->survey = SiteSurvey::create([
            'lead_id' => $this->lead->id,
            'surveyed_by' => $this->admin->id,
            'survey_date' => now()->toDateString(),
            'site_address' => '100 Tech Park, Bangalore',
            'contact_person' => 'Enterprise Client',
            'contact_phone' => '9876543210',
            'camera_count_recommended' => 4,
            'status' => 'completed',
        ]);

        $this->ticket = ServiceTicket::create([
            'ticket_no' => 'TKT-2026-TEST01',
            'lead_id' => $this->lead->id,
            'title' => 'Camera Feed Offline',
            'description' => 'Camera 2 feed intermittent',
            'issue_type' => 'camera_offline',
            'priority' => 'high',
            'status' => 'open',
        ]);

        $this->equipment = InstalledEquipment::create([
            'lead_id' => $this->lead->id,
            'installation_job_id' => $this->job->id,
            'product_id' => $this->product->id,
            'equipment_name' => 'Hikvision 4MP Dome Camera',
            'serial_number' => 'HKV-4MP-TEST999',
            'mac_address' => 'BC:92:68:5A:11:01',
            'location_tag' => 'Main Gate',
            'installation_date' => now()->toDateString(),
            'manufacturer_warranty_expiry' => now()->addYears(2)->toDateString(),
            'service_warranty_expiry' => now()->addYear()->toDateString(),
            'status' => 'active',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Hikvision India Official',
            'contact_person' => 'Rajesh Sharma',
            'phone' => '9812345678',
            'email' => 'sales@hikvision-distributor.in',
            'city' => 'Bangalore',
            'status' => 'active',
        ]);

        $this->purchaseOrder = PurchaseOrder::create([
            'po_number' => 'PO-2026-TEST01',
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'subtotal' => 32000,
            'tax_percent' => 18,
            'tax_amount' => 5760,
            'total' => 37760,
            'status' => 'ordered',
            'payment_status' => 'unpaid',
            'created_by' => $this->admin->id,
        ]);

        $this->rmaClaim = RmaClaim::create([
            'rma_no' => 'RMA-2026-TEST01',
            'installed_equipment_id' => $this->equipment->id,
            'lead_id' => $this->lead->id,
            'supplier_id' => $this->supplier->id,
            'faulty_serial_number' => 'HKV-4MP-TEST999',
            'issue_description' => 'IR Cut Filter Failure',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        $this->jcr = JobCompletionReport::create([
            'report_no' => 'JCR-2026-TEST01',
            'lead_id' => $this->lead->id,
            'installation_job_id' => $this->job->id,
            'technician_id' => $this->admin->id,
            'completion_date' => now()->toDateString(),
            'signer_name' => 'Enterprise Client',
            'signer_phone' => '9876543210',
            'customer_rating' => 5,
            'customer_feedback' => 'Work completed with high quality',
            'customer_signature' => 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="300" height="100"><text x="10" y="50">Customer</text></svg>',
            'technician_signature' => 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="300" height="100"><text x="10" y="50">Admin</text></svg>',
            'work_summary' => 'Tested 4 cameras and verified NVR connection.',
            'status' => 'signed',
        ]);
    }

    /**
     * Test 1: Admin registers employee Kesavan via Admin User Management endpoint.
     */
    public function test_admin_can_register_kesavan_as_employee(): User
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'kesavan',
            'email' => 'kesavan@gmail.com',
            'password' => 'kesavan123',
            'password_confirmation' => 'kesavan123',
            'role' => 'staff',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status', 'Account registered successfully.');

        $this->assertDatabaseHas('users', [
            'name' => 'kesavan',
            'email' => 'kesavan@gmail.com',
            'role' => 'staff',
        ]);

        $kesavan = User::where('email', 'kesavan@gmail.com')->firstOrFail();
        $this->assertTrue(Hash::check('kesavan123', $kesavan->password));
        $this->assertTrue($kesavan->isStaff());
        $this->assertTrue($kesavan->isInternal());

        return $kesavan;
    }

    /**
     * Test 2: Realtime login authentication for employee Kesavan.
     */
    public function test_kesavan_employee_can_login_with_exact_credentials(): void
    {
        $kesavan = User::create([
            'name' => 'kesavan',
            'email' => 'kesavan@gmail.com',
            'password' => Hash::make('kesavan123'),
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'kesavan@gmail.com',
            'password' => 'kesavan123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($kesavan);
    }

    /**
     * Test 3: Kesavan can access all operational internal CRM modules in realtime.
     */
    public function test_kesavan_can_access_all_internal_crm_modules(): void
    {
        $kesavan = User::create([
            'name' => 'kesavan',
            'email' => 'kesavan@gmail.com',
            'password' => Hash::make('kesavan123'),
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($kesavan);

        // 1. Dashboard & Live Metrics API
        $this->get(route('dashboard'))->assertOk()->assertSee('kesavan');
        $this->get(route('dashboard.data'))->assertOk()->assertJsonStructure(['stats']);

        // 2. Operations Calendar & Events
        $this->get(route('calendar.index'))->assertOk();
        $this->get(route('calendar.events'))->assertOk();

        // 3. Leads Module
        $this->get(route('leads.index'))->assertOk()->assertSee('Enterprise Client');
        $this->get(route('leads.show', $this->lead))->assertOk()->assertSee('Enterprise Client');
        $this->get(route('leads.vcard', $this->lead))->assertOk();

        // 4. Quotations Module
        $this->get(route('quotations.index'))->assertOk()->assertSee('QT-2026-TEST01');
        $this->get(route('quotations.show', $this->quotation))->assertOk();
        $this->get(route('quotations.pdf', ['quotation' => $this->quotation, 'format' => 'compact']))->assertOk();

        // 5. CCTV Storage & Estimator
        $this->get(route('estimator.index'))->assertOk();

        // 6. Installation Jobs
        $this->get(route('jobs.index'))->assertOk()->assertSee('JOB-2026-TEST01');
        $this->get(route('jobs.show', $this->job))->assertOk();

        // 7. Products Catalog
        $this->get(route('products.index'))->assertOk()->assertSee('Hikvision 4MP Dome Camera');

        // 8. Inventory & Stock Movements
        $this->get(route('inventory.index'))->assertOk();
        $this->get(route('inventory.movements'))->assertOk();

        // 9. Installed Equipment Registry & API
        $this->get(route('equipment.index'))->assertOk()->assertSee('HKV-4MP-TEST999');
        $this->get(route('equipment.show', $this->equipment))->assertOk();
        $this->get(route('equipment.lookup', ['serial' => 'HKV-4MP-TEST999']))->assertOk();

        // 10. RMA Claims
        $this->get(route('rma.index'))->assertOk()->assertSee('RMA-2026-TEST01');
        $this->get(route('rma.show', $this->rmaClaim))->assertOk();
        $this->get(route('rma.dispatch-pdf', $this->rmaClaim))->assertOk();

        // 11. Suppliers & Vendors
        $this->get(route('suppliers.index'))->assertOk()->assertSee('Hikvision India Official');
        $this->get(route('suppliers.show', $this->supplier))->assertOk();

        // 12. Purchase Orders
        $this->get(route('purchase-orders.index'))->assertOk()->assertSee('PO-2026-TEST01');
        $this->get(route('purchase-orders.show', $this->purchaseOrder))->assertOk();

        // 13. Invoices & Receipts
        $this->get(route('invoices.index'))->assertOk()->assertSee('INV-2026-TEST01');
        $this->get(route('invoices.show', $this->invoice))->assertOk();
        $this->get(route('payments.receipt.pdf', $this->payment))->assertOk();

        // 14. AMC Contracts
        $this->get(route('amcs.index'))->assertOk()->assertSee('AMC-2026-TEST01');
        $this->get(route('amcs.show', $this->amc))->assertOk();

        // 15. Site Surveys
        $this->get(route('site-surveys.index'))->assertOk();
        $this->get(route('site-surveys.show', $this->survey))->assertOk();

        // 16. Service Tickets
        $this->get(route('service-tickets.index'))->assertOk()->assertSee('TKT-2026-TEST01');
        $this->get(route('service-tickets.show', $this->ticket))->assertOk();
        $this->get(route('service-tickets.export'))->assertOk();
        $this->get(route('service-tickets.export-pdf'))->assertOk();

        // 17. Digital Job Completion Reports (JCR)
        $this->get(route('jcr.index'))->assertOk()->assertSee('JCR-2026-TEST01');
        $this->get(route('jcr.show', $this->jcr))->assertOk();
        $this->get(route('jcr.download-pdf', $this->jcr))->assertOk();

        // 18. Attendance Module & Clock In / Out
        $this->get(route('attendance.index'))->assertOk();
        $clockInResponse = $this->post(route('attendance.clock-in'));
        $clockInResponse->assertRedirect();
        $attRecord = EmployeeAttendance::where('user_id', $kesavan->id)->latest()->first();
        $this->assertNotNull($attRecord);
        $this->assertContains($attRecord->status, ['present', 'late']);

        $clockOutResponse = $this->post(route('attendance.clock-out'));
        $clockOutResponse->assertRedirect();

        // 19. Mobile Scanner & Universal Omnisearch
        $this->get(route('mobile.scanner'))->assertOk();
        $this->get(route('api.mobile-scanner.lan-info'))->assertOk();
        $this->get(route('api.global-search', ['q' => 'Hikvision']))->assertOk();
    }

    /**
     * Test 4: Security RBAC restrictions protect Admin and Customer areas from Kesavan (Staff).
     */
    public function test_kesavan_is_properly_restricted_from_admin_and_customer_only_routes(): void
    {
        $kesavan = User::create([
            'name' => 'kesavan',
            'email' => 'kesavan@gmail.com',
            'password' => Hash::make('kesavan123'),
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($kesavan);

        // 1. Blocked from User Management
        $this->get(route('admin.users.index'))->assertStatus(403);
        $this->get(route('admin.users.create'))->assertStatus(403);
        $this->post(route('admin.users.store'), [])->assertStatus(403);

        // 2. Blocked from Automated Alerts & Gateways
        $this->get(route('alerts.index'))->assertStatus(403);
        $this->get(route('alerts.templates'))->assertStatus(403);
        $this->get(route('alerts.gateways'))->assertStatus(403);

        // 3. Blocked from Finance & Salaries Configuration
        $this->get(route('finance.salaries.index'))->assertStatus(403);
        $this->get(route('finance.payroll.index'))->assertStatus(403);
        $this->get(route('finance.analytics'))->assertStatus(403);

        // 4. Blocked from Executive Analytics
        $this->get(route('analytics.index'))->assertStatus(403);
        $this->get(route('analytics.cost-profit'))->assertStatus(403);
        $this->get(route('analytics.technicians'))->assertStatus(403);
        $this->get(route('analytics.mrr-retention'))->assertStatus(403);

        // 5. Blocked from Customer Client Portal
        $this->get(route('portal.dashboard'))->assertStatus(403);
    }

    /**
     * Test 5: Admin manages employee Kesavan's salary structure and attendance.
     */
    public function test_admin_can_manage_kesavan_salary_and_attendance(): void
    {
        $kesavan = User::create([
            'name' => 'kesavan',
            'email' => 'kesavan@gmail.com',
            'password' => Hash::make('kesavan123'),
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($this->admin);

        // Admin configures Kesavan's salary with phone number 8789076658 recorded in notes
        $response = $this->from(route('finance.salaries.index'))->post(route('finance.salaries.update', $kesavan), [
            'base_salary_monthly' => 30000,
            'daily_rate' => 1200,
            'hourly_rate' => 150,
            'travel_allowance' => 2000,
            'payment_method' => 'bank_transfer',
            'bank_name' => 'HDFC Bank',
            'bank_account_number' => '50100234567890',
            'bank_ifsc' => 'HDFC0001234',
            'notes' => 'Phone: 8789076658 - Senior Surveillance Field Engineer',
        ]);

        $response->assertRedirect(route('finance.salaries.index'));

        $this->assertDatabaseHas('employee_salaries', [
            'user_id' => $kesavan->id,
            'base_salary_monthly' => 30000,
            'payment_method' => 'bank_transfer',
        ]);

        // Admin quick marks attendance for Kesavan
        $attendanceResponse = $this->post(route('attendance.quick-mark'), [
            'user_id' => $kesavan->id,
            'date' => now()->toDateString(),
            'status' => 'present',
            'work_hours' => 8.5,
        ]);

        $attendanceResponse->assertRedirect();
        $this->assertDatabaseHas('employee_attendances', [
            'user_id' => $kesavan->id,
            'status' => 'present',
        ]);

        // Admin generates monthly payroll including Kesavan
        $payrollResponse = $this->from(route('finance.payroll.index'))->post(route('finance.payroll.generate'), [
            'period_type'  => 'monthly',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end'   => now()->endOfMonth()->toDateString(),
            'user_id'      => $kesavan->id,
        ]);

        $payrollResponse->assertRedirect(route('finance.payroll.index'));
        $this->assertDatabaseHas('payrolls', [
            'user_id' => $kesavan->id,
        ]);
    }

    /**
     * Test 6: Full end-to-end operational flow between Admin and Employee Kesavan.
     */
    public function test_e2e_admin_to_kesavan_flow_and_realtime_operations(): void
    {
        // 1. Admin creates a new Lead with contact details
        $this->actingAs($this->admin);
        $createLeadResponse = $this->post(route('leads.store'), [
            'customer_name' => 'Cyber City Towers',
            'phone' => '8789076658',
            'email' => 'contact@cybercity.com',
            'site_address' => 'Floor 12, Tower B, Electronic City',
            'source' => 'call',
            'notes' => 'Handled by Kesavan - CCTV 16 Channel Upgrade',
        ]);
        $createLeadResponse->assertRedirect();

        $newLead = Lead::where('customer_name', 'Cyber City Towers')->firstOrFail();

        // 2. Admin creates employee Kesavan
        $createStaffResponse = $this->post(route('admin.users.store'), [
            'name' => 'kesavan',
            'email' => 'kesavan@gmail.com',
            'password' => 'kesavan123',
            'password_confirmation' => 'kesavan123',
            'role' => 'staff',
        ]);
        $createStaffResponse->assertRedirect(route('admin.users.index'));

        $kesavan = User::where('email', 'kesavan@gmail.com')->firstOrFail();

        // 3. Kesavan logs in and operates the system
        $this->actingAs($kesavan);

        // Kesavan views the newly created lead
        $leadViewResponse = $this->get(route('leads.show', $newLead));
        $leadViewResponse->assertOk()->assertSee('Cyber City Towers');

        // Kesavan checks global omnisearch for the customer
        $searchResponse = $this->get(route('api.global-search', ['q' => 'Cyber City']));
        $searchResponse->assertOk();

        // Kesavan self-clocks in for the workday
        $clockIn = $this->post(route('attendance.clock-in'));
        $clockIn->assertRedirect();

        // Kesavan self-clocks out
        $clockOut = $this->post(route('attendance.clock-out'));
        $clockOut->assertRedirect();

        $attendance = EmployeeAttendance::where('user_id', $kesavan->id)
            ->whereDate('date', now()->toDateString())
            ->first();
        $this->assertNotNull($attendance);
        $this->assertContains($attendance->status, ['present', 'late']);
    }
}


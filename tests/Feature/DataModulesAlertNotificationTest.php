<?php

namespace Tests\Feature;

use App\Models\ExpenseClaim;
use App\Models\InstallationJob;
use App\Models\JobCompletionReport;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SiteSurvey;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AlertNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DataModulesAlertNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $technician;
    private User $staff;
    private AlertNotificationService $alertService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'     => 'System Admin',
            'email'    => 'admin@cctvcrm.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        $this->technician = User::factory()->create([
            'name'     => 'Rajesh Technician',
            'email'    => 'rajesh.tech@cctvcrm.com',
            'password' => Hash::make('password123'),
            'role'     => 'technician',
        ]);

        $this->staff = User::factory()->create([
            'name'     => 'Anand Staff',
            'email'    => 'anand@cctvcrm.com',
            'password' => Hash::make('password123'),
            'role'     => 'staff',
        ]);

        $this->alertService = app(AlertNotificationService::class);
    }

    /**
     * 1. Test Lead Creation and Status Update Notifications.
     */
    public function test_lead_creation_and_status_transition_triggers_notifications(): void
    {
        $response = $this->actingAs($this->admin)->post(route('leads.store'), [
            'customer_name' => 'Dr. Ramesh Nathan',
            'phone'         => '9876543210',
            'email'         => 'ramesh@hospital.com',
            'site_address'  => '123 Apollo Health City, Chennai',
            'company_name'  => 'Apollo Hospitals',
            'source'        => 'referral',
            'status'        => 'new',
            'notes'         => 'Hospital surveillance expansion project',
        ]);

        $response->assertRedirect();
        $lead = Lead::where('customer_name', 'Dr. Ramesh Nathan')->firstOrFail();

        $this->assertDatabaseHas('notification_logs', [
            'event_type'      => 'lead_created',
            'recipient_name'  => 'Dr. Ramesh Nathan',
            'recipient_phone' => '9876543210',
        ]);

        // Status transition
        $responseStatus = $this->actingAs($this->admin)->post(route('leads.status', $lead), [
            'status' => 'contacted',
        ]);

        $responseStatus->assertRedirect();
        $this->assertDatabaseHas('notification_logs', [
            'event_type'     => 'lead_status_updated',
            'recipient_name' => 'Dr. Ramesh Nathan',
        ]);
    }

    /**
     * 2. Test Site Survey Scheduling and Completion Alerts.
     */
    public function test_site_survey_scheduling_and_completion_triggers_alerts(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Senthil Kumar',
            'phone'         => '9887766554',
            'email'         => 'senthil@factory.com',
            'site_address'  => 'Plot 45, Ambattur Industrial Estate',
            'status'        => 'contacted',
        ]);

        $response = $this->actingAs($this->admin)->post(route('site-surveys.store'), [
            'lead_id'        => $lead->id,
            'surveyed_by'    => $this->technician->id,
            'survey_date'    => now()->addDays(2)->format('Y-m-d H:i:s'),
            'site_address'   => $lead->site_address,
            'contact_person' => $lead->customer_name,
            'contact_phone'  => $lead->phone,
            'visit_notes'    => 'Initial perimeter survey for 16 IP cameras',
            'status'         => 'pending',
        ]);

        $response->assertRedirect();
        $survey = SiteSurvey::where('lead_id', $lead->id)->firstOrFail();

        $this->assertDatabaseHas('notification_logs', [
            'event_type'      => 'survey_scheduled',
            'recipient_name'  => 'Senthil Kumar',
            'recipient_phone' => '9887766554',
        ]);

        // Complete survey
        $responseUpdate = $this->actingAs($this->admin)->put(route('site-surveys.update', $survey), [
            'lead_id'        => $lead->id,
            'surveyed_by'    => $this->technician->id,
            'survey_date'    => $survey->survey_date,
            'site_address'   => $survey->site_address,
            'contact_person' => $survey->contact_person,
            'contact_phone'  => $survey->contact_phone,
            'visit_notes'    => 'Survey completed. 16 channel NVR and CAT6 cabling recommended.',
            'status'         => 'completed',
        ]);

        $responseUpdate->assertRedirect();
        $this->assertDatabaseHas('notification_logs', [
            'event_type'     => 'survey_completed',
            'recipient_name' => 'Senthil Kumar',
        ]);
    }

    /**
     * 3. Test Installation Job Scheduling Alert.
     */
    public function test_installation_job_scheduling_dispatches_alert(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Vikram Seth',
            'phone'         => '9123456789',
            'email'         => 'vikram@residence.com',
            'site_address'  => 'Villa 12, Palm Meadows',
            'status'        => 'won',
        ]);

        $quote = Quotation::create([
            'lead_id'      => $lead->id,
            'quotation_no' => 'QT-2026-901',
            'status'       => 'accepted',
            'subtotal'     => 50000,
            'tax_percent'  => 18,
            'tax_amount'   => 9000,
            'total'        => 59000,
        ]);

        $job = InstallationJob::create([
            'quotation_id' => $quote->id,
            'job_no'       => 'JOB-202609-001',
            'status'       => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('jobs.update', $job), [
            'assigned_technician_id' => $this->technician->id,
            'scheduled_date'         => now()->addDays(3)->format('Y-m-d H:i:s'),
            'status'                 => 'scheduled',
            'installation_notes'     => 'Installation scheduled with technician Rajesh.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('notification_logs', [
            'event_type'      => 'job_scheduled',
            'recipient_name'  => 'Vikram Seth',
            'recipient_phone' => '9123456789',
        ]);
    }

    /**
     * 4. Test Expense Claim Submission and Status Update Alerts.
     */
    public function test_expense_claim_lifecycle_triggers_alerts(): void
    {
        $response = $this->actingAs($this->staff)->post(route('finance.expenses.store'), [
            'expense_category' => 'hardware_tools',
            'expense_date'     => now()->toDateString(),
            'amount'           => 1450.00,
            'description'      => 'Drill bits and crimping tool for site deployment',
        ]);

        $response->assertRedirect();
        $claim = ExpenseClaim::where('user_id', $this->staff->id)->firstOrFail();

        $this->assertDatabaseHas('notification_logs', [
            'event_type'     => 'expense_submitted',
            'recipient_name' => 'Anand Staff',
        ]);

        // Admin approves claim
        $responseApprove = $this->actingAs($this->admin)->patch(route('finance.expenses.approve', $claim));
        $responseApprove->assertRedirect();

        $this->assertDatabaseHas('notification_logs', [
            'event_type'     => 'expense_status_updated',
            'recipient_name' => 'Anand Staff',
        ]);
    }

    /**
     * 5. Test Leave Request Submission and Approval Alerts.
     */
    public function test_leave_request_lifecycle_triggers_alerts(): void
    {
        $response = $this->actingAs($this->staff)->post(route('leaves.store'), [
            'leave_type' => 'sick',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date'   => now()->addDays(6)->toDateString(),
            'reason'     => 'Dental treatment procedure and rest',
        ]);

        $response->assertRedirect();
        $leave = LeaveRequest::where('user_id', $this->staff->id)->firstOrFail();

        $this->assertDatabaseHas('notification_logs', [
            'event_type'     => 'leave_submitted',
            'recipient_name' => 'Anand Staff',
        ]);

        // Admin approves leave
        $responseApprove = $this->actingAs($this->admin)->patch(route('leaves.approve', $leave));
        $responseApprove->assertRedirect();

        $this->assertDatabaseHas('notification_logs', [
            'event_type'     => 'leave_status_updated',
            'recipient_name' => 'Anand Staff',
        ]);
    }

    /**
     * 6. Test Purchase Order Creation and Goods Receipt Alerts.
     */
    public function test_purchase_order_creation_and_receipt_triggers_alerts(): void
    {
        $supplier = Supplier::create([
            'name'         => 'Praveen Wholesaler',
            'company_name' => 'Hikvision Authorised Distributor',
            'phone'        => '9443322110',
            'email'        => 'sales@hikdistributor.com',
            'is_active'    => true,
        ]);

        $product = Product::create([
            'name'           => 'Hikvision 4MP IP Dome Camera',
            'sku'            => 'DS-2CD1143G0-I',
            'category'       => 'camera',
            'purchase_price' => 2200,
            'selling_price'  => 3500,
            'stock_quantity' => 10,
            'min_stock'      => 5,
            'is_active'      => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchase-orders.store'), [
            'supplier_id'            => $supplier->id,
            'order_date'             => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(4)->toDateString(),
            'tax_percent'            => 18,
            'shipping_cost'          => 300,
            'items' => [
                [
                    'product_id'        => $product->id,
                    'item_name'         => $product->name,
                    'sku'               => $product->sku,
                    'unit_cost'         => 2200,
                    'quantity_ordered'  => 10,
                ]
            ],
        ]);

        $response->assertRedirect();
        $po = PurchaseOrder::where('supplier_id', $supplier->id)->firstOrFail();

        $this->assertDatabaseHas('notification_logs', [
            'event_type'      => 'po_created',
            'recipient_name'  => 'Hikvision Authorised Distributor',
            'recipient_phone' => '9443322110',
        ]);

        // Receive goods
        $item = $po->items->first();
        $responseReceive = $this->actingAs($this->admin)->post(route('purchase-orders.receive', $po), [
            'received' => [
                $item->id => 10,
            ],
        ]);

        $responseReceive->assertRedirect();
        $this->assertDatabaseHas('notification_logs', [
            'event_type'     => 'po_goods_received',
            'recipient_name' => 'Hikvision Authorised Distributor',
        ]);
    }

    /**
     * 7. Test Payroll Disbursal Alert.
     */
    public function test_payroll_disbursal_triggers_salary_alert(): void
    {
        $payroll = Payroll::create([
            'payroll_number' => 'PAY-202609-888',
            'user_id'        => $this->staff->id,
            'period_type'    => 'monthly',
            'period_start'   => '2026-09-01',
            'period_end'     => '2026-09-30',
            'working_days'   => 26,
            'present_days'   => 26,
            'basic_pay'      => 25000.00,
            'allowances'     => 1000.00,
            'deductions'     => 0.00,
            'net_salary'     => 26000.00,
            'status'         => 'approved',
            'created_by'     => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('finance.payroll.updateStatus', $payroll), [
            'status'            => 'paid',
            'payment_date'      => now()->toDateString(),
            'payment_reference' => 'HDFC-NEFT-9918231',
            'notes'             => 'September 2026 Salary Disbursed',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('notification_logs', [
            'event_type'     => 'salary_disbursed',
            'recipient_name' => 'Anand Staff',
        ]);
    }
}

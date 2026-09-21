<?php

namespace Tests\Feature;

use App\Models\AmcContract;
use App\Models\InstalledEquipment;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\ServiceTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $otherCustomer;
    private User $admin;
    private Lead $lead;
    private Lead $otherLead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lead = Lead::create([
            'customer_name' => 'Acme Corporation',
            'email' => 'client@acme.com',
            'phone' => '9876543210',
            'site_address' => '123 Tech Park, Sector 5',
            'status' => 'won',
        ]);

        $this->otherLead = Lead::create([
            'customer_name' => 'Global Logistics',
            'email' => 'other@global.com',
            'phone' => '9123456780',
            'site_address' => '456 Warehouse Blvd',
            'status' => 'won',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'John Customer',
            'email' => 'client@acme.com',
            'role' => 'customer',
            'lead_id' => $this->lead->id,
        ]);

        $this->otherCustomer = User::factory()->create([
            'name' => 'Other Customer',
            'email' => 'other@global.com',
            'role' => 'customer',
            'lead_id' => $this->otherLead->id,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@cctvcrm.com',
            'role' => 'admin',
        ]);
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get(route('portal.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_view_portal_dashboard(): void
    {
        $response = $this->actingAs($this->customer)->get(route('portal.dashboard'));
        $response->assertOk();
        $response->assertSee('Acme Corporation');
        $response->assertSee('Customer Portal');
    }

    public function test_customer_dashboard_displays_correct_metrics_and_equipment(): void
    {
        $product = Product::create([
            'sku' => 'CAM-DOME-01',
            'name' => 'Hikvision 4MP Dome Camera',
            'unit' => 'Nos',
            'cost_price' => 2000,
            'unit_price' => 3200,
            'stock_quantity' => 10,
        ]);

        InstalledEquipment::create([
            'lead_id' => $this->lead->id,
            'product_id' => $product->id,
            'equipment_name' => 'Reception IP Dome Camera',
            'serial_number' => 'HK-SN-998877',
            'location_tag' => 'Reception Desk',
            'installation_date' => now()->subMonths(2),
            'manufacturer_warranty_expiry' => now()->addMonths(22),
            'service_warranty_expiry' => now()->addMonths(10),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->customer)->get(route('portal.dashboard'));
        $response->assertOk();
        $response->assertSee('Reception IP Dome Camera');
        $response->assertSee('HK-SN-998877');
        $response->assertSee('Reception Desk');
    }

    public function test_customer_can_view_their_installed_equipment_list(): void
    {
        InstalledEquipment::create([
            'lead_id' => $this->lead->id,
            'equipment_name' => 'Warehouse Gate Bullet Camera',
            'serial_number' => 'SN-WH-112233',
            'location_tag' => 'Gate 1',
            'installation_date' => now()->subDays(30),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->customer)->get(route('portal.equipment'));
        $response->assertOk();
        $response->assertSee('Warehouse Gate Bullet Camera');
        $response->assertSee('SN-WH-112233');
    }

    public function test_customer_cannot_view_other_customers_equipment(): void
    {
        InstalledEquipment::create([
            'lead_id' => $this->otherLead->id,
            'equipment_name' => 'Secret Private Camera',
            'serial_number' => 'SN-SECRET-777',
            'location_tag' => 'Vault',
            'installation_date' => now()->subDays(10),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->customer)->get(route('portal.equipment'));
        $response->assertOk();
        $response->assertDontSee('Secret Private Camera');
        $response->assertDontSee('SN-SECRET-777');
    }

    public function test_customer_can_view_amc_contracts_and_request_renewal(): void
    {
        $contract = AmcContract::create([
            'lead_id' => $this->lead->id,
            'contract_no' => 'AMC-2026-001',
            'start_date' => now()->subMonths(1),
            'end_date' => now()->addMonths(11),
            'value' => 12000,
            'frequency' => 'quarterly',
            'status' => 'active',
        ]);
        $contract->generateVisits();

        $response = $this->actingAs($this->customer)->get(route('portal.amc'));
        $response->assertOk();
        $response->assertSee('AMC-2026-001');
        $response->assertSee('Quarterly Maintenance Coverage');

        // Request renewal
        $renewResponse = $this->actingAs($this->customer)->post(route('portal.amc.renew'), [
            'notes' => 'Please extend for 2 more years.',
        ]);
        $renewResponse->assertSessionHas('status');

        $this->assertDatabaseHas('service_tickets', [
            'lead_id' => $this->lead->id,
            'title' => 'AMC Contract Renewal Request',
        ]);
    }

    public function test_customer_can_create_new_service_ticket(): void
    {
        $response = $this->actingAs($this->customer)->get(route('portal.tickets.create'));
        $response->assertOk();
        $response->assertSee('Report Breakdown / Service Request');

        $postResponse = $this->actingAs($this->customer)->post(route('portal.tickets.store'), [
            'title' => 'NVR not recording on Channel 4',
            'issue_type' => 'recording_failure',
            'priority' => 'high',
            'description' => 'Since morning, Channel 4 shows blank recording timeline.',
        ]);

        $postResponse->assertSessionHas('status');

        $this->assertDatabaseHas('service_tickets', [
            'lead_id' => $this->lead->id,
            'title' => 'NVR not recording on Channel 4',
            'issue_type' => 'recording_failure',
            'priority' => 'high',
            'status' => 'open',
        ]);
    }

    public function test_customer_cannot_view_other_customers_service_ticket(): void
    {
        $otherTicket = ServiceTicket::create([
            'ticket_no' => 'TCK-202609-9999',
            'lead_id' => $this->otherLead->id,
            'title' => 'Private other client ticket',
            'issue_type' => 'camera_offline',
            'priority' => 'medium',
            'status' => 'open',
            'description' => 'Other client problem',
            'billing_type' => 'billable',
        ]);

        $response = $this->actingAs($this->customer)->get(route('portal.tickets.show', $otherTicket));
        $response->assertForbidden();
    }

    public function test_customer_can_view_invoices(): void
    {
        $quotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'quotation_no' => 'QT-INV-001',
            'quotation_date' => now(),
            'subtotal' => 10000,
            'discount' => 0,
            'tax_percent' => 18,
            'tax_amount' => 1800,
            'total' => 11800,
            'status' => 'accepted',
        ]);

        $job = \App\Models\InstallationJob::create([
            'quotation_id' => $quotation->id,
            'job_no' => 'JOB-INV-001',
            'status' => 'completed',
        ]);

        Invoice::create([
            'installation_job_id' => $job->id,
            'quotation_id' => $quotation->id,
            'invoice_no' => 'INV-20260901-0001',
            'invoice_date' => now(),
            'due_date' => now()->addDays(15),
            'subtotal' => 10000,
            'discount' => 0,
            'tax_percent' => 18,
            'tax_amount' => 1800,
            'total' => 11800,
            'amount_paid' => 5000,
            'status' => 'partially_paid',
        ]);

        $response = $this->actingAs($this->customer)->get(route('portal.invoices'));
        $response->assertOk();
        $response->assertSee('INV-20260901-0001');
        $response->assertSee('11,800.00');
        $response->assertSee('Partially Paid');
    }

    public function test_customer_can_accept_and_reject_quotation(): void
    {
        $quote1 = Quotation::create([
            'lead_id' => $this->lead->id,
            'quotation_no' => 'QT-ACCEPT-01',
            'quotation_date' => now(),
            'subtotal' => 20000,
            'discount' => 0,
            'tax_percent' => 18,
            'tax_amount' => 3600,
            'total' => 23600,
            'status' => 'sent',
        ]);

        $quote2 = Quotation::create([
            'lead_id' => $this->lead->id,
            'quotation_no' => 'QT-REJECT-02',
            'quotation_date' => now(),
            'subtotal' => 30000,
            'discount' => 0,
            'tax_percent' => 18,
            'tax_amount' => 5400,
            'total' => 35400,
            'status' => 'sent',
        ]);

        // Accept Quote 1
        $acceptResp = $this->actingAs($this->customer)->post(route('portal.quotations.accept', $quote1));
        $acceptResp->assertSessionHas('status');
        $this->assertEquals('accepted', $quote1->fresh()->status);
        $this->assertEquals('won', $this->lead->fresh()->status);

        // Reject Quote 2
        $rejectResp = $this->actingAs($this->customer)->post(route('portal.quotations.reject', $quote2), [
            'rejection_reason' => 'Price is above our approved quarterly budget.',
        ]);
        $rejectResp->assertSessionHas('status');
        $this->assertEquals('rejected', $quote2->fresh()->status);
        $this->assertEquals('Price is above our approved quarterly budget.', $quote2->fresh()->rejection_reason);
    }

    public function test_unlinked_customer_can_view_all_portal_pages_without_errors(): void
    {
        $unlinkedCustomer = User::factory()->create([
            'name' => 'Brand New User',
            'email' => 'newuser@randomsite.com',
            'role' => 'customer',
            'lead_id' => null,
        ]);

        $routes = [
            'portal.dashboard',
            'portal.equipment',
            'portal.amc',
            'portal.tickets',
            'portal.invoices',
            'portal.quotations',
        ];

        foreach ($routes as $route) {
            $resp = $this->actingAs($unlinkedCustomer)->get(route($route));
            $resp->assertOk();
        }
    }

    public function test_new_registered_customer_auto_provisions_lead_profile(): void
    {
        $newCustomer = User::factory()->create([
            'name' => 'Sailesh Kumar',
            'email' => 'sailesh@gmail.com',
            'role' => 'customer',
            'lead_id' => null,
        ]);

        $response = $this->actingAs($newCustomer)->get(route('portal.dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('hasLead', true);

        $newCustomer->refresh();
        $this->assertNotNull($newCustomer->lead_id);
        $this->assertEquals('Sailesh Kumar', $newCustomer->lead->customer_name);
        $this->assertEquals('sailesh@gmail.com', $newCustomer->lead->email);
        $this->assertEquals('customer_portal', $newCustomer->lead->source);
    }

    public function test_customer_can_submit_site_work_request(): void
    {
        $customer = User::factory()->create([
            'name' => 'Sailesh',
            'email' => 'sailesh@gmail.com',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->post(route('portal.site-work-request'), [
            'customer_name' => 'Sailesh Kumar Enterprise',
            'email'         => 'sailesh@gmail.com',
            'phone'         => '+91 98765 43210',
            'site_address'  => 'Flat 402, Green Valley Apartments, Sector 18, Noida',
            'work_type'     => 'cctv_installation',
            'description'   => 'Need 4 high clarity IP cameras installed for building perimeter and main gate.',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $response->assertSessionHas('status');

        $customer->refresh();
        $this->assertEquals('Sailesh Kumar Enterprise', $customer->lead->customer_name);
        $this->assertEquals('+91 98765 43210', $customer->lead->phone);
        $this->assertEquals('Flat 402, Green Valley Apartments, Sector 18, Noida', $customer->lead->site_address);

        $this->assertDatabaseHas('service_tickets', [
            'lead_id' => $customer->lead_id,
            'title'   => 'New CCTV Installation & Site Survey Request',
            'status'  => 'open',
        ]);

        $this->assertDatabaseHas('site_surveys', [
            'lead_id'        => $customer->lead_id,
            'contact_person' => 'Sailesh Kumar Enterprise',
            'contact_phone'  => '+91 98765 43210',
            'status'         => 'pending',
        ]);
    }

    public function test_customer_can_book_free_site_survey_from_modal(): void
    {
        $customer = User::factory()->create([
            'name' => 'Amit Verma',
            'email' => 'amit.verma@example.com',
            'role' => 'customer',
        ]);

        $preferredDate = now()->addDays(2)->format('Y-m-d');

        $response = $this->actingAs($customer)->post(route('portal.site-work-request'), [
            'customer_name'  => 'Amit Verma Tech Corp',
            'email'          => 'amit.verma@example.com',
            'phone'          => '9876512345',
            'site_address'   => 'Plot 101, IT Park, Outer Ring Road',
            'work_type'      => 'cctv_installation',
            'preferred_date' => $preferredDate,
            'description'    => 'Free site survey requested for 8 cameras in 2-floor commercial office.',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $response->assertSessionHas('status');

        $customer->refresh();
        $this->assertEquals('contacted', $customer->lead->status);

        $survey = \App\Models\SiteSurvey::where('lead_id', $customer->lead_id)->first();
        $this->assertNotNull($survey);
        $this->assertEquals($preferredDate, $survey->survey_date->format('Y-m-d'));
        $this->assertEquals('Amit Verma Tech Corp', $survey->contact_person);
        $this->assertEquals('pending', $survey->status);

        // Verify it appears in the customer portal surveys list
        $surveyResponse = $this->actingAs($customer)->get(route('portal.surveys'));
        $surveyResponse->assertOk();
        $surveyResponse->assertSee('Amit Verma Tech Corp');
    }

    public function test_admin_updates_lead_data_synced_to_customer_portal(): void
    {
        // Admin updates lead information
        $this->actingAs($this->admin)->put(route('leads.update', $this->lead), [
            'customer_name' => 'Acme Corporation Updated',
            'phone'         => '9988776655',
            'email'         => 'client@acme.com',
            'site_address'  => 'New Updated Tech Park Address',
            'source'        => 'website',
        ]);

        $this->lead->refresh();
        $this->assertEquals('Acme Corporation Updated', $this->lead->customer_name);
        $this->assertEquals('9988776655', $this->lead->phone);
        $this->assertEquals('New Updated Tech Park Address', $this->lead->site_address);

        // Customer views dashboard and sees updated information
        $response = $this->actingAs($this->customer)->get(route('portal.dashboard'));
        $response->assertOk();
        $response->assertSee('Acme Corporation Updated');
        $response->assertSee('New Updated Tech Park Address');
    }

    public function test_customer_can_submit_calculator_custom_quotation_and_appears_on_admin_page(): void
    {
        $customer = User::factory()->create([
            'name'  => 'Sunil Shetty',
            'email' => 'sunil@residency.in',
            'role'  => 'customer',
        ]);

        $response = $this->actingAs($customer)->post(route('portal.site-work-request'), [
            'customer_name'        => 'Sunil Heights Residency',
            'email'                => 'sunil@residency.in',
            'phone'                => '9822334455',
            'site_address'         => 'Tower 4, Palm Meadows, Bangalore',
            'work_type'            => 'cctv_installation',
            'is_calculator_quote'  => '1',
            'camera_count'         => 8,
            'tech_type'            => '5mp_colorvu',
            'storage_days'         => 30,
            'wiring_type'          => 'pvc',
            'description'          => '=== Customer Customized CCTV Package Estimate ===\n1. Camera Count: 8 Cameras\n2. Model & Technology: 5MP ColorVu 24/7 (3K Super HD)\n3. NVR System: 8-Channel 4K NVR\n4. Storage Retention: 30 Days (2TB Surveillance HDD (24/7 WD Purple))\n5. Power & Switching: 8-Port Gigabit PoE Network Switch\n6. Cabling & Conduit: Standard Heavy-Duty PVC Conduit + CAT6 Cable Run (8 Points)\n7. Estimated Hardware: ₹58,300\n8. Estimated Cabling & Installation: ₹9,600\n9. Total Estimated Package Value: ₹68,794 (incl. 18% GST)\n\nCustomer requested an on-site validation and official commercial quotation with this specification.',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $response->assertSessionHas('status');

        $customer->refresh();
        $this->assertEquals('quoted', $customer->lead->status);

        // Verify Quotation created in DB
        $quotation = Quotation::where('lead_id', $customer->lead_id)->first();
        $this->assertNotNull($quotation);
        $this->assertEquals('sent', $quotation->status);
        $this->assertGreaterThan(0, $quotation->total);
        $this->assertCount(7, $quotation->items);

        // Verify Admin can see this Quotation on the Admin Quotations page
        $adminQuoteResponse = $this->actingAs($this->admin)->get(route('quotations.index'));
        $adminQuoteResponse->assertOk();
        $adminQuoteResponse->assertSee($quotation->quotation_no);
        $adminQuoteResponse->assertSee('Sunil Heights Residency');

        // Verify Admin can see this Quotation on the Lead show page
        $adminLeadResponse = $this->actingAs($this->admin)->get(route('leads.show', $customer->lead));
        $adminLeadResponse->assertOk();
        $adminLeadResponse->assertSee($quotation->quotation_no);

        // Verify Customer sees this Quotation in Customer Portal
        $customerQuoteResponse = $this->actingAs($customer)->get(route('portal.quotations'));
        $customerQuoteResponse->assertOk();
        $customerQuoteResponse->assertSee($quotation->quotation_no);
    }
}

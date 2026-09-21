<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDataFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }
    public function test_admin_can_access_all_executive_and_crm_modules()
    {
        $admin = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($admin, 'Admin test user exists');

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/leads');
        $response->assertStatus(200);
        $response->assertSee('Srinithish P');

        $response = $this->actingAs($admin)->get('/quotations');
        $response->assertStatus(200);
        $response->assertSee('QT-2026-SRI01');

        $response = $this->actingAs($admin)->get('/inventory');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/analytics');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/analytics/mrr-retention');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/alerts');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/jcr');
        $response->assertStatus(200);
        $response->assertSee('JCR-2026-0089');
    }

    public function test_employee_staff_can_manage_leads_quotes_and_tickets()
    {
        $staff = User::where('email', 'alex@example.com')->first();
        $this->assertNotNull($staff, 'Staff user exists');

        $response = $this->actingAs($staff)->get('/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($staff)->get('/leads');
        $response->assertStatus(200);
        $response->assertSee('Srinithish P');

        $response = $this->actingAs($staff)->get('/service-tickets');
        $response->assertStatus(200);
        $response->assertSee('TKT-2026-1082');
    }

    public function test_technician_can_access_technician_portal_and_assigned_tasks()
    {
        $technician = User::where('email', 'bob@example.com')->first();
        $this->assertNotNull($technician, 'Technician user exists');

        $response = $this->actingAs($technician)->get('/technician/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Bob Miller');
    }

    public function test_customer_srinithish_can_access_self_service_portal()
    {
        $customer = User::where('email', 'srinithish.p@example.com')->first();
        $this->assertNotNull($customer, 'Customer srinithish.p exists');

        // Customer Dashboard
        $response = $this->actingAs($customer)->get('/portal/dashboard');
        $response->assertStatus(200);

        // Equipment & Warranty Tracking
        $response = $this->actingAs($customer)->get('/portal/equipment');
        $response->assertStatus(200);
        $response->assertSee('HKV-4MP-984210');

        // AMC Contract
        $response = $this->actingAs($customer)->get('/portal/amc');
        $response->assertStatus(200);
        $response->assertSee('AMC-2026-SRI');

        // Service Tickets
        $response = $this->actingAs($customer)->get('/portal/tickets');
        $response->assertStatus(200);
        $response->assertSee('TKT-2026-1082');

        // Invoices & Billing
        $response = $this->actingAs($customer)->get('/portal/invoices');
        $response->assertStatus(200);
        $response->assertSee('INV-2026-0042');

        // Quotations
        $response = $this->actingAs($customer)->get('/portal/quotations');
        $response->assertStatus(200);
        $response->assertSee('QT-2026-SRI01');
    }
}

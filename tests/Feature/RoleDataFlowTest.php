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
        // The demo customers, quotes and tickets these checks look for come from the demo seeder
        $this->seed();
        $this->seed(\Database\Seeders\DemoAnalyticsSeeder::class);
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
        $response->assertSee('TKT-2026-101');
    }

    public function test_technician_can_access_technician_portal_and_assigned_tasks()
    {
        $technician = User::where('email', 'bob@example.com')->first();
        $this->assertNotNull($technician, 'Technician user exists');

        $response = $this->actingAs($technician)->get('/technician/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Bob Miller');
    }

    public function test_customer_portal_routes_are_retired_and_customer_is_restricted()
    {
        $this->assertNull(User::where('email', 'srinithish.p@example.com')->first(), 'Customer demo account was removed');

        $customer = User::factory()->create(['role' => 'customer']);

        // Customer Portal routes are retired (404)
        $this->actingAs($customer)->get('/portal/dashboard')->assertStatus(404);
        $this->actingAs($customer)->get('/portal/equipment')->assertStatus(404);
        $this->actingAs($customer)->get('/portal/amc')->assertStatus(404);
        $this->actingAs($customer)->get('/portal/tickets')->assertStatus(404);

        // Customer is restricted from internal ERP (403)
        $this->actingAs($customer)->get('/dashboard')->assertStatus(403);
    }
}

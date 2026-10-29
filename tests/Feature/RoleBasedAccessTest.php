<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use App\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    public function test_login_redirects_correctly_based_on_role(): void
    {
        // Admin redirect
        $response = $this->post(route('login'), [
            'email' => $this->admin->email,
            'password' => 'password',
        ]);
        $response->assertRedirect(route('dashboard'));

        $this->post(route('logout'));

        // Staff redirect
        $response = $this->post(route('login'), [
            'email' => $this->staff->email,
            'password' => 'password',
        ]);
        $response->assertRedirect(route('dashboard'));

        $this->post(route('logout'));

        // Customer login is rejected
        $response = $this->post(route('login'), [
            'email' => $this->customer->email,
            'password' => 'password',
        ]);
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_can_access_everything(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        $response = $this->get(route('leads.index'));
        $response->assertOk();

        $response = $this->get(route('leads.create'));
        $response->assertOk();

        $response = $this->get(route('jobs.index'));
        $response->assertOk();

        $response = $this->get(route('products.index'));
        $response->assertOk();

        $response = $this->get(route('products.create'));
        $response->assertOk();

        $response = $this->get(route('admin.users.index'));
        $response->assertOk();
    }

    public function test_staff_add_records_but_cannot_manage_users(): void
    {
        $this->actingAs($this->staff);

        // Can view internal screens
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('leads.index'))->assertOk();
        $this->get(route('jobs.index'))->assertOk();
        $this->get(route('products.index'))->assertOk();

        // CAN add leads and products, CANNOT add projects or manage user accounts
        $this->get(route('leads.create'))->assertOk();
        $this->get(route('products.create'))->assertOk();
        $this->get(route('projects.create'))->assertStatus(403);
        $this->get(route('admin.users.index'))->assertStatus(403);

        // CAN manage quotations
        $this->get(route('quotations.index'))->assertOk();
    }

    public function test_customer_is_restricted_from_all_crm_internal_screens(): void
    {
        $this->actingAs($this->customer);

        // CANNOT access internal CRM dashboard
        $this->get(route('dashboard'))->assertStatus(403);

        // CANNOT access leads or jobs
        $this->get(route('leads.index'))->assertStatus(403);
        $this->get(route('jobs.index'))->assertStatus(403);
        $this->get(route('products.index'))->assertStatus(403);
        $this->get(route('quotations.index'))->assertStatus(403);
    }
}

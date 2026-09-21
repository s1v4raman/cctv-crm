<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
        $this->get(route('admin.users.create'))->assertRedirect(route('login'));
        $this->post(route('admin.users.store'), [])->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        // Staff/Employee blocked
        $this->actingAs($this->staff)->get(route('admin.users.index'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('admin.users.create'))->assertStatus(403);
        $this->actingAs($this->staff)->post(route('admin.users.store'), [])->assertStatus(403);

        // Customer blocked
        $this->actingAs($this->customer)->get(route('admin.users.index'))->assertStatus(403);
    }

    public function test_admin_can_view_users_list_with_filters(): void
    {
        $tech = User::factory()->create(['name' => 'John Tech', 'role' => 'technician']);
        $client = User::factory()->create(['name' => 'Mary Client', 'role' => 'customer']);

        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertOk();
        $response->assertSee('User Accounts Management');
        $response->assertSee('John Tech');
        $response->assertSee('Mary Client');

        // Filter by technician
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['role' => 'technician']));
        $response->assertOk();
        $response->assertSee('John Tech');
        $response->assertDontSee('Mary Client');
    }

    public function test_admin_can_register_new_technician_or_employee(): void
    {
        // 1. Create a Technician
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Charlie Davis',
            'email' => 'charlie@example.com',
            'role' => 'technician',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Charlie Davis',
            'email' => 'charlie@example.com',
            'role' => 'technician',
        ]);

        // 2. Create an Employee
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Emma Staff',
            'email' => 'emma@example.com',
            'role' => 'staff',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Emma Staff',
            'email' => 'emma@example.com',
            'role' => 'staff',
        ]);
    }

    public function test_admin_can_update_user_details_and_change_role(): void
    {
        $tech = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'role' => 'technician',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $tech), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'role' => 'staff', // changed role to employee
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $tech->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'role' => 'staff',
        ]);
    }

    public function test_admin_can_delete_other_users_but_not_self(): void
    {
        $tech = User::factory()->create(['role' => 'technician']);

        // Delete other user
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $tech));
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $tech->id]);

        // Try to delete self
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHasErrors(['delete']);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }
}

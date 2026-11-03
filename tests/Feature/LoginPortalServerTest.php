<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The local admin and technician servers (LOGIN_PORTAL=admin / technician) each show and accept only their own
 * sign-in: the admin server the admin's, the technician server the technician's and the daily worker's.
 */
class LoginPortalServerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_in_page_shows_every_portal_when_the_server_is_not_locked(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Operations Sign In')
            ->assertSee("setRole('admin')", false)
            ->assertSee("setRole('technician')", false)
            ->assertSee("setRole('staff')", false)
            ->assertSee("setRole('worker')", false)
            ->assertSee('workerLoginForm', false);
    }

    public function test_admin_server_shows_only_the_admin_sign_in(): void
    {
        config(['app.login_portal' => 'admin']);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Admin Sign In')
            ->assertSee('Admin Server')
            ->assertSee('loginForm', false)
            ->assertDontSee("setRole('technician')", false)
            ->assertDontSee("setRole('worker')", false)
            ->assertDontSee('workerLoginForm', false)
            ->assertDontSee('Operations Sign In');
    }

    public function test_technician_server_shows_the_technician_and_daily_worker_sign_in(): void
    {
        config(['app.login_portal' => 'technician']);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Technician Sign In')
            ->assertSee('Technician Server')
            ->assertSee("setRole('technician')", false)
            ->assertSee("setRole('worker')", false)
            ->assertSee('workerLoginForm', false)
            ->assertSee("portal: 'technician'", false)
            ->assertDontSee("setRole('admin')", false)
            ->assertDontSee("setRole('staff')", false);
    }

    public function test_technician_server_comes_back_to_the_daily_worker_tab(): void
    {
        config(['app.login_portal' => 'technician']);

        $this->withSession(['login_portal' => 'worker'])
            ->get('/login')
            ->assertOk()
            ->assertSee("portal: 'worker'", false);
    }

    public function test_admin_server_signs_in_admins_and_turns_technicians_away(): void
    {
        config(['app.login_portal' => 'admin']);
        $admin = User::factory()->create(['role' => 'admin']);
        $technician = User::factory()->create(['role' => 'technician']);

        $this->post('/login', ['email' => $technician->email, 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'This server is for admin sign-in only. Technicians and daily workers use the technician server.']);
        $this->assertGuest();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_technician_server_signs_in_technicians_and_turns_admins_away(): void
    {
        config(['app.login_portal' => 'technician']);
        $admin = User::factory()->create(['role' => 'admin']);
        $technician = User::factory()->create(['role' => 'technician']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'This server is for technician and daily worker sign-in only. Admins use the admin server.']);
        $this->assertGuest();

        $this->post('/login', ['email' => $technician->email, 'password' => 'password'])
            ->assertRedirect(route('technician.dashboard'));
        $this->assertAuthenticatedAs($technician);
    }

    public function test_daily_workers_sign_in_on_the_technician_server(): void
    {
        config(['app.login_portal' => 'technician']);
        Worker::create(['name' => 'Muthu Vel', 'phone' => '9840123456', 'daily_rate' => 750.00, 'is_active' => true]);

        $this->post(route('login.worker'), ['name' => 'Muthu Vel', 'phone' => '9840123456'])
            ->assertRedirect(route('worker.portal'));
        $this->assertAuthenticated();

        $this->get(route('worker.portal'))->assertOk();
        $this->assertAuthenticated();
    }

    public function test_an_account_already_signed_in_is_signed_out_on_the_wrong_server(): void
    {
        config(['app.login_portal' => 'technician']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_a_signed_in_daily_worker_is_signed_out_on_the_admin_server(): void
    {
        config(['app.login_portal' => 'admin']);
        $worker = User::factory()->create(['role' => 'worker']);

        $this->actingAs($worker)
            ->get(route('worker.portal'))
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_worker_sign_in_is_closed_on_the_admin_server(): void
    {
        config(['app.login_portal' => 'admin']);

        $this->post(route('login.worker'), ['name' => 'Muthu Vel', 'phone' => '9840123456'])
            ->assertNotFound();
        $this->assertDatabaseCount('workers', 0);
    }
}

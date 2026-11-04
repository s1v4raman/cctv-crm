<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Two sign-in links on one server: /login for admins and employees, /field/login for technicians and daily
 * workers, each turning the other's accounts away. The local admin and technician servers (LOGIN_PORTAL=admin /
 * technician) each show and accept only their own portal at /login.
 */
class LoginPortalServerTest extends TestCase
{
    use RefreshDatabase;

    public function test_office_sign_in_shows_only_admin_and_employee(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Admin &amp; Employee Sign In', false)
            ->assertSee("setRole('admin')", false)
            ->assertSee("setRole('staff')", false)
            ->assertSee('action="'.route('login').'"', false)
            ->assertDontSee("setRole('technician')", false)
            ->assertDontSee("setRole('worker')", false)
            ->assertDontSee('workerLoginForm', false);
    }

    public function test_field_sign_in_shows_only_technician_and_daily_worker(): void
    {
        $this->get('/field')->assertRedirect(route('field.login'));

        $this->get('/field/login')
            ->assertOk()
            ->assertSee('Technician Sign In')
            ->assertSee('Field Team')
            ->assertSee("setRole('technician')", false)
            ->assertSee("setRole('worker')", false)
            ->assertSee('workerLoginForm', false)
            ->assertSee('action="'.route('field.login.post').'"', false)
            ->assertSee('portal=field', false)
            ->assertDontSee("setRole('admin')", false)
            ->assertDontSee("setRole('staff')", false);
    }

    public function test_office_sign_in_lets_in_admins_and_employees_and_sends_technicians_and_workers_to_the_field_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $technician = User::factory()->create(['role' => 'technician']);
        $worker = User::factory()->create(['role' => 'worker']);
        $message = 'This sign-in page is for admins and employees. Technicians and daily workers sign in at '.route('field.login');

        foreach ([$technician, $worker] as $fieldAccount) {
            $this->post('/login', ['email' => $fieldAccount->email, 'password' => 'password'])
                ->assertRedirect(route('login'))
                ->assertSessionHasErrors(['email' => $message]);
            $this->assertGuest();
        }

        foreach ([$admin, $staff] as $officeAccount) {
            $this->post('/login', ['email' => $officeAccount->email, 'password' => 'password'])
                ->assertRedirect(route('dashboard'));
            $this->assertAuthenticatedAs($officeAccount);
            $this->post(route('logout'))->assertRedirect(route('login'));
        }
    }

    public function test_field_sign_in_lets_in_technicians_and_sends_admins_and_employees_to_the_office_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $technician = User::factory()->create(['role' => 'technician']);

        foreach ([$admin, $staff] as $officeAccount) {
            $this->post('/field/login', ['email' => $officeAccount->email, 'password' => 'password'])
                ->assertRedirect(route('field.login'))
                ->assertSessionHasErrors(['email' => 'This sign-in page is for technicians and daily workers. Admins and employees sign in at '.route('login')]);
            $this->assertGuest();
        }

        $this->post('/field/login', ['email' => $technician->email, 'password' => 'password'])
            ->assertRedirect(route('technician.dashboard'));
        $this->assertAuthenticatedAs($technician);

        $this->post(route('logout'))->assertRedirect(route('field.login'));
    }

    public function test_daily_workers_sign_in_and_out_on_the_field_link(): void
    {
        Worker::create(['name' => 'Muthu Vel', 'phone' => '9840123456', 'daily_rate' => 750.00, 'is_active' => true]);

        $this->from('/field/login')
            ->post(route('login.worker'), ['name' => 'Muthu Vel', 'phone' => '9840123456'])
            ->assertRedirect(route('worker.portal'));
        $this->assertAuthenticated();

        $this->post(route('logout'))
            ->assertRedirect(route('field.login'))
            ->assertSessionHas('login_portal', 'worker');
    }

    public function test_signed_out_technicians_and_workers_are_sent_to_the_field_link(): void
    {
        $this->get(route('technician.dashboard'))->assertRedirect(route('field.login'));
        $this->get(route('worker.portal'))->assertRedirect(route('field.login'));
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_google_sign_in_follows_the_page_it_started_from(): void
    {
        $technician = User::factory()->create(['email' => 'tech@gmail.com', 'role' => 'technician']);
        $googleUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $googleUser->shouldReceive('getId')->andReturn('google-tech');
        $googleUser->shouldReceive('getEmail')->andReturn('tech@gmail.com');
        $googleUser->shouldReceive('getAvatar')->andReturn(null);
        $provider = \Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($googleUser);
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        // From the office page's Google button
        $this->withSession(['oauth_mode' => 'login', 'oauth_portal' => 'staff'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        // From the field page's
        $this->withSession(['oauth_mode' => 'login', 'oauth_portal' => 'field'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('technician.dashboard'));
        $this->assertAuthenticatedAs($technician);
    }

    public function test_admin_server_shows_the_admin_and_employee_sign_in_and_no_field_link(): void
    {
        config(['app.login_portal' => 'admin']);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Admin &amp; Employee Sign In', false)
            ->assertSee('Admin Server')
            ->assertSee('loginForm', false)
            ->assertSee("setRole('admin')", false)
            ->assertSee("setRole('staff')", false)
            ->assertDontSee("setRole('technician')", false)
            ->assertDontSee("setRole('worker')", false)
            ->assertDontSee('workerLoginForm', false);

        $this->get('/field/login')->assertNotFound();
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

    public function test_admin_server_signs_in_admins_and_employees_and_turns_technicians_away(): void
    {
        config(['app.login_portal' => 'admin']);
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $technician = User::factory()->create(['role' => 'technician']);

        $this->post('/login', ['email' => $technician->email, 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'This server is for admin and employee sign-in only. Technicians and daily workers use the technician server.']);
        $this->assertGuest();

        $this->post('/login', ['email' => $staff->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($staff);
        $this->post(route('logout'));

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
            ->assertSessionHasErrors(['email' => 'This server is for technician and daily worker sign-in only. Admins and employees use the admin server.']);
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

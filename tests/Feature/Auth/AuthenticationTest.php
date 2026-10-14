<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_customer_can_logout_and_redirects_to_login(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('staff.login'));
    }

    public function test_staff_can_logout_and_redirects_to_login(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('staff.login'));
    }

    public function test_customer_login_screen_redirects_to_login(): void
    {
        $response = $this->get(route('customer.login'));

        $response->assertRedirect(route('login'));
    }

    public function test_customer_cannot_login_to_retired_portal(): void
    {
        $customer = User::factory()->create([
            'email' => 'customer@example.com',
            'role' => 'customer',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
    }

    public function test_staff_login_screen_renders_staff_erp_branding(): void
    {
        $response = $this->get(route('staff.login'));

        $response->assertOk()
            ->assertSee('Operations Sign In')
            ->assertSee('Staff &amp; Admin ERP', false)
            ->assertDontSee('Customer Sign In');
    }

    public function test_google_redirect_warns_if_credentials_missing(): void
    {
        config(['services.google.client_id' => '']);
        config(['services.google.client_secret' => '']);

        $response = $this->get(route('auth.google'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
    }

    public function test_google_callback_rejects_unregistered_user_in_login_mode(): void
    {
        $abstractUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-unregistered');
        $abstractUser->shouldReceive('getName')->andReturn('Unregistered User');
        $abstractUser->shouldReceive('getEmail')->andReturn('unregistered@gmail.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn(null);
        $abstractUser->shouldReceive('getNickname')->andReturn(null);

        $provider = \Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);

        // mode is login by default
        $response = $this->withSession(['oauth_mode' => 'login', 'oauth_portal' => 'customer'])
            ->get(route('auth.google.callback'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseMissing('users', ['email' => 'unregistered@gmail.com']);
    }

    public function test_google_callback_authenticates_existing_registered_staff_in_login_mode(): void
    {
        $user = User::factory()->create([
            'email' => 'existingstaff@gmail.com',
            'role' => 'staff',
        ]);

        $abstractUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-999');
        $abstractUser->shouldReceive('getName')->andReturn('Existing Staff');
        $abstractUser->shouldReceive('getEmail')->andReturn('existingstaff@gmail.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar2.jpg');
        $abstractUser->shouldReceive('getNickname')->andReturn(null);

        $provider = \Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);

        $response = $this->withSession(['oauth_mode' => 'login', 'oauth_portal' => 'staff'])
            ->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
        $this->assertEquals('google-999', $user->fresh()->google_id);
    }

    public function test_google_callback_rejects_customer_role_user(): void
    {
        $user = User::factory()->create([
            'email' => 'customeruser@gmail.com',
            'role' => 'customer',
        ]);

        $abstractUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-888');
        $abstractUser->shouldReceive('getName')->andReturn('Customer User');
        $abstractUser->shouldReceive('getEmail')->andReturn('customeruser@gmail.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn(null);
        $abstractUser->shouldReceive('getNickname')->andReturn(null);

        $provider = \Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);

        $response = $this->withSession(['oauth_mode' => 'login', 'oauth_portal' => 'staff'])
            ->get(route('auth.google.callback'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
    }
}

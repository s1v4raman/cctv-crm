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

    public function test_customer_can_logout_and_redirects_to_home(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_staff_can_logout_and_redirects_to_login(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('staff.login'));
    }

    public function test_customer_login_screen_renders_customer_portal_branding(): void
    {
        $response = $this->get(route('customer.login'));

        $response->assertOk()
            ->assertSee('Customer Sign In')
            ->assertSee('Customer Portal')
            ->assertDontSee('Operations Sign In');
    }

    public function test_staff_login_screen_renders_staff_erp_branding(): void
    {
        $response = $this->get(route('staff.login'));

        $response->assertOk()
            ->assertSee('Operations Sign In')
            ->assertSee('Staff')
            ->assertSee('Admin ERP')
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

    public function test_google_callback_creates_and_authenticates_customer_in_register_mode(): void
    {
        $abstractUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-123456');
        $abstractUser->shouldReceive('getName')->andReturn('Google Customer');
        $abstractUser->shouldReceive('getEmail')->andReturn('newcustomer@gmail.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.jpg');
        $abstractUser->shouldReceive('getNickname')->andReturn(null);

        $provider = \Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);

        $response = $this->withSession(['oauth_mode' => 'register', 'oauth_portal' => 'customer'])
            ->get(route('auth.google.callback'));

        $this->assertAuthenticated();
        $user = \App\Models\User::where('email', 'newcustomer@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('customer', $user->role);
        $this->assertEquals('google-123456', $user->google_id);
        $this->assertEquals('https://lh3.googleusercontent.com/avatar.jpg', $user->avatar);
        $response->assertRedirect(route('portal.dashboard'));
    }

    public function test_google_callback_authenticates_existing_registered_customer_in_login_mode(): void
    {
        $user = User::factory()->create([
            'email' => 'existingcustomer@gmail.com',
            'role' => 'customer',
        ]);

        $abstractUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-999');
        $abstractUser->shouldReceive('getName')->andReturn('Existing Customer');
        $abstractUser->shouldReceive('getEmail')->andReturn('existingcustomer@gmail.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar2.jpg');
        $abstractUser->shouldReceive('getNickname')->andReturn(null);

        $provider = \Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);

        $response = $this->withSession(['oauth_mode' => 'login', 'oauth_portal' => 'customer'])
            ->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('portal.dashboard'));
        $this->assertEquals('google-999', $user->fresh()->google_id);
    }
}

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
}

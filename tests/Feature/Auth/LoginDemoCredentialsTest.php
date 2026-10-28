<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginDemoCredentialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_hides_demo_credentials_outside_local(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('Demo Logins');
        $response->assertDontSee('test@example.com');
        $response->assertDontSee('value="password"', false);
        $response->assertDontSee('Raju Kumar');
    }

    public function test_login_screen_offers_demo_credentials_in_local(): void
    {
        $this->app['env'] = 'local';

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Demo Logins');
        $response->assertSee('value="password"', false);
    }
}

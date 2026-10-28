<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginDemoCredentialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_never_shows_demo_credentials(): void
    {
        foreach (['production', 'local'] as $environment) {
            $this->app['env'] = $environment;

            $response = $this->get('/login');

            $response->assertStatus(200);
            $response->assertDontSee('Demo Logins');
            $response->assertDontSee('test@example.com');
            $response->assertDontSee('bob@example.com');
            $response->assertDontSee('alex@example.com');
            $response->assertDontSee('value="password"', false);
            $response->assertDontSee('Raju Kumar');
            $response->assertDontSee('9876543210');
        }
    }
}

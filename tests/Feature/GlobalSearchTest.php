<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_returns_matches(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $lead = Lead::create([
            'customer_name' => 'Acme Security Systems',
            'phone' => '9876543210',
            'status' => 'new'
        ]);

        $response = $this->actingAs($user)->getJson('/api/global-search?q=Acme');

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Acme Security Systems']);
    }

    public function test_global_search_is_closed_to_guests_workers_and_customers(): void
    {
        Lead::create(['customer_name' => 'Acme Security Systems', 'phone' => '9876543210', 'status' => 'new']);

        // Results carry customer names, phone numbers and addresses
        $this->getJson('/api/global-search?q=Acme')->assertUnauthorized();
        $this->actingAs(User::factory()->create(['role' => 'worker']))->getJson('/api/global-search?q=Acme')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'customer']))->getJson('/api/global-search?q=Acme')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'technician']))->getJson('/api/global-search?q=Acme')->assertOk();
    }
}

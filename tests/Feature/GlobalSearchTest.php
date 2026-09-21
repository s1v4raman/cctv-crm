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
}

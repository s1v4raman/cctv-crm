<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActionButtonsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_leads_index_renders_action_button_icons_for_admin_and_employee()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);

        Lead::create([
            'customer_name' => 'Acme Corp',
            'phone' => '9876543210',
            'status' => 'new',
            'created_by' => $admin->id,
        ]);

        // Test as Admin
        $response = $this->actingAs($admin)->get(route('leads.index'));
        $response->assertStatus(200);

        // Check More actions button has icon
        $response->assertSee('title="More actions"', false);
        $response->assertSee('aria-label="More actions"', false);
        $response->assertSee('circle cx="4" cy="10"', false);

        // Check scroll buttons have SVG arrows
        $response->assertSee('aria-label="Scroll Up"', false);
        $response->assertSee('aria-label="Scroll Down"', false);
        $response->assertSee('M5 15l7-7 7 7', false);
        $response->assertSee('M19 9l-7 7-7-7', false);

        // Check CSS rules in layout contain exemption and display overrides for action buttons
        $response->assertSee('.ld-outline svg', false);
        $response->assertSee('button[aria-label*="actions" i] svg', false);

        // Test as Staff (Employee)
        $responseStaff = $this->actingAs($staff)->get(route('leads.index'));
        $responseStaff->assertStatus(200);
        $responseStaff->assertSee('title="More actions"', false);
        $responseStaff->assertSee('circle cx="4" cy="10"', false);
    }

    public function test_quotations_index_renders_action_button_icons()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('quotations.index'));
        $response->assertStatus(200);

        $response->assertSee('title="More actions"', false);
        $response->assertSee('aria-label="More actions"', false);
        $response->assertSee('circle cx="4" cy="10"', false);
        $response->assertSee('aria-label="Scroll Up"', false);
        $response->assertSee('aria-label="Scroll Down"', false);
    }

    public function test_alerts_and_wages_and_attendance_render_action_button_icons()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Alerts index
        $responseAlerts = $this->actingAs($admin)->get(route('alerts.index'));
        $responseAlerts->assertStatus(200);
        $responseAlerts->assertSee('aria-label="More actions"', false);
        $responseAlerts->assertSee('circle cx="4" cy="10"', false);

        // Wages index
        $responseWages = $this->actingAs($admin)->get(route('wages.index'));
        $responseWages->assertStatus(200);
        $responseWages->assertSee('aria-label="More actions"', false);
        $responseWages->assertSee('circle cx="4" cy="10"', false);

        // Workers attendance index
        $responseAtt = $this->actingAs($admin)->get(route('workers.attendance.index'));
        $responseAtt->assertStatus(200);
        $responseAtt->assertSee('aria-label="More actions"', false);
        $responseAtt->assertSee('circle cx="4" cy="10"', false);
    }
}

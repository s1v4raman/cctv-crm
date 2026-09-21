<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\SiteSurvey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteSurveyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $technician;
    private User $customer;
    private Lead $lead;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->technician = User::factory()->create(['name' => 'Bob Miller', 'role' => 'technician']);

        $this->lead = Lead::create([
            'customer_name' => 'Sailesh Kumar Enterprise',
            'email' => 'sailesh@gmail.com',
            'phone' => '+91 98765 43210',
            'site_address' => 'Flat 402, Green Valley Apartments, Sector 18, Noida',
            'status' => 'won',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Sailesh',
            'email' => 'sailesh@gmail.com',
            'role' => 'customer',
            'lead_id' => $this->lead->id,
        ]);
    }

    public function test_admin_can_assign_site_survey_to_technician(): void
    {
        $response = $this->actingAs($this->admin)->post(route('site-surveys.store'), [
            'lead_id' => $this->lead->id,
            'surveyed_by' => $this->technician->id,
            'survey_date' => '2026-09-15',
            'site_address' => 'Flat 402, Green Valley Apartments, Sector 18, Noida',
            'contact_person' => 'Sailesh',
            'contact_phone' => '+91 98765 43210',
            'status' => 'pending',
            'visit_notes' => 'Customer requested pre-installation inspection for 4 cameras.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('site_surveys', [
            'lead_id' => $this->lead->id,
            'surveyed_by' => $this->technician->id,
            'status' => 'pending',
        ]);
    }

    public function test_technician_can_view_assigned_survey_and_complete_with_photos(): void
    {
        $survey = SiteSurvey::create([
            'lead_id' => $this->lead->id,
            'surveyed_by' => $this->technician->id,
            'survey_date' => '2026-09-15',
            'site_address' => 'Flat 402, Green Valley Apartments, Sector 18, Noida',
            'contact_person' => 'Sailesh',
            'contact_phone' => '+91 98765 43210',
            'status' => 'pending',
        ]);

        // 1. Technician sees survey on dashboard
        $dashResponse = $this->actingAs($this->technician)->get(route('technician.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Site Survey: Sailesh Kumar Enterprise');

        // 2. Technician completes survey with photo uploads
        $photo1 = UploadedFile::fake()->create('entrance_camera.jpg', 100, 'image/jpeg');
        $photo2 = UploadedFile::fake()->create('server_rack.jpg', 100, 'image/jpeg');

        $completeResponse = $this->actingAs($this->technician)->post(route('technician.surveys.complete', $survey), [
            'camera_count_recommended' => 6,
            'dvr_location' => 'Server Room Rack #1',
            'cable_length_estimate' => 120.5,
            'power_availability' => '230V AC available, connected to 2KVA UPS',
            'challenges' => 'False ceiling in corridor requires conduit tubing',
            'visit_notes' => 'Recommended 4x IP Dome cameras for indoor and 2x IP Bullet cameras for parking.',
            'photos' => [$photo1, $photo2],
            'captions' => ['Main Entrance Blind Spot', 'Server Room NVR Location'],
        ]);

        $completeResponse->assertRedirect(route('technician.dashboard'));
        $completeResponse->assertSessionHas('status');

        $survey->refresh();
        $this->assertEquals('completed', $survey->status);
        $this->assertEquals(6, $survey->camera_count_recommended);
        $this->assertEquals(120.5, $survey->cable_length_estimate);
        $this->assertEquals(2, $survey->photos()->count());

        // 3. Admin can view completed survey and photo gallery
        $adminViewResponse = $this->actingAs($this->admin)->get(route('site-surveys.show', $survey));
        $adminViewResponse->assertStatus(200);
        $adminViewResponse->assertSee('Bob Miller');
        $adminViewResponse->assertSee('Server Room Rack #1');

        // 4. Customer sees survey report and photos on their portal
        $customerPortalResponse = $this->actingAs($this->customer)->get(route('portal.surveys.show', $survey));
        $customerPortalResponse->assertStatus(200);
        $customerPortalResponse->assertSee('On-Site Inspection Completed');
        $customerPortalResponse->assertSee('6 Units');
        $customerPortalResponse->assertSee('Bob Miller');
    }
}

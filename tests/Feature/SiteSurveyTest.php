<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\SiteSurvey;
use App\Models\SiteSurveyPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteSurveyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $customer;
    private User $technician;
    private Lead $lead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin      = User::factory()->create(['role' => 'admin']);
        $this->staff      = User::factory()->create(['role' => 'staff']);
        $this->customer   = User::factory()->create(['role' => 'customer']);
        $this->technician = User::factory()->create(['role' => 'technician']);

        $this->lead = Lead::create([
            'customer_name' => 'Survey Client',
            'phone'         => '9876543210',
            'status'        => 'new',
        ]);
    }

    // ── Access Control ──────────────────────────────────────────────────────────

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('site-surveys.index'))->assertRedirect(route('login'));
    }

    public function test_customers_and_technicians_cannot_access_site_surveys(): void
    {
        $this->actingAs($this->customer);
        $this->get(route('site-surveys.index'))->assertStatus(403);

        $this->actingAs($this->technician);
        $this->get(route('site-surveys.index'))->assertStatus(403);
    }

    public function test_staff_and_admin_can_access_site_surveys(): void
    {
        $this->actingAs($this->staff);
        $this->get(route('site-surveys.index'))->assertOk();
        $this->get(route('site-surveys.create'))->assertOk();

        $this->actingAs($this->admin);
        $this->get(route('site-surveys.index'))->assertOk();
    }

    // ── Create Survey ───────────────────────────────────────────────────────────

    public function test_staff_can_schedule_a_site_survey(): void
    {
        $this->actingAs($this->staff);

        $response = $this->post(route('site-surveys.store'), [
            'lead_id'        => $this->lead->id,
            'surveyed_by'    => $this->technician->id,
            'survey_date'    => '2026-09-01',
            'status'         => 'pending',
            'site_address'   => '123 Test Street, Chennai',
            'contact_person' => 'Ravi Kumar',
            'contact_phone'  => '9876543210',
            'visit_notes'    => 'Customer requested pre-installation inspection for 4 cameras.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('site_surveys', [
            'lead_id'        => $this->lead->id,
            'surveyed_by'    => $this->technician->id,
            'status'         => 'pending',
            'contact_person' => 'Ravi Kumar',
        ]);
    }

    public function test_creating_survey_requires_lead_and_date(): void
    {
        $this->actingAs($this->staff);

        $response = $this->post(route('site-surveys.store'), [
            'lead_id'     => '',
            'survey_date' => '',
            'status'      => 'pending',
        ]);

        $response->assertSessionHasErrors(['lead_id', 'survey_date']);
    }

    // ── Photo Upload via Technician ─────────────────────────────────────────────

    public function test_technician_uploads_photos_on_survey_completion(): void
    {
        Storage::fake('public');

        $survey = SiteSurvey::create([
            'lead_id'     => $this->lead->id,
            'surveyed_by' => $this->technician->id,
            'survey_date' => '2026-09-01',
            'status'      => 'pending',
        ]);

        $this->actingAs($this->technician);

        $this->post(route('technician.surveys.complete', $survey), [
            'camera_count_recommended' => 4,
            'dvr_location'             => 'Server Room Rack',
            'cable_length_estimate'    => 80.5,
            'visit_notes'              => 'Completed on-site inspection.',
            'photos'                   => [
                UploadedFile::fake()->create('camera_spot.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('entrance.jpg', 100, 'image/jpeg'),
            ],
            'captions'                 => ['Camera spot', 'Entrance view'],
        ]);

        $survey->refresh();
        $this->assertEquals('completed', $survey->status);
        $this->assertCount(2, $survey->photos);

        foreach ($survey->photos as $photo) {
            Storage::disk('public')->assertExists('site-surveys/' . $photo->filename);
        }
    }

    // ── Delete Survey ───────────────────────────────────────────────────────────

    public function test_deleting_survey_removes_photos_from_disk(): void
    {
        Storage::fake('public');

        $survey = SiteSurvey::create([
            'lead_id'     => $this->lead->id,
            'surveyed_by' => $this->technician->id,
            'survey_date' => '2026-09-01',
            'status'      => 'completed',
        ]);

        $photoFile = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');
        $filename = uniqid('survey_') . '.jpg';
        $photoFile->storeAs('site-surveys', $filename, 'public');

        $photo = $survey->photos()->create([
            'filename'      => $filename,
            'original_name' => 'test.jpg',
        ]);

        Storage::disk('public')->assertExists('site-surveys/' . $photo->filename);

        $this->actingAs($this->admin)->delete(route('site-surveys.destroy', $survey));

        $this->assertDatabaseMissing('site_surveys', ['id' => $survey->id]);
        $this->assertDatabaseMissing('site_survey_photos', ['id' => $photo->id]);
        Storage::disk('public')->assertMissing('site-surveys/' . $photo->filename);
    }

    // ── Delete Individual Photo ─────────────────────────────────────────────────

    public function test_individual_photo_can_be_deleted(): void
    {
        Storage::fake('public');

        $survey = SiteSurvey::create([
            'lead_id'     => $this->lead->id,
            'surveyed_by' => $this->technician->id,
            'survey_date' => '2026-09-01',
            'status'      => 'completed',
        ]);

        $photoFile = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');
        $filename = uniqid('survey_') . '.jpg';
        $photoFile->storeAs('site-surveys', $filename, 'public');

        $photo = $survey->photos()->create([
            'filename'      => $filename,
            'original_name' => 'test.jpg',
        ]);

        $this->actingAs($this->admin)->delete(route('site-surveys.photos.destroy', $photo));

        $this->assertDatabaseMissing('site_survey_photos', ['id' => $photo->id]);
        Storage::disk('public')->assertMissing('site-surveys/' . $photo->filename);
    }

    // ── Update Survey Schedule ──────────────────────────────────────────────────

    public function test_survey_schedule_can_be_updated(): void
    {
        $this->actingAs($this->staff);

        $survey = SiteSurvey::create([
            'lead_id'        => $this->lead->id,
            'surveyed_by'    => $this->staff->id,
            'survey_date'    => '2026-09-01',
            'status'         => 'pending',
            'contact_person' => 'Ravi',
        ]);

        $response = $this->put(route('site-surveys.update', $survey), [
            'lead_id'        => $this->lead->id,
            'surveyed_by'    => $this->technician->id,
            'survey_date'    => '2026-09-02',
            'status'         => 'pending',
            'contact_person' => 'Ravi Kumar (Updated)',
            'visit_notes'    => 'Updated instructions for field engineer.',
        ]);

        $response->assertRedirect(route('site-surveys.show', $survey));

        $this->assertDatabaseHas('site_surveys', [
            'id'             => $survey->id,
            'surveyed_by'    => $this->technician->id,
            'contact_person' => 'Ravi Kumar (Updated)',
            'visit_notes'    => 'Updated instructions for field engineer.',
        ]);
    }

    public function test_lead_show_page_displays_associated_site_surveys(): void
    {
        $this->actingAs($this->staff);

        SiteSurvey::create([
            'lead_id'                  => $this->lead->id,
            'surveyed_by'              => $this->staff->id,
            'survey_date'              => '2026-09-01',
            'status'                   => 'completed',
            'camera_count_recommended' => 6,
        ]);

        $response = $this->get(route('leads.show', $this->lead));
        $response->assertOk();
        $response->assertSee('Site Surveys');
        $response->assertSee('01 Sep 2026');
        $response->assertSee('6');
    }
}

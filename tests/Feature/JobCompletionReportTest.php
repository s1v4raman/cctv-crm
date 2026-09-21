<?php

namespace Tests\Feature;

use App\Models\AmcContract;
use App\Models\AmcVisit;
use App\Models\InstallationJob;
use App\Models\JobCompletionReport;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\ServiceTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobCompletionReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $technician;
    private User $otherTechnician;
    private Lead $lead;
    private Quotation $quotation;
    private InstallationJob $job;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->technician = User::factory()->create([
            'name' => 'Rajesh Sharma',
            'role' => 'technician',
        ]);

        $this->otherTechnician = User::factory()->create([
            'name' => 'Amit Patel',
            'role' => 'technician',
        ]);

        $this->lead = Lead::create([
            'customer_name' => 'Tech Park Mumbai',
            'email'         => 'admin@techpark.in',
            'phone'         => '9876543210',
            'site_address'  => 'Building 4, SEZ Zone, Andheri East, Mumbai',
            'status'        => 'won',
        ]);

        $this->quotation = Quotation::create([
            'lead_id'        => $this->lead->id,
            'quotation_no'   => 'QT-2026-0001',
            'quotation_date' => now(),
            'subtotal'       => 50000,
            'tax_percent'    => 18,
            'tax_amount'     => 9000,
            'total'          => 59000,
            'status'         => 'accepted',
        ]);

        $this->job = InstallationJob::create([
            'quotation_id'           => $this->quotation->id,
            'job_no'                 => 'JOB-2026-0001',
            'scheduled_date'         => now(),
            'assigned_technician_id' => $this->technician->id,
            'status'                 => 'assigned',
        ]);
    }

    public function test_technician_can_view_job_signoff_wizard(): void
    {
        $response = $this->actingAs($this->technician)
            ->get(route('jcr.create-job', $this->job));

        $response->assertOk();
        $response->assertSee('Digital Job Sign-Off', false);
        $response->assertSee('Tech Park Mumbai');
        $response->assertSee('Quality Assurance');
    }

    public function test_other_technician_cannot_access_unassigned_job_signoff(): void
    {
        $response = $this->actingAs($this->otherTechnician)
            ->get(route('jcr.create-job', $this->job));

        $response->assertStatus(403);
    }

    public function test_technician_can_submit_jcr_for_installation_job(): void
    {
        Storage::fake('public');

        $signatureBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $photo = UploadedFile::fake()->create('dvr_rack.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->technician)->post(route('jcr.store'), [
            'installation_job_id'        => $this->job->id,
            'lead_id'                    => $this->lead->id,
            'signer_name'                => 'Mr. Arvind Verma',
            'signer_designation'         => 'Facility Director',
            'signer_phone'               => '9876543210',
            'customer_rating'            => 5,
            'customer_feedback'          => 'Flawless camera installation and crisp night vision feeds.',
            'customer_signature'         => $signatureBase64,
            'all_cameras_positioned'     => '1',
            'recording_configured'       => '1',
            'remote_mobile_app_setup'    => '1',
            'power_backup_tested'        => '1',
            'cables_dressed_and_trunked' => '1',
            'client_training_completed'  => '1',
            'work_area_cleaned'          => '1',
            'warranty_card_handed'       => '1',
            'work_summary'               => 'Mounted 8x 4K IP cameras with NVR and Hik-Connect mobile app configured.',
            'photos'                     => [$photo],
            'photo_types'                => ['rack_setup'],
            'photo_captions'             => ['Main NVR Server Rack Setup'],
        ]);

        $response->assertRedirect();

        // Verify JCR record
        $this->assertDatabaseHas('job_completion_reports', [
            'installation_job_id' => $this->job->id,
            'lead_id'             => $this->lead->id,
            'technician_id'       => $this->technician->id,
            'signer_name'         => 'Mr. Arvind Verma',
            'signer_designation'  => 'Facility Director',
            'customer_rating'     => 5,
            'status'              => 'signed',
        ]);

        // Verify Installation Job auto-updated to completed
        $this->assertEquals('completed', $this->job->fresh()->status);

        // Verify photo record
        $this->assertDatabaseHas('job_completion_photos', [
            'photo_type' => 'rack_setup',
            'caption'    => 'Main NVR Server Rack Setup',
        ]);
    }

    public function test_submitting_jcr_for_service_ticket_resolves_the_ticket(): void
    {
        $ticket = ServiceTicket::create([
            'ticket_no'              => 'TKT-2026-0001',
            'lead_id'                => $this->lead->id,
            'assigned_technician_id' => $this->technician->id,
            'created_by_id'          => $this->admin->id,
            'title'                  => 'Camera 3 Video Loss',
            'description'            => 'Video feed flickering intermittently',
            'issue_type'             => 'camera_offline',
            'priority'               => 'high',
            'status'                 => 'in_progress',
        ]);

        $signatureBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($this->technician)->post(route('jcr.store'), [
            'service_ticket_id'          => $ticket->id,
            'lead_id'                    => $this->lead->id,
            'signer_name'                => 'Security Officer',
            'customer_rating'            => 5,
            'customer_signature'         => $signatureBase64,
            'all_cameras_positioned'     => '1',
            'recording_configured'       => '1',
            'remote_mobile_app_setup'    => '1',
            'power_backup_tested'        => '1',
            'cables_dressed_and_trunked' => '1',
            'client_training_completed'  => '1',
            'work_area_cleaned'          => '1',
            'warranty_card_handed'       => '1',
            'work_summary'               => 'Replaced faulty BNC connector and 12V adapter. Video restored.',
        ]);

        $response->assertRedirect();

        $freshTicket = $ticket->fresh();
        $this->assertEquals('resolved', $freshTicket->status);
        $this->assertNotNull($freshTicket->resolved_at);
        $this->assertEquals('Replaced faulty BNC connector and 12V adapter. Video restored.', $freshTicket->resolution_notes);
    }

    public function test_submitting_jcr_for_amc_visit_completes_the_visit(): void
    {
        $contract = AmcContract::create([
            'contract_no' => 'AMC-2026-0001',
            'lead_id'     => $this->lead->id,
            'start_date'  => now()->subMonths(1),
            'end_date'    => now()->addMonths(11),
            'frequency'   => 'quarterly',
            'value'       => 24000,
            'status'      => 'active',
        ]);

        $visit = AmcVisit::create([
            'amc_contract_id'        => $contract->id,
            'assigned_technician_id' => $this->technician->id,
            'scheduled_date'         => now(),
            'status'                 => 'pending',
        ]);

        $signatureBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($this->technician)->post(route('jcr.store'), [
            'amc_visit_id'               => $visit->id,
            'lead_id'                    => $this->lead->id,
            'signer_name'                => 'Site Admin',
            'customer_rating'            => 5,
            'customer_signature'         => $signatureBase64,
            'all_cameras_positioned'     => '1',
            'recording_configured'       => '1',
            'remote_mobile_app_setup'    => '1',
            'power_backup_tested'        => '1',
            'cables_dressed_and_trunked' => '1',
            'client_training_completed'  => '1',
            'work_area_cleaned'          => '1',
            'warranty_card_handed'       => '1',
            'work_summary'               => 'Full quarterly lens cleaning, voltage checks, and HDD health scan.',
        ]);

        $response->assertRedirect();

        $freshVisit = $visit->fresh();
        $this->assertEquals('completed', $freshVisit->status);
        $this->assertNotNull($freshVisit->completed_at);
    }

    public function test_jcr_index_and_show_views_render_for_internal_users(): void
    {
        $report = JobCompletionReport::create([
            'report_no'           => 'JCR-2026-0001',
            'installation_job_id' => $this->job->id,
            'lead_id'             => $this->lead->id,
            'technician_id'       => $this->technician->id,
            'completion_date'     => now(),
            'signer_name'         => 'Mr. Arvind Verma',
            'customer_rating'     => 5,
            'customer_signature'  => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'status'              => 'signed',
        ]);

        $indexResponse = $this->actingAs($this->admin)->get(route('jcr.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('JCR-2026-0001');
        $indexResponse->assertSee('Tech Park Mumbai');

        $showResponse = $this->actingAs($this->admin)->get(route('jcr.show', $report));
        $showResponse->assertOk();
        $showResponse->assertSee('JCR-2026-0001');
        $showResponse->assertSee('Mr. Arvind Verma');
    }

    public function test_technician_can_view_jcr_show_and_download_pdf(): void
    {
        $report = JobCompletionReport::create([
            'report_no'           => 'JCR-2026-0001',
            'installation_job_id' => $this->job->id,
            'lead_id'             => $this->lead->id,
            'technician_id'       => $this->technician->id,
            'completion_date'     => now(),
            'signer_name'         => 'Mr. Arvind Verma',
            'customer_rating'     => 5,
            'customer_signature'  => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'status'              => 'signed',
        ]);

        $showResponse = $this->actingAs($this->technician)->get(route('jcr.show', $report));
        $showResponse->assertOk();
        $showResponse->assertSee('JCR-2026-0001');

        $pdfResponse = $this->actingAs($this->technician)->get(route('jcr.download-pdf', $report));
        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_customer_can_view_own_jcr_and_cannot_view_others(): void
    {
        $report = JobCompletionReport::create([
            'report_no'           => 'JCR-2026-0001',
            'installation_job_id' => $this->job->id,
            'lead_id'             => $this->lead->id,
            'technician_id'       => $this->technician->id,
            'completion_date'     => now(),
            'signer_name'         => 'Mr. Arvind Verma',
            'customer_rating'     => 5,
            'customer_signature'  => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'status'              => 'signed',
        ]);

        $customer = User::factory()->create([
            'role'    => 'customer',
            'lead_id' => $this->lead->id,
            'email'   => 'customer@techpark.in',
        ]);

        $otherCustomer = User::factory()->create([
            'role'  => 'customer',
            'email' => 'other@random.in',
        ]);

        // Own customer can access
        $response = $this->actingAs($customer)->get(route('jcr.show', $report));
        $response->assertOk();

        // Other customer gets 403
        $forbiddenResponse = $this->actingAs($otherCustomer)->get(route('jcr.show', $report));
        $forbiddenResponse->assertStatus(403);
    }
}

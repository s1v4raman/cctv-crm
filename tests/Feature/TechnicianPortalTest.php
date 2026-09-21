<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\InstallationJob;
use App\Models\AmcContract;
use App\Models\AmcVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicianPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $technician;
    private User $otherTechnician;
    private User $staff;
    private User $customer;
    private InstallationJob $job;
    private AmcVisit $visit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin           = User::factory()->create(['role' => 'admin']);
        $this->technician      = User::factory()->create(['role' => 'technician']);
        $this->otherTechnician = User::factory()->create(['role' => 'technician']);
        $this->staff           = User::factory()->create(['role' => 'staff']);
        $this->customer        = User::factory()->create(['role' => 'customer']);

        $lead = Lead::create([
            'customer_name' => 'Test Client',
            'phone' => '9876543210',
            'status' => 'won',
        ]);

        $quotation = Quotation::create([
            'lead_id'        => $lead->id,
            'quotation_no'   => 'QT-TEST-001',
            'quotation_date' => now(),
            'subtotal'       => 5000,
            'discount'       => 0,
            'tax_percent'    => 18,
            'tax_amount'     => 900,
            'total'          => 5900,
            'status'         => 'accepted',
        ]);

        $this->job = InstallationJob::create([
            'quotation_id'           => $quotation->id,
            'job_no'                 => 'JOB-001',
            'status'                 => 'pending',
            'assigned_technician_id' => $this->technician->id,
        ]);

        $contract = AmcContract::create([
            'lead_id'      => $lead->id,
            'contract_no'  => 'AMC-001',
            'start_date'   => now(),
            'end_date'     => now()->addYear(),
            'frequency'    => 'quarterly',
            'value'        => 12000,
            'status'       => 'active',
        ]);

        $this->visit = AmcVisit::create([
            'amc_contract_id'        => $contract->id,
            'assigned_technician_id' => $this->technician->id,
            'scheduled_date'         => now()->addDays(7),
            'status'                 => 'pending',
        ]);
    }

    // ── Access Control ─────────────────────────────────────────────────────────

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('technician.dashboard'))->assertRedirect(route('login'));
    }

    public function test_staff_and_customer_cannot_access_technician_portal(): void
    {
        $this->actingAs($this->staff);
        $this->get(route('technician.dashboard'))->assertStatus(403);

        $this->actingAs($this->customer);
        $this->get(route('technician.dashboard'))->assertStatus(403);
    }

    public function test_technician_can_access_dashboard(): void
    {
        $this->actingAs($this->technician);
        $this->get(route('technician.dashboard'))->assertOk();
    }

    public function test_admin_can_also_access_technician_portal(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('technician.dashboard'))->assertOk();
    }

    // ── Dashboard Data Isolation ────────────────────────────────────────────────

    public function test_technician_only_sees_their_own_assigned_jobs(): void
    {
        $this->actingAs($this->technician);
        $response = $this->get(route('technician.dashboard'));
        $response->assertOk();

        // Sees own job
        $response->assertViewHas('activeJobs', function ($jobs) {
            return $jobs->contains('id', $this->job->id);
        });
    }

    // ── Job Status Update ──────────────────────────────────────────────────────

    public function test_technician_can_update_assigned_job_status(): void
    {
        $this->actingAs($this->technician);

        $response = $this->post(route('technician.jobs.updateStatus', $this->job), [
            'status' => 'completed',
            'notes'  => 'All cameras installed and tested.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('installation_jobs', [
            'id'                 => $this->job->id,
            'status'             => 'completed',
            'installation_notes' => 'All cameras installed and tested.',
        ]);
    }

    public function test_technician_cannot_update_job_not_assigned_to_them(): void
    {
        $this->actingAs($this->otherTechnician);

        $response = $this->post(route('technician.jobs.updateStatus', $this->job), [
            'status' => 'completed',
            'notes'  => 'Unauthorized update.',
        ]);

        $response->assertStatus(403);
    }

    // ── AMC Visit Completion ───────────────────────────────────────────────────

    public function test_technician_can_complete_assigned_amc_visit(): void
    {
        $this->actingAs($this->technician);

        $response = $this->post(route('technician.amc-visits.complete', $this->visit), [
            'completion_notes' => 'Cameras cleaned and checked. All working.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('amc_visits', [
            'id'               => $this->visit->id,
            'status'           => 'completed',
            'completion_notes' => 'Cameras cleaned and checked. All working.',
        ]);
    }

    public function test_technician_cannot_complete_visit_not_assigned_to_them(): void
    {
        $this->actingAs($this->otherTechnician);

        $response = $this->post(route('technician.amc-visits.complete', $this->visit), [
            'completion_notes' => 'Unauthorized completion.',
        ]);

        $response->assertStatus(403);
    }

    public function test_completing_amc_visit_requires_notes(): void
    {
        $this->actingAs($this->technician);

        $response = $this->post(route('technician.amc-visits.complete', $this->visit), [
            'completion_notes' => '', // empty
        ]);

        $response->assertSessionHasErrors('completion_notes');
    }
}

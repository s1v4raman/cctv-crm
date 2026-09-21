<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use App\Models\AmcContract;
use App\Models\AmcVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmcContractTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $technician;
    private User $customer;
    private Lead $lead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->technician = User::factory()->create(['role' => 'technician']);
        $this->customer = User::factory()->create(['role' => 'customer']);

        $this->lead = Lead::create([
            'customer_name' => 'Bob Customer',
            'phone' => '9876543210',
            'status' => 'won',
        ]);
    }

    public function test_guests_and_customers_cannot_access_amc(): void
    {
        // Guest
        $this->get(route('amcs.index'))->assertRedirect(route('login'));

        // Customer
        $this->actingAs($this->customer);
        $this->get(route('amcs.index'))->assertStatus(403);
    }

    public function test_staff_and_admin_can_access_amc(): void
    {
        $this->actingAs($this->staff);
        $this->get(route('amcs.index'))->assertOk();
        $this->get(route('amcs.create'))->assertOk();
    }

    public function test_creating_amc_generates_correct_periodic_visits(): void
    {
        $this->actingAs($this->staff);

        $response = $this->post(route('amcs.store'), [
            'lead_id' => $this->lead->id,
            'start_date' => '2026-08-26',
            'end_date' => '2027-08-26', // exactly 1 year
            'value' => 12000.00,
            'frequency' => 'quarterly', // generates: 2026-08-26, 2026-11-26, 2027-02-26, 2027-05-26, 2027-08-26 (5 visits)
            'notes' => 'Test AMC scope',
        ]);

        $contract = AmcContract::first();
        $this->assertNotNull($contract);
        $response->assertRedirect(route('amcs.show', $contract));

        $this->assertDatabaseHas('amc_contracts', [
            'lead_id' => $this->lead->id,
            'value' => 12000.00,
            'frequency' => 'quarterly',
            'status' => 'active',
        ]);

        $this->assertEquals(5, $contract->visits()->count());
    }

    public function test_technician_assignment_to_visit(): void
    {
        $contract = AmcContract::create([
            'lead_id' => $this->lead->id,
            'contract_no' => 'AMC-TEST-001',
            'start_date' => '2026-08-26',
            'end_date' => '2027-08-26',
            'value' => 5000.00,
            'frequency' => 'annually',
            'status' => 'active',
        ]);

        $visit = $contract->visits()->create([
            'scheduled_date' => '2026-08-26',
            'status' => 'pending',
        ]);

        $this->actingAs($this->staff);

        $response = $this->post(route('amc-visits.assign', $visit), [
            'assigned_technician_id' => $this->technician->id,
        ]);

        $response->assertRedirect();
        $visit->refresh();
        $this->assertEquals($this->technician->id, $visit->assigned_technician_id);
    }

    public function test_visit_completion_logs_notes(): void
    {
        $contract = AmcContract::create([
            'lead_id' => $this->lead->id,
            'contract_no' => 'AMC-TEST-002',
            'start_date' => '2026-08-26',
            'end_date' => '2026-11-26',
            'value' => 2000.00,
            'frequency' => 'quarterly',
            'status' => 'active',
        ]);

        $visit = $contract->visits()->create([
            'scheduled_date' => '2026-08-26',
            'status' => 'pending',
        ]);

        $this->actingAs($this->staff);

        $response = $this->post(route('amc-visits.complete', $visit), [
            'completion_notes' => 'Everything checked and operational.',
        ]);

        $response->assertRedirect();
        $visit->refresh();
        $this->assertEquals('completed', $visit->status);
        $this->assertEquals('Everything checked and operational.', $visit->completion_notes);
        $this->assertNotNull($visit->completed_at);
    }

    public function test_only_admin_can_delete_contract(): void
    {
        $contract = AmcContract::create([
            'lead_id' => $this->lead->id,
            'contract_no' => 'AMC-TEST-003',
            'start_date' => '2026-08-26',
            'end_date' => '2026-11-26',
            'value' => 2000.00,
            'frequency' => 'quarterly',
            'status' => 'active',
        ]);

        // Staff
        $this->actingAs($this->staff);
        $this->delete(route('amcs.destroy', $contract))->assertStatus(403);

        // Admin
        $this->actingAs($this->admin);
        $response = $this->delete(route('amcs.destroy', $contract));
        $response->assertRedirect(route('amcs.index'));
        $this->assertDatabaseMissing('amc_contracts', ['id' => $contract->id]);
    }
}

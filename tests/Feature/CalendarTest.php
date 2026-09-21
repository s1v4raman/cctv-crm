<?php

namespace Tests\Feature;

use App\Models\AmcContract;
use App\Models\AmcVisit;
use App\Models\InstallationJob;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\ServiceTicket;
use App\Models\SiteSurvey;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $customer;
    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->technician = User::factory()->create(['role' => 'technician']);
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('calendar.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('calendar.events'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_and_staff_can_access_calendar_page(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('calendar.index'));
        $response->assertOk();
        $response->assertSee('Operations Calendar');
        $response->assertSee('Unified Schedule');

        $this->actingAs($this->staff);
        $response = $this->get(route('calendar.index'));
        $response->assertOk();
        $response->assertSee('Operations Calendar');
    }

    public function test_customer_cannot_access_calendar(): void
    {
        $this->actingAs($this->customer);
        $response = $this->get(route('calendar.index'));
        $response->assertStatus(403);
    }

    public function test_calendar_aggregates_events_across_models(): void
    {
        $this->actingAs($this->admin);

        // 1. Lead & Site Survey
        $lead = Lead::create([
            'customer_name' => 'John Enterprise',
            'site_address'  => '123 Tech Park, Bangalore',
            'phone'         => '9876543210',
            'status'        => 'new',
        ]);
        $survey = SiteSurvey::create([
            'lead_id'                   => $lead->id,
            'surveyed_by'               => $this->technician->id,
            'survey_date'               => now()->toDateString(),
            'site_address'              => '123 Tech Park, Bangalore',
            'contact_phone'             => '9876543210',
            'camera_count_recommended'  => 8,
            'status'                    => 'pending',
        ]);

        // 2. Installation Job
        $quotation = Quotation::create([
            'lead_id'      => $lead->id,
            'quotation_no' => 'QT-20260918-0001',
            'status'       => 'accepted',
        ]);
        $job = InstallationJob::create([
            'quotation_id'           => $quotation->id,
            'job_no'                 => 'JOB-9999',
            'scheduled_date'         => now()->toDateString(),
            'assigned_technician_id' => $this->technician->id,
            'status'                 => 'scheduled',
            'installation_notes'     => 'Install 8 IP cameras and NVR',
        ]);

        // 3. AMC Routine Visit
        $contract = AmcContract::create([
            'lead_id'       => $lead->id,
            'contract_no'   => 'AMC-8888',
            'frequency'     => 'quarterly',
            'start_date'    => now()->startOfYear(),
            'end_date'      => now()->endOfYear(),
            'status'        => 'active',
            'value'         => 25000.00,
        ]);
        $visit = AmcVisit::create([
            'amc_contract_id'        => $contract->id,
            'assigned_technician_id' => $this->technician->id,
            'scheduled_date'         => now()->toDateString(),
            'status'                 => 'pending',
            'completion_notes'       => 'Quarterly sensor check',
        ]);

        // 4. Service Ticket
        $ticket = ServiceTicket::create([
            'ticket_no'              => 'TCK-7777',
            'lead_id'                => $lead->id,
            'assigned_technician_id' => $this->technician->id,
            'created_by_id'          => $this->admin->id,
            'title'                  => 'Main Gate Camera Offline',
            'issue_type'             => 'camera_offline',
            'priority'               => 'high',
            'status'                 => 'assigned',
            'scheduled_date'         => now()->toDateString(),
            'description'            => 'Video feed flickering intermittently',
        ]);

        // Test Index view with events
        $response = $this->get(route('calendar.index'));
        $response->assertOk();
        $response->assertSee('John Enterprise');

        // Test JSON API endpoint
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();
        $jsonResponse = $this->getJson(route('calendar.events', ['start' => $start, 'end' => $end]));
        $jsonResponse->assertOk();

        $data = $jsonResponse->json();
        $this->assertCount(4, $data);

        $types = collect($data)->pluck('type')->toArray();
        $this->assertContains('survey', $types);
        $this->assertContains('job', $types);
        $this->assertContains('amc', $types);
        $this->assertContains('ticket', $types);

        // Verify structure of an event item
        $jobEvent = collect($data)->firstWhere('type', 'job');
        $this->assertStringContainsString('JOB-9999', $jobEvent['title']);
        $this->assertEquals('John Enterprise', $jobEvent['customer']);
        $this->assertEquals($this->technician->name, $jobEvent['tech']);
        $this->assertEquals('blue', $jobEvent['color']);
    }
}

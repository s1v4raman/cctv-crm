<?php

namespace Tests\Feature;

use App\Models\InstallationJob;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_installation_job_details_page_loads_with_technicians(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $tech = User::factory()->create(['name' => 'John Tech', 'role' => 'technician']);

        $lead = Lead::create([
            'customer_name' => 'Alice Client',
            'phone' => '1234567890',
            'status' => 'won',
        ]);

        $quotation = Quotation::create([
            'lead_id' => $lead->id,
            'quotation_no' => 'QT-20260825-0001',
            'status' => 'accepted',
        ]);

        $job = InstallationJob::create([
            'quotation_id' => $quotation->id,
            'job_no' => 'JOB-20260825-0001',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('jobs.show', $job));

        $response->assertOk();
        $response->assertSee('Alice Client');
        $response->assertSee('John Tech');
        $response->assertSee('Select Technician...');
    }

    public function test_can_assign_technician_to_job(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $tech = User::factory()->create(['name' => 'John Tech', 'role' => 'technician']);

        $lead = Lead::create([
            'customer_name' => 'Alice Client',
            'phone' => '1234567890',
            'status' => 'won',
        ]);

        $quotation = Quotation::create([
            'lead_id' => $lead->id,
            'quotation_no' => 'QT-20260825-0001',
            'status' => 'accepted',
        ]);

        $job = InstallationJob::create([
            'quotation_id' => $quotation->id,
            'job_no' => 'JOB-20260825-0001',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('jobs.update', $job), [
                'assigned_technician_id' => $tech->id,
                'status' => 'pending', // Automatic workflow should escalate to 'assigned'
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $job->refresh();
        $this->assertEquals($tech->id, $job->assigned_technician_id);
        $this->assertEquals('assigned', $job->status);
    }

    public function test_cannot_assign_non_existent_technician(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $lead = Lead::create([
            'customer_name' => 'Alice Client',
            'phone' => '1234567890',
            'status' => 'won',
        ]);

        $quotation = Quotation::create([
            'lead_id' => $lead->id,
            'quotation_no' => 'QT-20260825-0001',
            'status' => 'accepted',
        ]);

        $job = InstallationJob::create([
            'quotation_id' => $quotation->id,
            'job_no' => 'JOB-20260825-0001',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('jobs.update', $job), [
                'assigned_technician_id' => 99999, // invalid ID
                'status' => 'pending',
            ]);

        $response->assertSessionHasErrors('assigned_technician_id');
        $this->assertNull($job->refresh()->assigned_technician_id);
    }
}

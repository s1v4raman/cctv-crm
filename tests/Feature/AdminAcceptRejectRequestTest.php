<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\ServiceTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAcceptRejectRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $technician;
    private User $customer;
    private Lead $lead;
    private ServiceTicket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->ticket = ServiceTicket::create([
            'ticket_no' => 'TCK-202609-0099',
            'lead_id' => $this->lead->id,
            'created_by_id' => $this->customer->id,
            'title' => 'New CCTV Installation & Site Survey Request',
            'issue_type' => 'other',
            'priority' => 'high',
            'status' => 'open',
            'description' => 'Need 4 high clarity IP cameras installed for building perimeter.',
            'billing_type' => 'billable',
        ]);
    }

    public function test_admin_can_accept_and_schedule_work_request(): void
    {
        $response = $this->actingAs($this->admin)->post(route('service-tickets.accept', $this->ticket), [
            'assigned_technician_id' => $this->technician->id,
            'scheduled_date' => '2026-09-10',
            'admin_notes' => 'Approved. Technician Bob will visit site on 10th September.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->ticket->refresh();
        $this->assertEquals('assigned', $this->ticket->status);
        $this->assertEquals($this->technician->id, $this->ticket->assigned_technician_id);
        $this->assertEquals('2026-09-10', $this->ticket->scheduled_date->format('Y-m-d'));
        $this->assertStringContainsString('Admin Approval Note: Approved.', $this->ticket->resolution_notes);

        // Customer sees accepted notification on portal ticket view
        $portalResponse = $this->actingAs($this->customer)->get(route('portal.tickets.show', $this->ticket));
        $portalResponse->assertSee('Work Request Accepted & Confirmed!', false);
        $portalResponse->assertSee('Bob Miller');
    }

    public function test_admin_can_reject_work_request_with_reason(): void
    {
        $response = $this->actingAs($this->admin)->post(route('service-tickets.reject', $this->ticket), [
            'rejection_reason' => 'Requested installation premises is currently out of our service region.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->ticket->refresh();
        $this->assertEquals('cancelled', $this->ticket->status);
        $this->assertStringContainsString('DECLINED BY ADMIN', $this->ticket->resolution_notes);
        $this->assertStringContainsString('out of our service region', $this->ticket->resolution_notes);

        // Customer sees declined notification and reason on portal ticket view
        $portalResponse = $this->actingAs($this->customer)->get(route('portal.tickets.show', $this->ticket));
        $portalResponse->assertStatus(200);
        $portalResponse->assertSee('Work Request Declined');
        $portalResponse->assertSee('out of our service region');
    }
}

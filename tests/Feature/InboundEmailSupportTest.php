<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\ServiceTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InboundEmailSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_inbound_email_webhook_creates_lead_and_ticket(): void
    {
        $payload = [
            'from'    => 'Rajesh Sharma <rajesh@techcorp.com>',
            'subject' => 'URGENT: Main entrance camera 3 offline and black screen',
            'body'    => 'Hello support, our main entrance 4K camera is showing black screen and no signal since this morning.',
            'phone'   => '+91 98765 43210',
        ];

        $response = $this->postJson(route('api.inbound-email'), $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'priority' => 'critical',
                'issue_type' => 'camera_offline',
            ]);

        $this->assertDatabaseHas('leads', [
            'email' => 'rajesh@techcorp.com',
            'customer_name' => 'Rajesh Sharma',
        ]);

        $this->assertDatabaseHas('service_tickets', [
            'title' => 'URGENT: Main entrance camera 3 offline and black screen',
            'issue_type' => 'camera_offline',
            'priority' => 'critical',
            'status' => 'open',
        ]);
    }

    public function test_inbound_email_matches_existing_customer_lead(): void
    {
        $existingLead = Lead::create([
            'customer_name' => 'Acme Logistics Site',
            'email' => 'support@acmelogistics.com',
            'phone' => '+91 99999 88888',
            'status' => 'won',
        ]);

        $payload = [
            'from'    => 'support@acmelogistics.com',
            'subject' => 'DVR NVR hard disk beeping alarm',
            'body'    => 'Our NVR recorder in server room has started making continuous beeping sound.',
        ];

        $response = $this->postJson(route('api.inbound-email'), $payload);

        $response->assertStatus(201);

        $ticket = ServiceTicket::where('title', 'DVR NVR hard disk beeping alarm')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals($existingLead->id, $ticket->lead_id);
        $this->assertEquals('dvr_nvr_beep', $ticket->issue_type);
    }

    public function test_admin_can_simulate_inbound_email_ticket(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('service-tickets.simulate-email'), [
            'customer_name' => 'Vikram Malhotra',
            'email' => 'vikram@factory.com',
            'phone' => '+91 91234 56789',
            'subject' => 'Cable damaged by rats near warehouse zone B',
            'body' => 'Rats have cut the CCTV signal cable on the outdoor perimeter.',
            'priority' => 'high',
            'issue_type' => 'cable_damaged',
        ]);

        $ticket = ServiceTicket::where('title', 'Cable damaged by rats near warehouse zone B')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals('cable_damaged', $ticket->issue_type);
        $this->assertEquals('high', $ticket->priority);

        $response->assertRedirect(route('service-tickets.show', $ticket));
    }
}

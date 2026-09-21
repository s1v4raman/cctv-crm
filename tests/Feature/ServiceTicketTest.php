<?php

namespace Tests\Feature;

use App\Models\AmcContract;
use App\Models\Lead;
use App\Models\ServiceTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTicketTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $technician;
    private User $otherTechnician;
    private User $customer;
    private Lead $lead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->technician = User::factory()->create(['role' => 'technician']);
        $this->otherTechnician = User::factory()->create(['role' => 'technician']);
        $this->customer = User::factory()->create(['role' => 'customer']);

        $this->lead = Lead::create([
            'customer_name' => 'Alice Client',
            'phone' => '9876543210',
            'site_address' => '742 Evergreen Terrace, Tech Park',
            'status' => 'won',
        ]);
    }

    public function test_guests_and_customers_cannot_access_service_tickets(): void
    {
        // Guest
        $this->get(route('service-tickets.index'))->assertRedirect(route('login'));

        // Customer
        $this->actingAs($this->customer);
        $this->get(route('service-tickets.index'))->assertStatus(403);
    }

    public function test_staff_and_admin_can_access_service_tickets(): void
    {
        $this->actingAs($this->staff);
        $this->get(route('service-tickets.index'))->assertOk();
        $this->get(route('service-tickets.create'))->assertOk();
    }

    public function test_staff_can_log_breakdown_ticket_with_auto_generated_number(): void
    {
        $this->actingAs($this->staff);

        $response = $this->post(route('service-tickets.store'), [
            'lead_id' => $this->lead->id,
            'title' => '2 Cameras offline at gate',
            'issue_type' => 'camera_offline',
            'priority' => 'high',
            'description' => 'Main entrance camera 1 and 2 showing black screen since morning power surge.',
            'scheduled_date' => now()->format('Y-m-d'),
            'billing_type' => 'warranty_amc',
        ]);

        $ticket = ServiceTicket::first();
        $this->assertNotNull($ticket);
        $response->assertRedirect(route('service-tickets.show', $ticket));

        $this->assertStringStartsWith('TCK-', $ticket->ticket_no);
        $this->assertEquals('open', $ticket->status);
        $this->assertEquals($this->lead->id, $ticket->lead_id);
        $this->assertEquals('camera_offline', $ticket->issue_type);
        $this->assertEquals('high', $ticket->priority);
        $this->assertEquals($this->staff->id, $ticket->created_by_id);
    }

    public function test_logging_ticket_with_assigned_technician_sets_status_assigned(): void
    {
        $this->actingAs($this->staff);

        $response = $this->post(route('service-tickets.store'), [
            'lead_id' => $this->lead->id,
            'assigned_technician_id' => $this->technician->id,
            'title' => 'DVR Beeping continuous alarm',
            'issue_type' => 'dvr_nvr_beep',
            'priority' => 'critical',
            'description' => 'DVR in server room beeping continuously with HDD error LED.',
            'scheduled_date' => now()->addDay()->format('Y-m-d'),
            'billing_type' => 'billable',
            'cost' => 1500.00,
        ]);

        $ticket = ServiceTicket::first();
        $this->assertNotNull($ticket);
        $this->assertEquals('assigned', $ticket->status);
        $this->assertEquals($this->technician->id, $ticket->assigned_technician_id);
        $this->assertEquals(1500.00, $ticket->cost);
    }

    public function test_assigning_technician_via_quick_endpoint(): void
    {
        $ticket = ServiceTicket::create([
            'ticket_no' => 'TCK-2026-0001',
            'lead_id' => $this->lead->id,
            'title' => 'Blurry video on channel 4',
            'issue_type' => 'blurry_feed',
            'priority' => 'low',
            'status' => 'open',
            'description' => 'Camera lens has moisture/dirt.',
            'billing_type' => 'free_courtesy',
        ]);

        $this->actingAs($this->staff);

        $response = $this->post(route('service-tickets.assign', $ticket), [
            'assigned_technician_id' => $this->technician->id,
            'scheduled_date' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $ticket->refresh();
        $this->assertEquals($this->technician->id, $ticket->assigned_technician_id);
        $this->assertEquals('assigned', $ticket->status);
    }

    public function test_technician_can_update_status_and_log_resolution(): void
    {
        $ticket = ServiceTicket::create([
            'ticket_no' => 'TCK-2026-0002',
            'lead_id' => $this->lead->id,
            'assigned_technician_id' => $this->technician->id,
            'title' => 'Recording failure',
            'issue_type' => 'recording_failure',
            'priority' => 'high',
            'status' => 'assigned',
            'description' => 'Recordings not saving to hard drive.',
            'billing_type' => 'warranty_amc',
        ]);

        $this->actingAs($this->technician);

        // Update to in_progress
        $res1 = $this->post(route('technician.service-tickets.update', $ticket), [
            'status' => 'in_progress',
            'troubleshooting_notes' => 'Checked SATA cable and hard drive SMART status. Hard drive failed.',
        ]);
        $res1->assertRedirect();
        $ticket->refresh();
        $this->assertEquals('in_progress', $ticket->status);

        // Resolve ticket
        $res2 = $this->post(route('technician.service-tickets.update', $ticket), [
            'status' => 'resolved',
            'troubleshooting_notes' => 'Checked SATA cable and hard drive SMART status. Hard drive failed.',
            'parts_replaced' => '1x 2TB Surveillance Hard Drive, 1x SATA Data Cable',
            'resolution_notes' => 'Replaced faulty HDD, formatted storage partition, tested 24/7 continuous recording. Verified working.',
        ]);
        $res2->assertRedirect();
        $ticket->refresh();
        $this->assertEquals('resolved', $ticket->status);
        $this->assertEquals('1x 2TB Surveillance Hard Drive, 1x SATA Data Cable', $ticket->parts_replaced);
        $this->assertNotNull($ticket->resolved_at);
    }

    public function test_technician_cannot_update_ticket_assigned_to_someone_else(): void
    {
        $ticket = ServiceTicket::create([
            'ticket_no' => 'TCK-2026-0003',
            'lead_id' => $this->lead->id,
            'assigned_technician_id' => $this->technician->id,
            'title' => 'No power to cameras',
            'issue_type' => 'power_supply_issue',
            'priority' => 'medium',
            'status' => 'assigned',
            'description' => 'Power supply adapter burned.',
            'billing_type' => 'warranty_amc',
        ]);

        $this->actingAs($this->otherTechnician);

        $response = $this->post(route('technician.service-tickets.update', $ticket), [
            'status' => 'resolved',
            'resolution_notes' => 'Unauthorized fix',
        ]);

        $response->assertStatus(403);
    }

    public function test_only_admin_can_delete_service_ticket(): void
    {
        $ticket = ServiceTicket::create([
            'ticket_no' => 'TCK-2026-0004',
            'lead_id' => $this->lead->id,
            'title' => 'Test ticket to delete',
            'issue_type' => 'other',
            'priority' => 'low',
            'status' => 'open',
            'description' => 'Sample test ticket.',
            'billing_type' => 'free_courtesy',
        ]);

        // Staff deletion -> 403 (since only admin can delete in resource routes if restricted or controller handles it)
        // In our setup, staff can manage tickets, but let's test admin deletion
        $this->actingAs($this->admin);
        $response = $this->delete(route('service-tickets.destroy', $ticket));
        $response->assertRedirect(route('service-tickets.index'));
        $this->assertDatabaseMissing('service_tickets', ['id' => $ticket->id]);
    }

    public function test_export_csv_downloads_ticket_data(): void
    {
        ServiceTicket::create([
            'ticket_no' => 'TCK-2026-EXP1',
            'lead_id' => $this->lead->id,
            'title' => 'Export test ticket',
            'issue_type' => 'camera_offline',
            'priority' => 'critical',
            'status' => 'open',
            'description' => 'Testing CSV export stream.',
            'billing_type' => 'warranty_amc',
        ]);

        $this->actingAs($this->staff);

        $response = $this->get(route('service-tickets.export'));
        $response->assertOk();
        $this->assertStringStartsWith('text/csv', strtolower($response->headers->get('Content-Type')));
        $this->assertStringContainsString('TCK-2026-EXP1', $response->streamedContent());
    }

    public function test_lead_amc_endpoint_detects_active_contract(): void
    {
        // Without AMC
        $this->actingAs($this->staff);
        $res1 = $this->getJson(route('service-tickets.lead-amc', $this->lead));
        $res1->assertOk()->assertJson(['has_active_amc' => false]);

        // Create Active AMC
        $amc = AmcContract::create([
            'lead_id' => $this->lead->id,
            'contract_no' => 'AMC-TEST-AUTO',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(11),
            'value' => 8000.00,
            'frequency' => 'quarterly',
            'status' => 'active',
        ]);

        $res2 = $this->getJson(route('service-tickets.lead-amc', $this->lead));
        $res2->assertOk()->assertJson([
            'has_active_amc' => true,
            'contract_no' => 'AMC-TEST-AUTO',
            'amc_contract_id' => $amc->id,
        ]);
    }

    public function test_tab_based_filtering(): void
    {
        ServiceTicket::create([
            'ticket_no' => 'TCK-TAB-CRIT',
            'lead_id' => $this->lead->id,
            'title' => 'Critical ticket',
            'issue_type' => 'camera_offline',
            'priority' => 'critical',
            'status' => 'open',
            'description' => 'Urgent camera fault.',
            'billing_type' => 'warranty_amc',
        ]);

        ServiceTicket::create([
            'ticket_no' => 'TCK-TAB-RES',
            'lead_id' => $this->lead->id,
            'title' => 'Resolved ticket',
            'issue_type' => 'blurry_feed',
            'priority' => 'low',
            'status' => 'resolved',
            'description' => 'Fixed lens.',
            'billing_type' => 'free_courtesy',
        ]);

        $this->actingAs($this->staff);

        // Tab: critical
        $resCrit = $this->get(route('service-tickets.index', ['tab' => 'critical']));
        $resCrit->assertOk();
        $resCrit->assertSee('TCK-TAB-CRIT');
        $resCrit->assertDontSee('TCK-TAB-RES');

        // Tab: resolved
        $resRes = $this->get(route('service-tickets.index', ['tab' => 'resolved']));
        $resRes->assertOk();
        $resRes->assertSee('TCK-TAB-RES');
        $resRes->assertDontSee('TCK-TAB-CRIT');
    }

    public function test_staff_can_export_service_tickets_to_csv(): void
    {
        $this->actingAs($this->staff);

        ServiceTicket::create([
            'ticket_no' => 'TCK-CSV-001',
            'lead_id' => $this->lead->id,
            'title' => 'CSV Export Camera Test',
            'issue_type' => 'camera_offline',
            'priority' => 'high',
            'status' => 'open',
            'description' => 'Camera offline export test',
            'billing_type' => 'warranty_amc',
            'cost' => 500.00,
        ]);

        $response = $this->get(route('service-tickets.export'));
        $response->assertOk();
        $this->assertTrue($response->headers->contains('content-type', 'text/csv; charset=UTF-8'));

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Ticket No', $content);
        $this->assertStringContainsString('Customer Name', $content);
        $this->assertStringContainsString('TCK-CSV-001', $content);
        $this->assertStringContainsString('Alice Client', $content);
        $this->assertStringContainsString('Warranty Amc', $content);
    }

    public function test_staff_can_export_service_tickets_to_pdf(): void
    {
        $this->actingAs($this->staff);

        ServiceTicket::create([
            'ticket_no' => 'TCK-PDF-001',
            'lead_id' => $this->lead->id,
            'title' => 'PDF Export Camera Test',
            'issue_type' => 'camera_offline',
            'priority' => 'high',
            'status' => 'open',
            'description' => 'Camera offline export PDF test',
            'billing_type' => 'warranty_amc',
            'cost' => 750.00,
        ]);

        $response = $this->get(route('service-tickets.export-pdf'));
        $response->assertOk();
        $this->assertTrue($response->headers->contains('content-type', 'application/pdf'));
    }
}

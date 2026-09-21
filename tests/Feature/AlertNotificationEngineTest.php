<?php

namespace Tests\Feature;

use App\Models\AmcContract;
use App\Models\AmcVisit;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Services\AlertNotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AlertNotificationEngineTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Lead $lead;
    private AlertNotificationService $alertService;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);

        $this->lead = Lead::create([
            'customer_name' => 'Dr. Rajesh Sharma',
            'email'         => 'rajesh@sharmaclinic.com',
            'phone'         => '9876543210',
            'site_address'  => 'Sharma Eye Clinic, Sector 14, Gurgaon',
            'status'        => 'won',
        ]);

        $this->alertService = app(AlertNotificationService::class);
    }

    public function test_send_alert_dispatches_across_whatsapp_sms_and_email(): void
    {
        $logs = $this->alertService->sendAlert('quotation_sent', $this->lead, [
            'customer_name' => $this->lead->customer_name,
            'quote_no'      => 'QT-2026-0088',
            'total_amount'  => '₹45,000.00',
            'valid_until'   => '30 Sep 2026',
            'link'          => 'http://localhost:8000/view-quotation/1',
        ]);

        $this->assertCount(3, $logs);

        // Verify WhatsApp Log
        $this->assertDatabaseHas('notification_logs', [
            'channel'         => 'whatsapp',
            'event_type'      => 'quotation_sent',
            'recipient_phone' => '9876543210',
            'status'          => 'sent',
        ]);

        $waLog = NotificationLog::where('channel', 'whatsapp')->first();
        $this->assertStringContainsString('QT-2026-0088', $waLog->message_body);
        $this->assertStringContainsString('api.whatsapp.com/send?phone=919876543210', $waLog->action_url);

        // Verify SMS Log
        $this->assertDatabaseHas('notification_logs', [
            'channel'         => 'sms',
            'event_type'      => 'quotation_sent',
            'recipient_phone' => '9876543210',
        ]);

        // Verify Email Log
        $this->assertDatabaseHas('notification_logs', [
            'channel'         => 'email',
            'event_type'      => 'quotation_sent',
            'recipient_email' => 'rajesh@sharmaclinic.com',
        ]);
    }

    public function test_send_manual_broadcast_creates_log(): void
    {
        $response = $this->actingAs($this->admin)->post(route('alerts.broadcast'), [
            'channel'        => 'sms',
            'recipient_name' => 'Vikram Sethi',
            'phone'          => '9811122233',
            'message'        => 'Your CCTV technician is arriving at 3:00 PM today.',
        ]);

        $response->assertRedirect(route('alerts.index'));

        $this->assertDatabaseHas('notification_logs', [
            'channel'         => 'sms',
            'event_type'      => 'custom_broadcast',
            'recipient_name'  => 'Vikram Sethi',
            'recipient_phone' => '9811122233',
            'message_body'    => 'Your CCTV technician is arriving at 3:00 PM today.',
        ]);
    }

    public function test_automated_reminder_sweep_detects_amc_visits_and_overdue_invoices(): void
    {
        // 1. Create upcoming AMC visit (scheduled tomorrow)
        $contract = AmcContract::create([
            'lead_id'            => $this->lead->id,
            'contract_no'        => 'AMC-2026-0012',
            'start_date'         => now()->subMonths(3),
            'end_date'           => now()->addMonths(9),
            'value'              => 15000,
            'visits_per_year'    => 4,
            'preventive_visits'  => 4,
            'breakdown_visits'   => 2,
            'coverage_type'      => 'non_comprehensive',
            'status'             => 'active',
            'billing_cycle'      => 'annually',
        ]);

        $visit = AmcVisit::create([
            'amc_contract_id' => $contract->id,
            'visit_number'    => 1,
            'scheduled_date'  => Carbon::today()->addDay(),
            'status'          => 'pending',
            'visit_type'      => 'routine',
        ]);

        // 2. Create quotation and job for overdue invoice
        $quote = \App\Models\Quotation::create([
            'lead_id'        => $this->lead->id,
            'quotation_no'   => 'QT-2026-9999',
            'quotation_date' => now()->subDays(30),
            'subtotal'       => 20000,
            'tax_amount'     => 3600,
            'total'          => 23600,
            'status'         => 'accepted',
        ]);

        $job = \App\Models\InstallationJob::create([
            'quotation_id'   => $quote->id,
            'job_no'         => 'JOB-2026-9999',
            'status'         => 'completed',
            'scheduled_date' => now()->subDays(25),
        ]);

        $invoice = Invoice::create([
            'installation_job_id' => $job->id,
            'quotation_id'        => $quote->id,
            'invoice_no'          => 'INV-2026-0050',
            'invoice_date'        => now()->subDays(20),
            'due_date'            => now()->subDays(5),
            'subtotal'            => 20000,
            'tax_amount'          => 3600,
            'total'               => 23600,
            'status'              => 'unpaid',
        ]);

        // Run artisan reminder sweep command
        $this->artisan('alerts:send-reminders')->assertSuccessful();

        // Verify that AMC Visit reminder was dispatched
        $this->assertDatabaseHas('notification_logs', [
            'event_type'   => 'amc_visit_reminder',
            'reference_id' => $visit->id,
        ]);

        // Verify that Overdue invoice alert was dispatched
        $this->assertDatabaseHas('notification_logs', [
            'event_type'   => 'payment_overdue',
            'reference_id' => $invoice->id,
        ]);
    }

    public function test_user_can_view_templates_and_update_content(): void
    {
        $template = NotificationTemplate::getTemplate('ticket_created');

        $response = $this->actingAs($this->admin)->get(route('alerts.templates'));
        $response->assertOk();
        $response->assertSee('Service Ticket Registered');

        $updateResponse = $this->actingAs($this->admin)->put(route('alerts.templates.update', $template), [
            'whatsapp_template'   => 'Custom Ticket Alert: {ticket_no} for {customer_name}',
            'sms_template'        => 'Ticket {ticket_no} logged for {customer_name}',
            'email_subject'       => 'Urgent Ticket {ticket_no}',
            'email_body'          => '<p>Ticket {ticket_no} has been logged.</p>',
            'is_whatsapp_enabled' => 1,
            'is_sms_enabled'      => 0,
            'is_email_enabled'    => 1,
        ]);

        $updateResponse->assertRedirect();

        $freshTemplate = $template->fresh();
        $this->assertEquals('Custom Ticket Alert: {ticket_no} for {customer_name}', $freshTemplate->whatsapp_template);
        $this->assertTrue($freshTemplate->is_whatsapp_enabled);
        $this->assertFalse($freshTemplate->is_sms_enabled);
    }
}

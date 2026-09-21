<?php

namespace Tests\Feature;

use App\Models\AmcContract;
use App\Models\GatewaySetting;
use App\Models\Lead;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\Quotation;
use App\Models\ServiceTicket;
use App\Models\User;
use App\Services\AlertNotificationService;
use App\Services\OtpService;
use App\Services\SmsGatewayService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SmsGatewayAndReminderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_quotation_email_send_persists_customer_email_and_logs_notification(): void
    {
        Mail::fake();

        $lead = Lead::create([
            'customer_name' => 'John Security',
            'phone'         => '9876543210',
            'email'         => null,
            'site_address'  => '123 Site Road',
        ]);

        $quotation = Quotation::create([
            'lead_id'        => $lead->id,
            'quotation_no'   => 'QT-20260908-0001',
            'quotation_date' => Carbon::today(),
            'valid_until'    => Carbon::today()->addDays(15),
            'subtotal'       => 10000,
            'discount'       => 0,
            'tax_percent'    => 18,
            'tax_amount'     => 1800,
            'total'          => 11800,
            'status'         => 'draft',
        ]);

        $this->actingAs($this->admin)->post(route('quotations.send-email', $quotation), [
            'email'          => 'john.security@example.com',
            'subject'        => 'Custom Proposal Subject',
            'custom_message' => 'Please find the quote.',
            'pdf_format'     => '1',
        ]);

        $lead->refresh();
        $this->assertEquals('john.security@example.com', $lead->email);

        $quotation->refresh();
        $this->assertEquals('sent', $quotation->status);

        $this->assertDatabaseHas('notification_logs', [
            'channel'         => 'email',
            'event_type'      => 'quotation_sent',
            'recipient_email' => 'john.security@example.com',
            'reference_type'  => Quotation::class,
            'reference_id'    => $quotation->id,
        ]);
    }

    public function test_automated_reminder_sweep_dispatches_3_day_quotation_and_15_day_amc_alerts(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Expiring Client',
            'phone'         => '9876543210',
            'email'         => 'expiring@example.com',
        ]);

        // 1. Quotation expiring in 2 days (within 3 days window)
        $quote = Quotation::create([
            'lead_id'        => $lead->id,
            'quotation_no'   => 'QT-EXP-001',
            'quotation_date' => Carbon::today()->subDays(10),
            'valid_until'    => Carbon::today()->addDays(2),
            'subtotal'       => 5000,
            'total'          => 5000,
            'status'         => 'sent',
        ]);

        // 2. AMC contract expiring in 10 days (within 15 days window)
        $amc = AmcContract::create([
            'lead_id'      => $lead->id,
            'contract_no'  => 'AMC-EXP-001',
            'start_date'   => Carbon::today()->subYear(),
            'end_date'     => Carbon::today()->addDays(10),
            'value'        => 25000,
            'frequency'    => 'quarterly',
            'status'       => 'active',
        ]);

        Artisan::call('alerts:send-reminders');

        $this->assertDatabaseHas('notification_logs', [
            'event_type'     => 'quotation_expiring_soon',
            'reference_type' => Quotation::class,
            'reference_id'   => $quote->id,
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'event_type'     => 'amc_expiry_alert',
            'reference_type' => AmcContract::class,
            'reference_id'   => $amc->id,
        ]);
    }

    public function test_automated_reminders_deduplicate_to_prevent_duplicate_spam(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Deduplicate Client',
            'phone'         => '9876543210',
            'email'         => 'dedup@example.com',
        ]);

        $quote = Quotation::create([
            'lead_id'        => $lead->id,
            'quotation_no'   => 'QT-DEDUP-001',
            'quotation_date' => Carbon::today()->subDays(10),
            'valid_until'    => Carbon::today()->addDays(2),
            'subtotal'       => 5000,
            'total'          => 5000,
            'status'         => 'sent',
        ]);

        // Run first sweep
        Artisan::call('alerts:send-reminders');
        $initialCount = NotificationLog::where('event_type', 'quotation_expiring_soon')->count();
        $this->assertGreaterThan(0, $initialCount);

        // Run second sweep immediately -> should not duplicate
        Artisan::call('alerts:send-reminders');
        $secondCount = NotificationLog::where('event_type', 'quotation_expiring_soon')->count();
        $this->assertEquals($initialCount, $secondCount);
    }

    public function test_sms_gateway_service_supports_twilio_msg91_and_custom_webhook(): void
    {
        Http::fake([
            'https://api.twilio.com/*' => Http::response(['sid' => 'SM123456789'], 200),
            'https://control.msg91.com/*' => Http::response(['request_id' => 'MSG123', 'type' => 'success'], 200),
            'https://webhook.site/*' => Http::response(['status' => 'received'], 200),
        ]);

        $gatewayService = app(SmsGatewayService::class);
        $settings = GatewaySetting::getSettings();

        // 1. Test Twilio Driver
        $settings->update([
            'active_sms_gateway' => 'twilio',
            'twilio_account_sid' => 'AC_TEST_SID',
            'twilio_auth_token'  => 'AUTH_TEST_TOKEN',
            'twilio_from_number' => '+15550001111',
        ]);
        $resTwilio = $gatewayService->sendSms('9876543210', 'Test Twilio SMS');
        $this->assertTrue($resTwilio['success']);
        $this->assertEquals('twilio', $resTwilio['gateway']);

        // 2. Test MSG91 Driver
        $settings->update([
            'active_sms_gateway' => 'msg91',
            'msg91_auth_key'     => 'MSG91_KEY',
            'msg91_sender_id'    => 'CCTVCR',
            'msg91_flow_id'      => 'FLOW_123',
        ]);
        $resMsg91 = $gatewayService->sendSms('9876543210', 'Test MSG91 SMS');
        $this->assertTrue($resMsg91['success']);
        $this->assertEquals('msg91', $resMsg91['gateway']);

        // 3. Test Custom Webhook Driver
        $settings->update([
            'active_sms_gateway' => 'custom_webhook',
            'webhook_url'        => 'https://webhook.site/test-endpoint',
            'webhook_method'     => 'POST',
        ]);
        $resWebhook = $gatewayService->sendSms('9876543210', 'Test Webhook Alert');
        $this->assertTrue($resWebhook['success']);
        $this->assertEquals('custom_webhook', $resWebhook['gateway']);
    }

    public function test_otp_service_generates_and_verifies_otp(): void
    {
        $otpService = app(OtpService::class);

        $result = $otpService->generateAndSendOtp('9876543210', 'Test Customer');

        $this->assertNotEmpty($result['otp']);
        $this->assertEquals(6, strlen($result['otp']));
        $this->assertNotEmpty($result['whatsapp_url']);

        // Wrong code fails
        $this->assertFalse($otpService->verifyOtp('9876543210', '000000'));

        // Correct code succeeds
        $this->assertTrue($otpService->verifyOtp('9876543210', $result['otp']));

        // Second verification fails (single use)
        $this->assertFalse($otpService->verifyOtp('9876543210', $result['otp']));
    }

    public function test_service_ticket_triggers_realtime_notifications_on_create_and_status_update(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Ticket Customer',
            'phone'         => '9876543210',
            'email'         => 'ticket@example.com',
        ]);

        $ticketData = [
            'lead_id'      => $lead->id,
            'title'        => 'CCTV Camera Offline',
            'issue_type'   => 'camera_offline',
            'priority'     => 'high',
            'description'  => 'Camera in entrance has no video feed.',
            'billing_type' => 'billable',
        ];

        $response = $this->actingAs($this->admin)->post(route('service-tickets.store'), $ticketData);
        $response->assertSessionHasNoErrors();

        $ticket = ServiceTicket::where('lead_id', $lead->id)->first();
        $this->assertNotNull($ticket);

        $this->assertDatabaseHas('notification_logs', [
            'event_type'     => 'ticket_created',
            'reference_type' => ServiceTicket::class,
            'reference_id'   => $ticket->id,
        ]);

        // Status update to resolved
        $this->actingAs($this->admin)->patch(route('service-tickets.updateStatus', $ticket), [
            'status'           => 'resolved',
            'resolution_notes' => 'Replaced BNC connector and tested feed.',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'event_type'     => 'ticket_resolved',
            'reference_type' => ServiceTicket::class,
            'reference_id'   => $ticket->id,
        ]);
    }
}

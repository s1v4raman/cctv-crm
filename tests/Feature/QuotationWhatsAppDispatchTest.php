<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\NotificationLog;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationWhatsAppDispatchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Lead $lead;
    private Quotation $quotation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->lead = Lead::create([
            'customer_name' => 'Sailesh Kumar Enterprise',
            'email' => 'sailesh@gmail.com',
            'phone' => '9876543210',
            'site_address' => 'Flat 402, Green Valley Apartments, Sector 18, Noida',
            'status' => 'contacted',
        ]);

        $this->quotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'quotation_no' => 'Q-2026-9090',
            'quotation_date' => now(),
            'valid_until' => now()->addDays(15),
            'subtotal' => 25000,
            'discount' => 1000,
            'tax_percent' => 18,
            'tax_amount' => 4320,
            'total' => 28320,
            'status' => 'draft',
        ]);

        QuotationItem::create([
            'quotation_id' => $this->quotation->id,
            'item_name' => '4K ColorVu IP Camera 4MP',
            'description' => '4K ColorVu IP Camera 4MP',
            'quantity' => 4,
            'unit_price' => 5000,
            'total' => 20000,
        ]);
    }

    public function test_send_whatsapp_formats_phone_updates_status_and_redirects(): void
    {
        $response = $this->actingAs($this->admin)->get(route('quotations.send-whatsapp', $this->quotation));

        $this->quotation->refresh();
        $this->assertEquals('sent', $this->quotation->status);
        $this->assertNotNull($this->quotation->sent_at);
        $this->assertDatabaseHas('quotation_status_histories', [
            'quotation_id' => $this->quotation->id,
            'from_status' => 'draft',
            'to_status' => 'sent',
        ]);

        $this->assertEquals('919876543210', $this->quotation->getWhatsAppPhone());

        $response->assertRedirect();
        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('https://api.whatsapp.com/send?phone=919876543210', $targetUrl);
        $this->assertStringContainsString('Q-2026-9090', urldecode($targetUrl));
        $this->assertStringContainsString('28,320.00', urldecode($targetUrl));
    }

    public function test_send_whatsapp_with_format_selection_and_custom_phone_via_ajax(): void
    {
        $response = $this->actingAs($this->admin)->getJson(route('quotations.send-whatsapp', [
            'quotation' => $this->quotation,
            'format' => '2',
            'phone' => '919811122233',
            'customer_name' => 'Updated Customer Ltd',
        ]));

        $response->assertOk();
        $response->assertJson([
            'success'       => true,
            'format'        => '2',
            'phone'         => '919811122233',
            'customer_name' => 'Updated Customer Ltd',
            'is_saved'      => true,
        ]);
        $response->assertJsonStructure([
            'whatsapp_url',
            'web_url',
            'app_url',
            'sms_url',
            'pdf_url',
            'vcard_url',
        ]);

        $this->quotation->refresh();
        $this->assertEquals('sent', $this->quotation->status);
        $this->assertEquals('919811122233', $this->quotation->lead->fresh()->phone);
        $this->assertEquals('Updated Customer Ltd', $this->quotation->lead->fresh()->customer_name);

        $this->assertDatabaseHas('notification_logs', [
            'channel' => 'whatsapp',
            'event_type' => 'quotation_sent',
            'recipient_phone' => '919811122233',
            'reference_id' => $this->quotation->id,
        ]);
    }

    public function test_send_whatsapp_for_unsaved_customer_saves_phone_and_dispatches(): void
    {
        $unsavedLead = Lead::create([
            'customer_name' => 'New Walk-in Client',
            'phone' => '',
            'site_address' => 'Plot 12, Cyber City',
            'status' => 'new',
        ]);

        $quote = Quotation::create([
            'lead_id' => $unsavedLead->id,
            'quotation_no' => 'Q-2026-UNSAVED',
            'quotation_date' => now(),
            'valid_until' => now()->addDays(15),
            'subtotal' => 15000,
            'tax_amount' => 2700,
            'total' => 17700,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('quotations.send-whatsapp', [
            'quotation' => $quote,
            'format' => '1',
            'phone' => '9888877777',
            'customer_name' => 'Walk-in Client Verified',
        ]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'phone' => '919888877777',
            'customer_name' => 'Walk-in Client Verified',
            'is_saved' => true,
        ]);

        $this->assertEquals('9888877777', $unsavedLead->fresh()->phone);
        $this->assertEquals('Walk-in Client Verified', $unsavedLead->fresh()->customer_name);
    }

    public function test_whatsapp_and_sms_url_helpers_for_format_and_web(): void
    {
        $urlF1 = $this->quotation->getWhatsAppUrl('1');
        $this->assertStringContainsString('Executive Modern Quotation', urldecode($urlF1));

        $urlF2 = $this->quotation->getWhatsAppUrl('2');
        $this->assertStringContainsString('Technical Detailed Quotation', urldecode($urlF2));

        $webUrl = $this->quotation->getWhatsAppWebUrl('3');
        $this->assertStringContainsString('https://web.whatsapp.com/send?phone=919876543210', $webUrl);
        $this->assertStringContainsString('Classic Formal Quotation', urldecode($webUrl));

        $appUrl = $this->quotation->getWhatsAppAppUrl('1');
        $this->assertStringContainsString('whatsapp://send?phone=919876543210', $appUrl);

        $smsUrl = $this->quotation->getSmsUrl('1');
        $this->assertStringContainsString('sms:+919876543210?body=', $smsUrl);
        $this->assertStringContainsString('Q-2026-9090', urldecode($smsUrl));
    }
}

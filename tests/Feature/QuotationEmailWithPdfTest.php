<?php

namespace Tests\Feature;

use App\Mail\QuotationEmailWithPdf;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class QuotationEmailWithPdfTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Lead $lead;
    private Quotation $quotation;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->lead = Lead::create([
            'customer_name' => 'Sailesh Kumar Enterprise',
            'email' => 'sailesh@gmail.com',
            'phone' => '+91 98765 43210',
            'site_address' => 'Flat 402, Green Valley Apartments, Sector 18, Noida',
            'status' => 'contacted',
        ]);

        $this->quotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'quotation_no' => 'Q-2026-7788',
            'quotation_date' => now(),
            'valid_until' => now()->addDays(15),
            'subtotal' => 50000,
            'discount' => 2000,
            'tax_percent' => 18,
            'tax_amount' => 8640,
            'total' => 56640,
            'status' => 'draft',
        ]);

        QuotationItem::create([
            'quotation_id' => $this->quotation->id,
            'item_name' => '8-Channel 4K NVR with 4TB HDD',
            'description' => '8-Channel 4K NVR with 4TB HDD',
            'quantity' => 1,
            'unit_price' => 18000,
            'total' => 18000,
        ]);
    }

    public function test_send_email_dispatches_mailable_with_pdf_and_updates_status(): void
    {
        $response = $this->actingAs($this->admin)->post(route('quotations.send-email', $this->quotation), [
            'email' => 'sailesh@gmail.com',
            'subject' => 'Official CCTV Proposal for Sector 18 Premises',
            'custom_message' => 'Please find attached the official PDF proposal for 8-channel CCTV setup.',
            'pdf_format' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->quotation->refresh();
        $this->assertEquals('sent', $this->quotation->status);
        $this->assertNotNull($this->quotation->sent_at);

        $this->assertDatabaseHas('quotation_status_histories', [
            'quotation_id' => $this->quotation->id,
            'from_status' => 'draft',
            'to_status' => 'sent',
        ]);

        Mail::assertSent(QuotationEmailWithPdf::class, function ($mail) {
            $mail->build();
            $hasPdfAttachment = collect($mail->rawAttachments)->contains(function ($att) {
                return $att['name'] === "Quotation-Q-2026-7788.pdf" && $att['options']['mime'] === 'application/pdf';
            });

            return $mail->hasTo('sailesh@gmail.com') &&
                   $mail->emailSubject === 'Official CCTV Proposal for Sector 18 Premises' &&
                   $hasPdfAttachment;
        });
    }

    public function test_outlook_and_mailto_compose_urls_are_correctly_generated(): void
    {
        $outlookUrl = $this->quotation->getOutlookComposeUrl();
        $mailtoUrl = $this->quotation->getMailtoUrl();
        $emailSubject = $this->quotation->getEmailSubject();
        $formattedBody = $this->quotation->getEmailFormattedBody();

        $this->assertStringContainsString('https://outlook.office.com/mail/deeplink/compose', $outlookUrl);
        $this->assertStringContainsString(rawurlencode('sailesh@gmail.com'), $outlookUrl);
        $this->assertStringContainsString('Q-2026-7788', $outlookUrl);

        $this->assertStringStartsWith('mailto:sailesh@gmail.com', $mailtoUrl);
        $this->assertStringContainsString(rawurlencode($emailSubject), $mailtoUrl);

        $this->assertStringContainsString('Sailesh Kumar Enterprise', $formattedBody);
        $this->assertStringContainsString('56,640.00', $formattedBody);
        $this->assertStringContainsString('8-Channel 4K NVR with 4TB HDD', $formattedBody);
    }

    public function test_gateway_live_email_verification_route(): void
    {
        $response = $this->actingAs($this->admin)->post(route('alerts.gateways.test-email'), [
            'test_email' => 'client.verify@example.com',
        ]);

        $response->assertRedirect(route('alerts.gateways'));
        $response->assertSessionHas('status');

        Mail::assertSent(\Illuminate\Mail\Mailable::class, function ($mail) {
            return $mail->hasTo('client.verify@example.com');
        });
    }
}

<?php

namespace Tests\Feature;

use App\Mail\QuotationEmailWithPdf;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class QuotationEmailAutomaticAttachmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $technician;
    private Lead $lead;
    private Quotation $quotation;
    private Product $cameraProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Boss']);
        $this->technician = User::factory()->create(['role' => 'technician', 'name' => 'Site Tech']);

        $this->lead = Lead::create([
            'customer_name' => 'Babu Ramanathan',
            'email' => 'babu@precisionsecurity.com',
            'phone' => '9840123456',
            'site_address' => '12 Gandhi Road, Chennai',
            'status' => 'contacted',
        ]);

        $this->cameraProduct = Product::create([
            'sku' => 'CAM-HIK-2MP',
            'name' => 'Hikvision 2 MP PTZ Camera',
            'category' => 'CCTV Cameras & Recorders',
            'unit' => 'Nos',
            'cost_price' => 12000.00,
            'unit_price' => 18000.00,
            'stock_quantity' => 10,
            'status' => 'approved',
            'is_active' => true,
        ]);

        $this->quotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'created_by' => $this->admin->id,
            'quotation_no' => 'QT-20261122-0024',
            'quotation_date' => now(),
            'valid_until' => now()->addDays(15),
            'subtotal' => 36000.00,
            'discount' => 0,
            'tax_percent' => 18,
            'tax_amount' => 6480.00,
            'total' => 42480.00,
            'status' => 'approved',
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
        ]);

        QuotationItem::create([
            'quotation_id' => $this->quotation->id,
            'product_id' => $this->cameraProduct->id,
            'item_name' => 'Hikvision 2 MP PTZ Camera',
            'quantity' => 2,
            'unit' => 'Nos',
            'unit_price' => 18000.00,
            'total' => 36000.00,
        ]);
    }

    public function test_user_can_download_eml_draft_with_automatic_pdf_attachment(): void
    {
        $response = $this->actingAs($this->admin)->get(route('quotations.download-eml', [
            'quotation' => $this->quotation,
            'email' => 'babu@precisionsecurity.com',
            'subject' => 'Official CCTV Quotation #QT-20261122-0024 - Precision IT Systems',
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
            'custom_note' => 'Includes 2-year warranty and free installation.',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'message/rfc822');
        $this->assertStringContainsString('Draft_Quotation_QT-20261122-0024', $response->headers->get('Content-Disposition'));

        $content = $response->getContent();

        // 1. Must contain X-Unsent: 1 header so Outlook opens in Compose/Edit mode with Send button active!
        $this->assertStringContainsString('X-Unsent: 1', $content);

        // 2. Must contain recipient and subject headers with proper email enclosure
        $this->assertStringContainsString('<babu@precisionsecurity.com>', $content);

        // 3. Must contain PDF attachment headers
        $this->assertStringContainsString('Content-Type: application/pdf; name="Quotation_QT-20261122-0024_babu_ramanathan.pdf"', $content);
        $this->assertStringContainsString('Content-Disposition: attachment; filename="Quotation_QT-20261122-0024_babu_ramanathan.pdf"', $content);
        $this->assertStringContainsString('Content-Transfer-Encoding: base64', $content);

        // 4. Must state that the PDF is attached
        $this->assertStringContainsString('The official signed PDF document', $content);

        // 5. Must NOT contain web portal download links in the body
        $this->assertStringNotContainsString('View & Download Complete PDF Proposal:', $content);
        $this->assertStringNotContainsString('/public-pdf/', $content);
    }

    public function test_technician_cannot_download_eml_for_unapproved_quotation(): void
    {
        $pendingQuotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'created_by' => $this->technician->id,
            'quotation_no' => 'QT-PENDING-001',
            'quotation_date' => now(),
            'subtotal' => 18000.00,
            'discount' => 0,
            'tax_percent' => 18,
            'tax_amount' => 3240.00,
            'total' => 21240.00,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->technician)
            ->get(route('quotations.download-eml', $pendingQuotation));

        $response->assertRedirect(route('quotations.show', $pendingQuotation));
        $response->assertSessionHas('error');
    }

    public function test_quotation_mailable_attaches_pdf_without_download_links(): void
    {
        $mailable = new QuotationEmailWithPdf(
            $this->quotation,
            'Site survey completed on 22 Nov.',
            'Official Proposal',
            '1',
            'itemized'
        );

        $mailable->build();

        // Verify PDF attachment is registered
        $this->assertNotEmpty($mailable->rawAttachments);
        $attachment = $mailable->rawAttachments[0];
        $this->assertEquals('application/pdf', $attachment['options']['mime']);
        $this->assertStringContainsString('Quotation_QT-20261122-0024', $attachment['name']);

        // Render HTML and assert no portal download link
        $rendered = $mailable->render();
        $this->assertStringContainsString('Quotation Overview', $rendered);
        $this->assertStringContainsString('The official PDF proposal document', $rendered);
        $this->assertStringNotContainsString('Download PDF Proposal', $rendered);
        $this->assertStringNotContainsString('btn-portal', $rendered);
    }

    public function test_quotation_show_page_renders_auto_attach_ui(): void
    {
        $response = $this->actingAs($this->admin)->get(route('quotations.show', $this->quotation));

        $response->assertStatus(200);
        $response->assertSee('PDF Attached Automatically');
        $response->assertSee('Outlook (Auto-Attach PDF)');
        $response->assertSee('Gmail Web');
        $response->assertSee('download-eml');
        $response->assertSee('email-quick-smtp-drawer');
    }

    public function test_send_email_saves_quick_smtp_credentials_and_attempts_dispatch(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)->postJson(route('quotations.send-email', $this->quotation), [
            'email'          => 'customer@company.com',
            'subject'        => 'Custom Subject',
            'smtp_username'  => 'sivaraman02032003@gmail.com',
            'smtp_password'  => 'abcd efgh ijkl mnop',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('gateway_settings', [
            'smtp_username' => 'sivaraman02032003@gmail.com',
            'smtp_password' => 'abcd efgh ijkl mnop',
            'smtp_host'     => 'smtp.gmail.com',
        ]);

        Mail::assertSent(QuotationEmailWithPdf::class, function ($mail) {
            return $mail->hasTo('customer@company.com');
        });
    }
}

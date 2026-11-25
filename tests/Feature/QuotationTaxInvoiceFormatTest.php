<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationTaxInvoiceFormatTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Lead $lead;
    private Quotation $quotation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'theme_accent' => 'blue',
            'theme_style' => 'default',
        ]);

        $this->lead = Lead::create([
            'customer_name' => 'Apex Healthcare Hospital',
            'phone' => '9840123456',
            'site_address' => "100 GST Road\nGuindy, Chennai 600032",
            'status' => 'contacted',
        ]);

        $this->quotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'quotation_no' => 'QT-20261123-0099',
            'quotation_date' => now(),
            'valid_until' => now()->addDays(15),
            'subtotal' => 50000,
            'tax_percent' => 18,
            'tax_amount' => 9000,
            'total' => 59000,
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
            'doc_type' => 'quotation',
            'status' => 'approved',
        ]);

        QuotationItem::create([
            'quotation_id' => $this->quotation->id,
            'item_name' => 'CP Plus 4MP Dome Camera',
            'description' => 'Night vision IP dome camera',
            'quantity' => 10,
            'unit_price' => 3500,
            'total' => 35000,
        ]);

        QuotationItem::create([
            'quotation_id' => $this->quotation->id,
            'item_name' => '16 Channel 4K NVR',
            'description' => 'Network video recorder with 2 SATA slots',
            'quantity' => 1,
            'unit_price' => 15000,
            'total' => 15000,
        ]);
    }

    public function test_show_page_renders_method_button_strip_for_layout_and_doc_type(): void
    {
        $response = $this->actingAs($this->admin)->get(route('quotations.show', $this->quotation));

        $response->assertStatus(200);
        $response->assertSeeText('Layout:');
        $response->assertSeeText('Format 1 (Precision)');
        $response->assertSeeText('Format 2 (NP Solutions)');
        $response->assertSeeText('Format 3 (Linepix)');
        $response->assertSeeText('Pricing Mode:');
        $response->assertSeeText('With Product Cost & Total');
        $response->assertSeeText('Total Cost Only');
        $response->assertSeeText('Tax Invoice');
        $response->assertSee('show-tab-fmt-1');
        $response->assertSee('show-tab-mode-tax_invoice');
    }

    public function test_preview_page_renders_clickable_method_buttons_and_tax_invoice(): void
    {
        $response = $this->actingAs($this->admin)->get(route('quotations.preview', [
            $this->quotation,
            '1',
            'pricing_mode' => 'itemized',
            'doc_type' => 'invoice',
        ]));

        $response->assertStatus(200);
        $response->assertSeeText('Format 1 (Precision)');
        $response->assertSeeText('Format 2 (NP Solutions)');
        $response->assertSeeText('Format 3 (Linepix)');
        $response->assertSeeText('With Product Cost & Total');
        $response->assertSeeText('Total Cost Only');
        $response->assertSeeText('Tax Invoice');
        $response->assertSee('tab-mode-tax_invoice');
        $response->assertSee('doc_type=invoice');
    }

    public function test_user_can_persist_format_pricing_mode_and_doc_type(): void
    {
        $response = $this->actingAs($this->admin)->post(route('quotations.set-format', $this->quotation), [
            'pdf_format' => '2',
            'pricing_mode' => 'total_only',
            'doc_type' => 'invoice',
        ]);

        $response->assertRedirect();
        $this->quotation->refresh();

        $this->assertEquals('2', $this->quotation->pdf_format);
        $this->assertEquals('total_only', $this->quotation->pricing_mode);
        $this->assertEquals('invoice', $this->quotation->doc_type);
        $this->assertTrue($this->quotation->isTaxInvoice());
    }

    public function test_tax_invoice_pdf_streams_correctly_for_all_three_company_formats(): void
    {
        // Format 1: Precision IT Systems Tax Invoice
        $resF1 = $this->actingAs($this->admin)->get(route('quotations.download-pdf', [
            $this->quotation,
            '1',
            'doc_type' => 'invoice',
        ]));
        $resF1->assertStatus(200);
        $this->assertEquals('application/pdf', $resF1->headers->get('Content-Type'));
        $this->assertStringContainsString('Tax_Invoice_', $resF1->headers->get('Content-Disposition') ?? '');

        // Format 2: NP Solutions Tax Invoice
        $resF2 = $this->actingAs($this->admin)->get(route('quotations.download-pdf', [
            $this->quotation,
            '2',
            'doc_type' => 'invoice',
        ]));
        $resF2->assertStatus(200);
        $this->assertEquals('application/pdf', $resF2->headers->get('Content-Type'));
        $this->assertStringContainsString('Tax_Invoice_NP_', $resF2->headers->get('Content-Disposition') ?? '');

        // Format 3: Linepix Tax Invoice
        $resF3 = $this->actingAs($this->admin)->get(route('quotations.download-pdf', [
            $this->quotation,
            '3',
            'doc_type' => 'invoice',
        ]));
        $resF3->assertStatus(200);
        $this->assertEquals('application/pdf', $resF3->headers->get('Content-Type'));
        $this->assertStringContainsString('Tax_Invoice_LP_', $resF3->headers->get('Content-Disposition') ?? '');
    }

    public function test_quotation_formatted_invoice_numbers(): void
    {
        $invF1 = $this->quotation->getFormattedInvoiceNo('1');
        $invF2 = $this->quotation->getFormattedInvoiceNo('2');
        $invF3 = $this->quotation->getFormattedInvoiceNo('3');

        $this->assertMatchesRegularExpression('/\d{4}-\d{2}\/\d{3}/', $invF1); // e.g. 2026-27/099
        $this->assertMatchesRegularExpression('/NP\/\d{2}-\d{2}\/\d{4}/', $invF2); // e.g. NP/26-27/0099
        $this->assertMatchesRegularExpression('/LP\/\d{2}-\d{2}\/\d{2}/', $invF3); // e.g. LP/26-27/99
    }

    public function test_format1_tax_invoice_fits_cleanly_on_single_page(): void
    {
        $this->quotation->load(['lead', 'items']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('quotations.pdf.format1', [
            'quotation' => $this->quotation,
            'docType' => 'invoice',
        ]);
        $domPdf = $pdf->getDomPDF();
        $domPdf->render();
        $canvas = $domPdf->getCanvas();
        $this->assertEquals(1, $canvas->get_page_count());
    }
}

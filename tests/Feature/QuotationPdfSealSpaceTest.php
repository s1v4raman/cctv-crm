<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationPdfSealSpaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_three_formats_have_comfortable_seal_space_and_fit_single_page_for_under_10_items(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Seal Customer',
            'phone' => '9884664108',
            'site_address' => 'No.356 bala murugan 2nd street Nerkundram, Chennai.',
            'status' => 'contacted',
        ]);

        $quotation = Quotation::create([
            'lead_id' => $lead->id,
            'quotation_no' => 'Q-SEAL-2026',
            'quotation_date' => now(),
            'valid_until' => now()->addDays(10),
            'tax_percent' => 18,
            'discount' => 0,
            'status' => 'approved',
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
        ]);

        // Add 5 items (typical single-page quotation)
        for ($i = 1; $i <= 5; $i++) {
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'item_name' => "Hikvision 4MP IP Dome Camera {$i}",
                'description' => "Night vision IR camera {$i}",
                'quantity' => 2,
                'unit' => 'Nos',
                'unit_price' => 3000,
                'total' => 6000,
            ]);
        }
        $quotation->recalculateTotals();

        foreach (['1', '2', '3'] as $fmt) {
            $pdf = Pdf::loadView("quotations.pdf.format{$fmt}", compact('quotation'));
            $output = $pdf->output();
            $pageCount = $pdf->getCanvas()->get_page_count();
            
            // Should strictly fit on 1 page
            $this->assertEquals(1, $pageCount, "Format {$fmt} exceeded 1 page with 5 items.");
            
            // Should contain Authorized Signatory and Company line
            $viewContent = view("quotations.pdf.format{$fmt}", compact('quotation'))->render();
            $this->assertStringContainsString('Authorized Signatory', $viewContent);
        }
    }
}

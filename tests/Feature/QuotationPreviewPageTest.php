<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationPreviewPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_live_quotation_preview_webpage(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'theme_accent' => 'blue',
            'theme_style' => 'default',
        ]);

        $lead = Lead::create([
            'customer_name' => 'Preview Customer',
            'phone' => '9876543210',
            'status' => 'contacted',
        ]);

        $quotation = Quotation::create([
            'lead_id' => $lead->id,
            'quotation_no' => 'Q-2026-PREVIEW',
            'quotation_date' => now(),
            'valid_until' => now()->addDays(10),
            'subtotal' => 15000,
            'tax_percent' => 18,
            'tax_amount' => 2700,
            'total' => 17700,
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->get(route('quotations.preview', [$quotation, '1']));

        $response->assertStatus(200);
        $response->assertSee('Quotation Preview #' . $quotation->quotation_no);
        $response->assertSee('Back to Quotation');
        $response->assertSeeText('Format 1 (Precision)');
        $response->assertSeeText('Format 2 (NP Solutions)');
        $response->assertSeeText('Format 3 (Linepix)');
        $response->assertSeeText('With Product Cost & Total');
        $response->assertSeeText('Total Cost Only');
        $response->assertSeeText('Save as Default');
        $response->assertSee(route('quotations.preview-pdf', [$quotation, '1']));
    }

    public function test_show_page_displays_clean_preview_button(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'theme_accent' => 'blue',
            'theme_style' => 'default',
        ]);

        $lead = Lead::create([
            'customer_name' => 'Show Page Customer',
            'phone' => '9876543211',
            'status' => 'contacted',
        ]);

        $quotation = Quotation::create([
            'lead_id' => $lead->id,
            'quotation_no' => 'Q-2026-SHOW',
            'quotation_date' => now(),
            'valid_until' => now()->addDays(10),
            'subtotal' => 20000,
            'tax_percent' => 18,
            'tax_amount' => 3600,
            'total' => 23600,
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->get(route('quotations.show', $quotation));

        $response->assertStatus(200);
        $response->assertSee(route('quotations.preview', [$quotation, '1']));
        $response->assertSee('Preview Quotation');
        // The card form is removed from the show page
        $response->assertDontSee('id="quotation-format-form"', false);
    }
}

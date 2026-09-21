<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use App\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationPdfTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $customer;
    private Quotation $quotation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->customer = User::factory()->create(['role' => 'customer']);

        $lead = Lead::create([
            'customer_name' => 'John Customer',
            'phone' => '1234567890',
            'status' => 'won',
        ]);

        $this->quotation = Quotation::create([
            'lead_id' => $lead->id,
            'quotation_no' => 'QT-1234',
            'quotation_date' => now(),
            'subtotal' => 1000.00,
            'discount' => 100.00,
            'tax_percent' => 18.00,
            'tax_amount' => 162.00,
            'total' => 1062.00,
            'status' => 'draft',
        ]);

        $this->quotation->items()->create([
            'item_name' => 'Camera Lens',
            'quantity' => 1,
            'unit' => 'Nos',
            'unit_price' => 1000.00,
            'total' => 1000.00,
        ]);
    }

    public function test_guests_cannot_download_quotation_pdf(): void
    {
        $response = $this->get(route('quotations.pdf', [$this->quotation, '1']));
        $response->assertRedirect(route('login'));
    }

    public function test_customers_are_forbidden_from_downloading_quotation_pdf(): void
    {
        $this->actingAs($this->customer);
        $response = $this->get(route('quotations.pdf', [$this->quotation, '1']));
        $response->assertStatus(403);
    }

    public function test_staff_and_admin_can_download_all_three_pdf_formats(): void
    {
        $this->actingAs($this->staff);

        foreach (['1', '2', '3'] as $format) {
            $response = $this->get(route('quotations.pdf', [$this->quotation, $format]));
            $response->assertOk();
            $response->assertHeader('content-type', 'application/pdf');
            $response->assertHeader('content-disposition', 'attachment; filename=' . $this->quotation->quotation_no . '_format_' . $format . '.pdf');
        }
    }

    public function test_public_can_view_quotation_pdf_stream_via_whatsapp_link(): void
    {
        foreach (['1', '2', '3'] as $format) {
            $response = $this->get(route('quotations.public-pdf', ['quotation' => $this->quotation->id, 'format' => $format]));
            $response->assertOk();
            $response->assertHeader('content-type', 'application/pdf');
        }
    }
}

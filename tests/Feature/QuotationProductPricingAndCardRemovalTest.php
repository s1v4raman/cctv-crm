<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationProductPricingAndCardRemovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Lead $lead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->lead = Lead::create([
            'customer_name' => 'City Hospital',
            'phone' => '9876543210',
            'email' => 'city@hospital.com',
            'status' => 'new',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_format_selection_cards_are_removed_and_hidden_input_is_present(): void
    {
        $response = $this->actingAs($this->admin)->get(route('quotations.create', $this->lead));
        $response->assertStatus(200);
        $response->assertDontSee('Quotation PDF Format & Style');
        $response->assertDontSee('Executive Modern');
        $response->assertSee('name="pdf_format"', false);
        $response->assertSee('type="hidden"', false);
    }

    public function test_zero_cost_product_price_is_stored_to_catalog(): void
    {
        $zeroCostProduct = Product::create([
            'sku' => 'BULLET-4MP',
            'name' => 'Bullet IP Camera 4MP Outdoor',
            'unit_price' => 0.00,
            'is_active' => true,
        ]);

        $this->assertEquals(0.00, (float) $zeroCostProduct->unit_price);

        $payload = [
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(15)->toDateString(),
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
            'items' => [
                [
                    'product_id' => $zeroCostProduct->id,
                    'item_name' => $zeroCostProduct->name,
                    'description' => 'Outdoor bullet cam',
                    'quantity' => 2,
                    'unit' => 'Nos',
                    'unit_price' => 2500.00,
                ],
            ],
            'discount' => 0,
            'tax_percent' => 18,
        ];

        $response = $this->actingAs($this->admin)->post(route('quotations.store', $this->lead), $payload);
        $response->assertSessionHasNoErrors();

        $zeroCostProduct->refresh();
        $this->assertEquals(2500.00, (float) $zeroCostProduct->unit_price);
    }

    public function test_existing_cost_product_price_is_never_overwritten_by_quotation(): void
    {
        $existingProduct = Product::create([
            'sku' => 'CPPLUS-6MP',
            'name' => 'CP PLUS 6MP Network Camera',
            'unit_price' => 12000.00,
            'is_active' => true,
        ]);

        $this->assertEquals(12000.00, (float) $existingProduct->unit_price);

        $payload = [
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(15)->toDateString(),
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
            'items' => [
                [
                    'product_id' => $existingProduct->id,
                    'item_name' => $existingProduct->name,
                    'description' => 'High-end 6MP cam',
                    'quantity' => 1,
                    'unit' => 'Nos',
                    'unit_price' => 11000.00, // discounted price on quote
                ],
            ],
            'discount' => 0,
            'tax_percent' => 18,
        ];

        $response = $this->actingAs($this->admin)->post(route('quotations.store', $this->lead), $payload);
        $response->assertSessionHasNoErrors();

        $existingProduct->refresh();
        // Must remain exactly 12000.00 in the catalog
        $this->assertEquals(12000.00, (float) $existingProduct->unit_price);
    }

    public function test_quotation_update_sets_zero_cost_but_does_not_overwrite_existing(): void
    {
        $zeroCostProduct = Product::create([
            'sku' => 'DOME-2MP',
            'name' => 'Dome 2MP Camera',
            'unit_price' => 0.00,
            'is_active' => true,
        ]);

        $existingProduct = Product::create([
            'sku' => 'NVR-8CH',
            'name' => '8 Channel NVR',
            'unit_price' => 8500.00,
            'is_active' => true,
        ]);

        $quotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'created_by' => $this->admin->id,
            'quotation_no' => 'QT-2026-TEST',
            'quotation_date' => now()->toDateString(),
            'subtotal' => 8500.00,
            'tax_percent' => 18,
            'tax_amount' => 1530.00,
            'total' => 10030.00,
            'status' => 'draft',
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
        ]);

        $payload = [
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(15)->toDateString(),
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
            'items' => [
                [
                    'product_id' => $zeroCostProduct->id,
                    'item_name' => $zeroCostProduct->name,
                    'description' => '2MP indoor',
                    'quantity' => 1,
                    'unit' => 'Nos',
                    'unit_price' => 1800.00,
                ],
                [
                    'product_id' => $existingProduct->id,
                    'item_name' => $existingProduct->name,
                    'description' => '8CH NVR',
                    'quantity' => 1,
                    'unit' => 'Nos',
                    'unit_price' => 7900.00,
                ],
            ],
            'discount' => 0,
            'tax_percent' => 18,
        ];

        $response = $this->actingAs($this->admin)->put(route('quotations.update', $quotation), $payload);
        $response->assertSessionHasNoErrors();

        $zeroCostProduct->refresh();
        $this->assertEquals(1800.00, (float) $zeroCostProduct->unit_price);

        $existingProduct->refresh();
        $this->assertEquals(8500.00, (float) $existingProduct->unit_price);
    }
}

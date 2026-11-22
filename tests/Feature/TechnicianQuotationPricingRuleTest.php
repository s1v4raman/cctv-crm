<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicianQuotationPricingRuleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $technician;
    private Lead $lead;
    private Product $cameraProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin User']);
        $this->technician = User::factory()->create(['role' => 'technician', 'name' => 'Site Tech Rajesh']);

        $this->lead = Lead::create([
            'customer_name' => 'Aravind Mills Coimbatore',
            'email' => 'aravind@mills.com',
            'phone' => '9843210987',
            'site_address' => '45 Mill Road, Coimbatore',
            'status' => 'contacted',
        ]);

        $this->cameraProduct = Product::create([
            'sku' => 'CAM-HIK-4MP',
            'name' => 'Hikvision 4MP IP Dome Camera',
            'category' => 'CCTV Cameras & Recorders',
            'unit' => 'Nos',
            'cost_price' => 2800.00,
            'unit_price' => 3500.00, // Admin scheduled base price
            'stock_quantity' => 20,
            'status' => 'approved',
            'is_active' => true,
        ]);
    }

    public function test_technician_sees_pricing_fields_in_quotation_create(): void
    {
        $response = $this->actingAs($this->technician)->get(route('quotations.create', $this->lead));

        $response->assertStatus(200);
        $response->assertSee('Unit Price');
        $response->assertSee('Total');
        $response->assertSee('Subtotal');
        $response->assertSee('Grand Total');
        $response->assertSee('Admin Price Protection Rule Active');
    }

    public function test_technician_can_create_quotation_with_exact_admin_price(): void
    {
        $payload = [
            'quotation_date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(15)->format('Y-m-d'),
            'tax_percent' => 18,
            'discount' => 0,
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
            'items' => [
                [
                    'product_id' => $this->cameraProduct->id,
                    'item_name' => 'Hikvision 4MP IP Dome Camera',
                    'description' => 'Standard installation',
                    'quantity' => 2,
                    'unit' => 'Nos',
                    'unit_price' => 3500.00, // Same as admin scheduled price
                ],
            ],
        ];

        $response = $this->actingAs($this->technician)
            ->post(route('quotations.store', $this->lead), $payload);

        $response->assertRedirect(route('technician.dashboard'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('quotations', [
            'lead_id' => $this->lead->id,
            'subtotal' => 7000.00,
            'total' => 8260.00, // 7000 + 18% GST (1260)
            'status' => 'pending_approval',
        ]);

        $this->assertDatabaseHas('quotation_items', [
            'product_id' => $this->cameraProduct->id,
            'unit_price' => 3500.00,
            'total' => 7000.00,
        ]);
    }

    public function test_technician_can_create_quotation_with_price_above_admin_range(): void
    {
        $payload = [
            'quotation_date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(15)->format('Y-m-d'),
            'tax_percent' => 18,
            'discount' => 0,
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
            'items' => [
                [
                    'product_id' => $this->cameraProduct->id,
                    'item_name' => 'Hikvision 4MP IP Dome Camera',
                    'description' => 'High ceiling mounting surcharge',
                    'quantity' => 2,
                    'unit' => 'Nos',
                    'unit_price' => 4200.00, // Above admin scheduled price (3500)
                ],
            ],
        ];

        $response = $this->actingAs($this->technician)
            ->post(route('quotations.store', $this->lead), $payload);

        $response->assertRedirect(route('technician.dashboard'));

        $this->assertDatabaseHas('quotations', [
            'lead_id' => $this->lead->id,
            'subtotal' => 8400.00,
            'total' => 9912.00, // 8400 + 18% GST (1512)
        ]);

        $this->assertDatabaseHas('quotation_items', [
            'product_id' => $this->cameraProduct->id,
            'unit_price' => 4200.00,
            'total' => 8400.00,
        ]);
    }

    public function test_technician_cannot_reduce_product_price_below_admin_scheduled_cost(): void
    {
        $payload = [
            'quotation_date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(15)->format('Y-m-d'),
            'tax_percent' => 18,
            'discount' => 0,
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
            'items' => [
                [
                    'product_id' => $this->cameraProduct->id,
                    'item_name' => 'Hikvision 4MP IP Dome Camera',
                    'description' => 'Discounted rate attempt',
                    'quantity' => 2,
                    'unit' => 'Nos',
                    'unit_price' => 3200.00, // Below admin price (3500) -> Must be rejected!
                ],
            ],
        ];

        $response = $this->actingAs($this->technician)
            ->post(route('quotations.store', $this->lead), $payload);

        $response->assertSessionHasErrors(['items.0.unit_price']);

        $this->assertDatabaseMissing('quotations', [
            'lead_id' => $this->lead->id,
        ]);
    }

    public function test_technician_cannot_apply_extra_discounts(): void
    {
        $payload = [
            'quotation_date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(15)->format('Y-m-d'),
            'tax_percent' => 18,
            'discount' => 500.00, // Technicians are not allowed to apply discounts
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
            'items' => [
                [
                    'product_id' => $this->cameraProduct->id,
                    'item_name' => 'Hikvision 4MP IP Dome Camera',
                    'description' => 'Test',
                    'quantity' => 1,
                    'unit' => 'Nos',
                    'unit_price' => 3500.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->technician)
            ->post(route('quotations.store', $this->lead), $payload);

        $response->assertSessionHasErrors(['discount']);
    }

    public function test_admin_can_quote_lower_price_without_technician_restriction(): void
    {
        $payload = [
            'quotation_date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(15)->format('Y-m-d'),
            'tax_percent' => 18,
            'discount' => 0,
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
            'items' => [
                [
                    'product_id' => $this->cameraProduct->id,
                    'item_name' => 'Hikvision 4MP IP Dome Camera',
                    'description' => 'Special Admin Partner Discount',
                    'quantity' => 1,
                    'unit' => 'Nos',
                    'unit_price' => 3000.00, // Below 3500 catalog rate - Admin CAN quote this!
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('quotations.store', $this->lead), $payload);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('quotations', [
            'lead_id' => $this->lead->id,
            'subtotal' => 3000.00,
        ]);
    }

    public function test_technician_can_quote_custom_unlisted_items(): void
    {
        $payload = [
            'quotation_date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(15)->format('Y-m-d'),
            'tax_percent' => 18,
            'discount' => 0,
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
            'items' => [
                [
                    'product_id' => null, // Custom unlisted labor service
                    'item_name' => 'Conduit Trenching & Underground Laying 40m',
                    'description' => 'Site civil work',
                    'quantity' => 1,
                    'unit' => 'Job',
                    'unit_price' => 2500.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->technician)
            ->post(route('quotations.store', $this->lead), $payload);

        $response->assertRedirect(route('technician.dashboard'));
        $this->assertDatabaseHas('quotation_items', [
            'item_name' => 'Conduit Trenching & Underground Laying 40m',
            'unit_price' => 2500.00,
        ]);
    }

    public function test_technician_sees_pricing_in_quotation_show(): void
    {
        $quotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'created_by' => $this->technician->id,
            'quotation_no' => 'QT-TEST-2026-01',
            'quotation_date' => now(),
            'valid_until' => now()->addDays(15),
            'subtotal' => 7000.00,
            'discount' => 0,
            'tax_percent' => 18,
            'tax_amount' => 1260.00,
            'total' => 8260.00,
            'status' => 'pending_approval',
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => $this->cameraProduct->id,
            'item_name' => 'Hikvision 4MP IP Dome Camera',
            'quantity' => 2,
            'unit' => 'Nos',
            'unit_price' => 3500.00,
            'total' => 7000.00,
        ]);

        $response = $this->actingAs($this->technician)->get(route('quotations.show', $quotation));

        $response->assertStatus(200);
        $response->assertSee('Unit Price');
        $response->assertSee('Subtotal');
        $response->assertSee('₹3,500.00');
        $response->assertSee('₹7,000.00');
        $response->assertSee('₹8,260.00');
    }

    public function test_technician_receives_admin_catalog_unit_price_in_search(): void
    {
        $response = $this->actingAs($this->technician)->getJson(route('products.search', ['q' => 'Hikvision']));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $this->cameraProduct->id,
            'name' => 'Hikvision 4MP IP Dome Camera',
            'unit_price' => '3500.00',
        ]);
    }

    public function test_when_admin_updates_product_price_technician_receives_new_price_in_search(): void
    {
        // Admin updates the product price in "Products & Pricing"
        $this->actingAs($this->admin)->put(route('products.update', $this->cameraProduct), [
            'sku' => $this->cameraProduct->sku,
            'name' => $this->cameraProduct->name,
            'unit' => $this->cameraProduct->unit,
            'cost_price' => 2900.00,
            'unit_price' => 3850.00, // Updated by admin
            'stock_quantity' => 20,
            'is_active' => true,
        ]);

        $this->cameraProduct->refresh();
        $this->assertEquals(3850.00, (float) $this->cameraProduct->unit_price);

        // Technician immediately receives the new updated price when quoting
        $response = $this->actingAs($this->technician)->getJson(route('products.search', ['q' => 'Hikvision']));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $this->cameraProduct->id,
            'name' => 'Hikvision 4MP IP Dome Camera',
            'unit_price' => '3850.00',
        ]);
    }

    public function test_quotation_auto_resolves_catalog_price_when_item_name_matches(): void
    {
        // Technician submits without selecting product_id or unit_price
        $payload = [
            'quotation_date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(15)->format('Y-m-d'),
            'tax_percent' => 18,
            'discount' => 0,
            'pdf_format' => '1',
            'pricing_mode' => 'itemized',
            'items' => [
                [
                    'product_id' => null, // Omitted
                    'item_name' => 'Hikvision 4MP IP Dome Camera', // Exact catalog match
                    'description' => 'Test',
                    'quantity' => 2,
                    'unit' => 'Nos',
                    'unit_price' => 0, // Zero price initially
                ],
            ],
        ];

        $response = $this->actingAs($this->technician)
            ->post(route('quotations.store', $this->lead), $payload);

        $response->assertRedirect(route('technician.dashboard'));

        $this->assertDatabaseHas('quotation_items', [
            'product_id' => $this->cameraProduct->id,
            'item_name' => 'Hikvision 4MP IP Dome Camera',
            'unit_price' => 3500.00, // Auto-populated from catalog
            'total' => 7000.00,
        ]);
    }

    public function test_sync_quotation_catalog_prices_artisan_command(): void
    {
        $quotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'created_by' => $this->technician->id,
            'quotation_no' => 'QT-ZERO-TEST',
            'quotation_date' => now(),
            'subtotal' => 0,
            'discount' => 0,
            'tax_percent' => 18,
            'tax_amount' => 0,
            'total' => 0,
            'status' => 'pending_approval',
        ]);

        $item = QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => null,
            'item_name' => 'Hikvision 4MP IP Dome Camera',
            'quantity' => 3,
            'unit' => 'Nos',
            'unit_price' => 0.00,
            'total' => 0.00,
        ]);

        $this->artisan('quotations:sync-catalog-prices', ['--quotation_id' => $quotation->id])
            ->assertExitCode(0);

        $item->refresh();
        $this->assertEquals(3500.00, (float) $item->unit_price);
        $this->assertEquals(10500.00, (float) $item->total);

        $quotation->refresh();
        $this->assertEquals(10500.00, (float) $quotation->subtotal);
        $this->assertEquals(12390.00, (float) $quotation->total); // 10500 + 18% GST (1890)
    }
}

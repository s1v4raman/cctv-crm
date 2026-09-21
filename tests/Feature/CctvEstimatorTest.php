<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Product;
use App\Models\SiteSurvey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CctvEstimatorTest extends TestCase
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
            'customer_name' => 'Acme Corporation',
            'phone' => '9876543210',
            'email' => 'contact@acme.com',
            'site_address' => '123 Industrial Hub, Tech Park',
            'status' => 'new',
        ]);
    }

    public function test_estimator_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->admin)->get(route('estimator.index'));

        $response->assertStatus(200);
        $response->assertSee('CCTV Storage');
        $response->assertSee('Auto-BOM Engine');
    }

    public function test_estimator_loads_with_site_survey_context(): void
    {
        $survey = SiteSurvey::create([
            'lead_id' => $this->lead->id,
            'surveyed_by' => $this->admin->id,
            'survey_date' => now()->toDateString(),
            'site_address' => '123 Industrial Hub',
            'contact_person' => 'John Doe',
            'contact_phone' => '9876543210',
            'camera_count_recommended' => 8,
            'cable_length_estimate' => 200,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)->get(route('estimator.index', [
            'survey_id' => $survey->id,
            'lead_id' => $this->lead->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Loaded from Site Survey #' . $survey->id);
    }

    public function test_bom_can_be_converted_to_official_quotation(): void
    {
        $product = Product::create([
            'sku' => 'HK-4MP-TEST',
            'name' => 'Hikvision 4 MP Bullet Camera',
            'unit' => 'Nos',
            'unit_price' => 3500,
            'cost_price' => 2500,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $payload = [
            'lead_id' => $this->lead->id,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(15)->toDateString(),
            'discount' => 500,
            'tax_percent' => 18,
            'notes' => 'Generated from Auto-BOM Estimator for Factory Deployment',
            'items' => [
                [
                    'product_id' => $product->id,
                    'item_name' => $product->name,
                    'description' => '4 MP IP Outdoor Bullet Camera',
                    'quantity' => 4,
                    'unit' => 'Nos',
                    'unit_price' => 3500,
                ],
                [
                    'product_id' => null,
                    'item_name' => 'Cat6 Cable Laying Service',
                    'description' => 'Conduit and cabling labor',
                    'quantity' => 100,
                    'unit' => 'Mtr',
                    'unit_price' => 25,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->postJson(route('estimator.convert'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('quotations', [
            'lead_id' => $this->lead->id,
            'status' => 'draft',
            'subtotal' => 16500.00, // (4 * 3500) + (100 * 25) = 14000 + 2500 = 16500
            'discount' => 500.00,
            'tax_percent' => 18.00,
            'tax_amount' => 2880.00, // (16500 - 500) * 0.18 = 2880
            'total' => 18880.00, // 16000 + 2880
        ]);

        $this->assertDatabaseHas('quotation_items', [
            'item_name' => 'Hikvision 4 MP Bullet Camera',
            'quantity' => 4,
            'unit_price' => 3500.00,
            'total' => 14000.00,
        ]);

        $this->assertDatabaseHas('quotation_items', [
            'item_name' => 'Cat6 Cable Laying Service',
            'quantity' => 100,
            'unit_price' => 25.00,
            'total' => 2500.00,
        ]);

        $this->assertEquals('quoted', $this->lead->fresh()->status);
    }
}

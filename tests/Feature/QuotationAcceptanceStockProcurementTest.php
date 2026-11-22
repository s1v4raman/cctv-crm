<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationAcceptanceStockProcurementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Lead $lead;
    private Supplier $supplier;
    private Product $zeroStockProduct;
    private Product $lowStockProduct;
    private Product $fullStockProduct;
    private Quotation $quotation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Managing Director']);
        $this->staff = User::factory()->create(['role' => 'staff', 'name' => 'Office Staff Suresh']);

        $this->lead = Lead::create([
            'customer_name' => 'Meenakshi Hospitals Madurai',
            'email' => 'procurement@meenakshi.com',
            'phone' => '9876501234',
            'site_address' => '100 Feet Road, Madurai',
            'status' => 'contacted',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Surveillance World India',
            'company_name' => 'Surveillance World Private Limited',
            'phone' => '9888877777',
            'categories' => ['CCTV Cameras & Recorders'],
            'is_active' => true,
        ]);

        $this->zeroStockProduct = Product::create([
            'sku' => 'CAM-ZERO-01',
            'name' => 'Hikvision 8MP Panoramic Camera',
            'category' => 'CCTV Cameras & Recorders',
            'unit' => 'Nos',
            'cost_price' => 5000.00,
            'unit_price' => 7500.00,
            'stock_quantity' => 0, // Zero Stock!
            'is_active' => true,
        ]);

        $this->lowStockProduct = Product::create([
            'sku' => 'NVR-LOW-02',
            'name' => 'Dahua 16 Channel 4K NVR',
            'category' => 'CCTV Cameras & Recorders',
            'unit' => 'Nos',
            'cost_price' => 8000.00,
            'unit_price' => 12000.00,
            'stock_quantity' => 1, // Only 1 in stock, need 4 -> Low Stock!
            'is_active' => true,
        ]);

        $this->fullStockProduct = Product::create([
            'sku' => 'CBL-FULL-03',
            'name' => 'D-Link Cat6 UTP Cable Box 305m',
            'category' => 'Cables & Conduits',
            'unit' => 'Box',
            'cost_price' => 3200.00,
            'unit_price' => 4800.00,
            'stock_quantity' => 20, // 20 in stock, need 2 -> Fully in stock!
            'is_active' => true,
        ]);

        $this->quotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'quotation_no' => 'QT-SHORTAGE-2026-99',
            'quotation_date' => now(),
            'valid_until' => now()->addDays(15),
            'subtotal' => 95100.00,
            'discount' => 0.00,
            'tax_percent' => 18.00,
            'tax_amount' => 17118.00,
            'total' => 112218.00,
            'status' => 'approved',
        ]);

        // Item 1: Zero Stock (quoted 3)
        QuotationItem::create([
            'quotation_id' => $this->quotation->id,
            'product_id' => $this->zeroStockProduct->id,
            'item_name' => 'Hikvision 8MP Panoramic Camera',
            'quantity' => 3,
            'unit' => 'Nos',
            'unit_price' => 7500.00,
            'total' => 22500.00,
        ]);

        // Item 2: Low Stock (quoted 4, only 1 on hand, deficit 3)
        QuotationItem::create([
            'quotation_id' => $this->quotation->id,
            'product_id' => $this->lowStockProduct->id,
            'item_name' => 'Dahua 16 Channel 4K NVR',
            'quantity' => 4,
            'unit' => 'Nos',
            'unit_price' => 12000.00,
            'total' => 48000.00,
        ]);

        // Item 3: Full Stock (quoted 2, 20 on hand)
        QuotationItem::create([
            'quotation_id' => $this->quotation->id,
            'product_id' => $this->fullStockProduct->id,
            'item_name' => 'D-Link Cat6 UTP Cable Box 305m',
            'quantity' => 2,
            'unit' => 'Box',
            'unit_price' => 4800.00,
            'total' => 9600.00,
        ]);
    }

    public function test_accepting_quotation_with_stock_shortage_redirects_to_procurement_check(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('quotations.accept', $this->quotation));

        $response->assertRedirect(route('quotations.procurement-check', $this->quotation));
        $response->assertSessionHas('status');

        $this->quotation->refresh();
        $this->assertEquals('accepted', $this->quotation->status);
        $this->assertEquals('won', $this->quotation->lead->status);
    }

    public function test_procurement_check_page_displays_only_zero_and_low_stock_items(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('quotations.procurement-check', $this->quotation));

        $response->assertStatus(200);

        // Highlights quoted customer & proposal data
        $response->assertSee('Meenakshi Hospitals Madurai');
        $response->assertSee('QT-SHORTAGE-2026-99');

        // Contains zero stock product and low stock product
        $response->assertSee('Hikvision 8MP Panoramic Camera');
        $response->assertSee('Zero Stock');
        $response->assertSee('Dahua 16 Channel 4K NVR');
        $response->assertSee('Low Stock');

        // Excludes full stock product
        $response->assertDontSee('D-Link Cat6 UTP Cable Box 305m');
    }

    public function test_employee_can_view_shortage_data_in_read_only_mode(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('quotations.procurement-check', $this->quotation));

        $response->assertStatus(200);
        $response->assertSee('Employee Visibility Mode');
        $response->assertSee('Hikvision 8MP Panoramic Camera');
        $response->assertSee('Dahua 16 Channel 4K NVR');

        // Employee does NOT have the Purchase Order generation submit button
        $response->assertDontSee('Create Purchase Order & Open PDF');
    }

    public function test_employee_cannot_generate_purchase_order_from_shortage(): void
    {
        $qItem = $this->quotation->items()->first();

        $payload = [
            'items' => [
                [
                    'item_id' => $qItem->id,
                    'product_id' => $this->zeroStockProduct->id,
                    'supplier_id' => $this->supplier->id,
                    'quantity' => 3,
                    'unit_cost' => 5000.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->staff)
            ->post(route('quotations.procurement-po', $this->quotation), $payload);

        $response->assertStatus(403);
        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_admin_can_generate_purchase_order_from_analyzed_shortage_data(): void
    {
        $items = $this->quotation->items()->whereIn('product_id', [$this->zeroStockProduct->id, $this->lowStockProduct->id])->get();

        $payload = [
            'items' => [
                [
                    'item_id' => $items[0]->id,
                    'product_id' => $this->zeroStockProduct->id,
                    'supplier_id' => $this->supplier->id,
                    'quantity' => 3,
                    'unit_cost' => 5000.00,
                ],
                [
                    'item_id' => $items[1]->id,
                    'product_id' => $this->lowStockProduct->id,
                    'supplier_id' => $this->supplier->id,
                    'quantity' => 3, // Deficit = 4 - 1
                    'unit_cost' => 8000.00,
                ],
            ],
            'notes' => 'Priority shipping required for hospital CCTV upgrade.',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('quotations.procurement-po', $this->quotation), $payload);

        $response->assertRedirect();
        $this->assertDatabaseCount('purchase_orders', 1);

        $po = PurchaseOrder::first();
        $this->assertNotNull($po);
        $this->assertEquals($this->supplier->id, $po->supplier_id);
        $this->assertEquals(39000.00, (float) $po->subtotal); // (3*5000) + (3*8000) = 15000 + 24000 = 39000

        $response->assertRedirect(route('purchase-orders.show', $po));

        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'product_id' => $this->zeroStockProduct->id,
            'quantity_ordered' => 3,
            'unit_cost' => 5000.00,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'product_id' => $this->lowStockProduct->id,
            'quantity_ordered' => 3,
            'unit_cost' => 8000.00,
        ]);
    }

    public function test_accepting_quotation_with_full_stock_redirects_to_show(): void
    {
        // New quotation with only in-stock items
        $fullQuotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'quotation_no' => 'QT-FULL-STOCK-01',
            'quotation_date' => now(),
            'valid_until' => now()->addDays(15),
            'subtotal' => 9600.00,
            'total' => 11328.00,
            'status' => 'approved',
        ]);

        QuotationItem::create([
            'quotation_id' => $fullQuotation->id,
            'product_id' => $this->fullStockProduct->id,
            'item_name' => 'D-Link Cat6 Cable',
            'quantity' => 2, // 20 in stock
            'unit' => 'Box',
            'unit_price' => 4800.00,
            'total' => 9600.00,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('quotations.accept', $fullQuotation));

        $response->assertRedirect(route('quotations.show', $fullQuotation));
        $response->assertSessionHas('status', 'Quotation marked as accepted successfully. All quoted products are available in warehouse stock.');
    }
}

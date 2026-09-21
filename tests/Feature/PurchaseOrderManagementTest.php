<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->technician = User::factory()->create([
            'role' => 'technician',
        ]);
    }

    public function test_can_view_suppliers_and_procurement_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('suppliers.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->get(route('purchase-orders.index'));
        $response->assertStatus(200);
    }

    public function test_can_register_new_supplier(): void
    {
        $payload = [
            'name' => 'Hikvision Central Hub',
            'company_name' => 'Prama Hikvision India Pvt Ltd',
            'contact_person' => 'Ramesh Sharma',
            'email' => 'ramesh@hikvision.com',
            'phone' => '+91 98765 43210',
            'gst_number' => '27AAAAA0000A1Z5',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'payment_terms' => 'Net 30 Days',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post(route('suppliers.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Hikvision Central Hub',
            'gst_number' => '27AAAAA0000A1Z5',
        ]);
    }

    public function test_can_create_purchase_order_with_line_items(): void
    {
        $supplier = Supplier::create([
            'name' => 'CP Plus Authorized Distributor',
            'phone' => '9988776655',
        ]);

        $product = Product::create([
            'sku' => 'CP-DOME-2MP',
            'name' => 'CP Plus 2MP Dome Camera',
            'category' => 'camera',
            'cost_price' => 1200.00,
            'unit_price' => 1800.00,
            'stock_quantity' => 5,
        ]);

        $payload = [
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(3)->toDateString(),
            'tax_percent' => 18,
            'shipping_cost' => 200.00,
            'status' => 'ordered',
            'notes' => 'Urgent replenishment for upcoming school installation',
            'items' => [
                [
                    'product_id' => $product->id,
                    'item_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_cost' => 1200.00,
                    'quantity_ordered' => 10,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('purchase-orders.store'), $payload);
        $response->assertRedirect();

        $po = PurchaseOrder::where('supplier_id', $supplier->id)->first();
        $this->assertNotNull($po);
        $this->assertEquals('ordered', $po->status);
        $this->assertEquals(12000.00, (float) $po->subtotal);
        $this->assertEquals(2160.00, (float) $po->tax_amount);
        $this->assertEquals(14360.00, (float) $po->total);
        $this->assertCount(1, $po->items);
    }

    public function test_receiving_purchase_order_automatically_increments_inventory_stock_and_logs_movement(): void
    {
        $supplier = Supplier::create([
            'name' => 'Dahua Direct',
        ]);

        $product = Product::create([
            'sku' => 'DH-IPC-HFW',
            'name' => 'Dahua 4MP Bullet Camera',
            'category' => 'camera',
            'cost_price' => 2500.00,
            'unit_price' => 3500.00,
            'stock_quantity' => 4,
        ]);

        $po = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'po_number' => 'PO-20260831-0001',
            'order_date' => now()->toDateString(),
            'status' => 'ordered',
            'subtotal' => 25000.00,
            'tax_percent' => 18,
            'tax_amount' => 4500.00,
            'shipping_cost' => 0.00,
            'total' => 29500.00,
        ]);

        $item = $po->items()->create([
            'product_id' => $product->id,
            'item_name' => $product->name,
            'sku' => $product->sku,
            'unit_cost' => 2500.00,
            'quantity_ordered' => 10,
            'quantity_received' => 0,
            'total_cost' => 25000.00,
        ]);

        // Inward 6 units now (partial shipment)
        $response = $this->actingAs($this->admin)->post(route('purchase-orders.receive', $po), [
            'received' => [
                $item->id => 6,
            ],
        ]);

        $response->assertRedirect();

        $product->refresh();
        $this->assertEquals(10, $product->stock_quantity); // Initial 4 + Inward 6 = 10

        $po->refresh();
        $this->assertEquals('partially_received', $po->status);

        // Verify stock movement ledger entry
        $movement = StockMovement::where('product_id', $product->id)->where('type', 'in')->first();
        $this->assertNotNull($movement);
        $this->assertEquals(6, $movement->quantity);
        $this->assertEquals(10, $movement->balance_after);
        $this->assertEquals(PurchaseOrder::class, $movement->reference_type);
        $this->assertEquals($po->id, $movement->reference_id);

        // Inward remaining 4 units
        $this->actingAs($this->admin)->post(route('purchase-orders.receive', $po), [
            'received' => [
                $item->id => 4,
            ],
        ]);

        $product->refresh();
        $this->assertEquals(14, $product->stock_quantity); // 10 + 4 = 14

        $po->refresh();
        $this->assertEquals('received', $po->status);
    }

    public function test_can_update_vendor_payment_record(): void
    {
        $supplier = Supplier::create(['name' => 'Western Digital Distributor']);
        $po = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'po_number' => 'PO-20260831-0002',
            'order_date' => now()->toDateString(),
            'status' => 'received',
            'total' => 10000.00,
            'payment_status' => 'unpaid',
            'amount_paid' => 0.00,
        ]);

        $response = $this->actingAs($this->admin)->patch(route('purchase-orders.updatePayment', $po), [
            'amount_paid' => 10000.00,
            'payment_status' => 'paid',
        ]);

        $response->assertRedirect();

        $po->refresh();
        $this->assertEquals(10000.00, (float) $po->amount_paid);
        $this->assertEquals('paid', $po->payment_status);
        $this->assertEquals(0.00, $po->balanceDue());
    }

    public function test_technician_cannot_access_procurement(): void
    {
        $response = $this->actingAs($this->technician)->get(route('suppliers.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->technician)->get(route('purchase-orders.index'));
        $response->assertStatus(403);
    }
}

<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\GoodsReceiptSerial;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\DocumentSequenceService;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class GoodsReceiptAndStockLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $technician;
    protected User $worker;
    protected Supplier $supplier;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'  => 'admin',
            'email' => 'admin_grn_' . uniqid() . '@example.com',
        ]);

        $this->staff = User::factory()->create([
            'role'  => 'staff',
            'email' => 'staff_grn_' . uniqid() . '@example.com',
        ]);

        $this->technician = User::factory()->create([
            'role'  => 'technician',
            'email' => 'tech_grn_' . uniqid() . '@example.com',
        ]);

        $this->worker = User::factory()->create([
            'role'  => 'worker',
            'email' => 'worker_grn_' . uniqid() . '@example.com',
        ]);

        $this->supplier = Supplier::create([
            'name'         => 'Hikvision Distributor',
            'company_name' => 'Prama Hikvision India Pvt Ltd',
            'email'        => 'vendor_' . uniqid() . '@hikvision.com',
            'phone'        => '9884664108',
            'gst_number'   => '33AAACP1234F1Z8',
            'state'        => 'Tamil Nadu',
            'status'       => 'active',
        ]);

        $this->product = Product::create([
            'name'           => '4MP IP Dome Camera',
            'sku'            => 'DS-2CD2143G2-I-' . uniqid(),
            'cost_price'     => 1000.00,
            'unit_price'     => 1800.00,
            'stock_quantity' => 10,
            'track_serial'   => false,
            'is_active'      => true,
        ]);
    }

    public function test_goods_receipt_creation_updates_stock_and_creates_movements(): void
    {
        $po = PurchaseOrder::create([
            'po_number'   => 'PO-' . date('Ymd') . '-' . rand(1000, 9999),
            'order_date'  => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'status'      => PurchaseOrder::STATUS_SENT,
            'subtotal'    => 5000.00,
            'total'       => 5900.00,
            'created_by'  => $this->admin->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id'        => $this->product->id,
            'item_name'         => $this->product->name,
            'sku'               => $this->product->sku,
            'quantity_ordered'  => 5,
            'quantity_received' => 0,
            'unit_cost'         => 1200.00,
            'total_cost'        => 6000.00,
        ]);

        $initialStock = $this->product->stock_quantity; // 10

        // Inward 5 items
        $grn = $po->inwardStock([
            'delivery_note_no' => 'DC-998811',
            'notes'            => 'Received at bay 3',
            'received_date'    => now()->toDateString(),
            'items'            => [
                $poItem->id => [
                    'qty_received'  => 5,
                    'qty_rejected'  => 0,
                    'reject_reason' => null,
                ],
            ],
        ], $this->admin->id);

        $this->assertInstanceOf(GoodsReceipt::class, $grn);
        $this->assertStringStartsWith('GRN-', $grn->grn_number);
        $this->assertEquals('DC-998811', $grn->delivery_note_no);

        // Verify stock quantity updated: 10 + 5 = 15
        $this->assertEquals($initialStock + 5, $this->product->fresh()->stock_quantity);

        // Verify stock movement was recorded
        $movement = StockMovement::where('reference_type', PurchaseOrder::class)
            ->where('reference_id', $po->id)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(StockMovement::TYPE_RECEIPT, $movement->movement_type);
        $this->assertEquals(5, $movement->qty);
        $this->assertEquals(1200.00, (float) $movement->unit_cost);
        $this->assertEquals($initialStock + 5, $movement->balance_after);

        // Verify PO status transitioned to received
        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_RECEIVED, $po->status);
        $this->assertEquals(5, $poItem->fresh()->quantity_received);
    }

    public function test_partial_receipt_and_po_status_transition(): void
    {
        $po = PurchaseOrder::create([
            'po_number'   => 'PO-' . date('Ymd') . '-' . rand(1000, 9999),
            'order_date'  => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'status'      => PurchaseOrder::STATUS_SENT,
            'subtotal'    => 10000.00,
            'total'       => 11800.00,
            'created_by'  => $this->admin->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id'        => $this->product->id,
            'item_name'         => $this->product->name,
            'quantity_ordered'  => 10,
            'quantity_received' => 0,
            'unit_cost'         => 1000.00,
            'total_cost'        => 10000.00,
        ]);

        // Partial inward: 4 out of 10
        $po->inwardStock([$poItem->id => 4], $this->admin->id);

        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_PARTIALLY_RECEIVED, $po->status);
        $this->assertEquals(4, $poItem->fresh()->quantity_received);

        // Complete inward: remaining 6
        $po->inwardStock([$poItem->id => 6], $this->admin->id);

        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_RECEIVED, $po->status);
        $this->assertEquals(10, $poItem->fresh()->quantity_received);
    }

    public function test_rejected_quantity_recorded_without_adding_to_stock(): void
    {
        $po = PurchaseOrder::create([
            'po_number'   => 'PO-' . date('Ymd') . '-' . rand(1000, 9999),
            'order_date'  => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'status'      => PurchaseOrder::STATUS_SENT,
            'subtotal'    => 10000.00,
            'total'       => 11800.00,
            'created_by'  => $this->admin->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id'        => $this->product->id,
            'item_name'         => $this->product->name,
            'quantity_ordered'  => 10,
            'quantity_received' => 0,
            'unit_cost'         => 1000.00,
            'total_cost'        => 10000.00,
        ]);

        $stockBefore = $this->product->stock_quantity;

        // Inward 6 accepted, 2 rejected
        $grn = $po->inwardStock([
            'items' => [
                $poItem->id => [
                    'qty_received'  => 6,
                    'qty_rejected'  => 2,
                    'reject_reason' => 'Broken front glass dome',
                ],
            ],
        ], $this->admin->id);

        $grItem = $grn->items->first();
        $this->assertEquals(6, $grItem->qty_received);
        $this->assertEquals(2, $grItem->qty_rejected);
        $this->assertEquals('Broken front glass dome', $grItem->reject_reason);

        // Stock must increase ONLY by accepted quantity (6), NOT rejected (2)
        $this->assertEquals($stockBefore + 6, $this->product->fresh()->stock_quantity);

        // PO item quantity_received must also be 6
        $this->assertEquals(6, $poItem->fresh()->quantity_received);
    }

    public function test_serial_tracking_requires_exact_serials_per_accepted_unit(): void
    {
        $serialProduct = Product::create([
            'name'           => '32-Channel NVR',
            'sku'            => 'NVR-32CH-' . uniqid(),
            'cost_price'     => 15000.00,
            'unit_price'     => 22000.00,
            'stock_quantity' => 0,
            'track_serial'   => true, // Serial tracking required!
            'is_active'      => true,
        ]);

        $po = PurchaseOrder::create([
            'po_number'   => 'PO-' . date('Ymd') . '-' . rand(1000, 9999),
            'order_date'  => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'status'      => PurchaseOrder::STATUS_SENT,
            'subtotal'    => 30000.00,
            'total'       => 35400.00,
            'created_by'  => $this->admin->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id'        => $serialProduct->id,
            'item_name'         => $serialProduct->name,
            'quantity_ordered'  => 2,
            'quantity_received' => 0,
            'unit_cost'         => 15000.00,
            'total_cost'        => 30000.00,
        ]);

        // Attempting to inward 2 units with only 1 serial number must fail
        $failed = false;
        try {
            $po->inwardStock([
                'items' => [
                    $poItem->id => [
                        'qty_received'  => 2,
                        'qty_rejected'  => 0,
                        'serials'       => ['SN-NVR-001'], // Only 1 serial provided
                    ],
                ],
            ], $this->admin->id);
        } catch (InvalidArgumentException $e) {
            $failed = true;
            $this->assertStringContainsString('requires 2 serial numbers', $e->getMessage());
        }

        $this->assertTrue($failed, 'Expected InvalidArgumentException when serial count does not match accepted qty.');

        // Inward with exact 2 serial numbers
        $grn = $po->inwardStock([
            'items' => [
                $poItem->id => [
                    'qty_received'  => 2,
                    'qty_rejected'  => 0,
                    'serials'       => [
                        ['serial_no' => 'SN-NVR-001', 'mac_address' => 'AA:BB:CC:11:22:33'],
                        ['serial_no' => 'SN-NVR-002', 'mac_address' => 'AA:BB:CC:11:22:34'],
                    ],
                ],
            ],
        ], $this->admin->id);

        $this->assertCount(2, GoodsReceiptSerial::where('product_id', $serialProduct->id)->get());
    }

    public function test_serial_number_uniqueness_enforced_per_product(): void
    {
        $serialProduct = Product::create([
            'name'           => 'Biometric Terminal',
            'sku'            => 'BIO-TERM-' . uniqid(),
            'cost_price'     => 8000.00,
            'unit_price'     => 12000.00,
            'stock_quantity' => 0,
            'track_serial'   => true,
            'is_active'      => true,
        ]);

        $po1 = PurchaseOrder::create([
            'po_number'   => 'PO-' . date('Ymd') . '-' . rand(1000, 9999),
            'order_date'  => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'status'      => PurchaseOrder::STATUS_SENT,
            'subtotal'    => 8000.00,
            'total'       => 9440.00,
            'created_by'  => $this->admin->id,
        ]);

        $item1 = PurchaseOrderItem::create([
            'purchase_order_id' => $po1->id,
            'product_id'        => $serialProduct->id,
            'item_name'         => $serialProduct->name,
            'quantity_ordered'  => 1,
            'quantity_received' => 0,
            'unit_cost'         => 8000.00,
            'total_cost'        => 8000.00,
        ]);

        // Inward serial BIO-SN-100
        $po1->inwardStock([
            'items' => [
                $item1->id => [
                    'qty_received' => 1,
                    'serials'      => ['BIO-SN-100'],
                ],
            ],
        ], $this->admin->id);

        // Create second PO and attempt to inward same serial number
        $po2 = PurchaseOrder::create([
            'po_number'   => 'PO-' . date('Ymd') . '-' . rand(1000, 9999),
            'order_date'  => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'status'      => PurchaseOrder::STATUS_SENT,
            'subtotal'    => 8000.00,
            'total'       => 9440.00,
            'created_by'  => $this->admin->id,
        ]);

        $item2 = PurchaseOrderItem::create([
            'purchase_order_id' => $po2->id,
            'product_id'        => $serialProduct->id,
            'item_name'         => $serialProduct->name,
            'quantity_ordered'  => 1,
            'quantity_received' => 0,
            'unit_cost'         => 8000.00,
            'total_cost'        => 8000.00,
        ]);

        $duplicateBlocked = false;
        try {
            $po2->inwardStock([
                'items' => [
                    $item2->id => [
                        'qty_received' => 1,
                        'serials'      => ['BIO-SN-100'], // Duplicate serial!
                    ],
                ],
            ], $this->admin->id);
        } catch (InvalidArgumentException $e) {
            $duplicateBlocked = true;
            $this->assertStringContainsString('already registered', $e->getMessage());
        }

        $this->assertTrue($duplicateBlocked, 'Expected InvalidArgumentException for duplicate serial.');
    }

    public function test_weighted_average_cost_updated_on_receipt(): void
    {
        // Existing stock: 10 units @ ₹100.00 each = ₹1,000.00
        $costProduct = Product::create([
            'name'           => 'Cat6 Cable Roll',
            'sku'            => 'CAT6-ROLL-' . uniqid(),
            'cost_price'     => 100.00,
            'unit_price'     => 180.00,
            'stock_quantity' => 10,
            'is_active'      => true,
        ]);

        $po = PurchaseOrder::create([
            'po_number'   => 'PO-' . date('Ymd') . '-' . rand(1000, 9999),
            'order_date'  => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'status'      => PurchaseOrder::STATUS_SENT,
            'subtotal'    => 2000.00,
            'total'       => 2360.00,
            'created_by'  => $this->admin->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id'        => $costProduct->id,
            'item_name'         => $costProduct->name,
            'quantity_ordered'  => 10,
            'quantity_received' => 0,
            'unit_cost'         => 200.00, // New batch @ ₹200.00 each = ₹2,000.00
            'total_cost'        => 2000.00,
        ]);

        // Weighted Average Cost = (10 * 100 + 10 * 200) / (10 + 10) = 3000 / 20 = ₹150.00
        $po->inwardStock([$poItem->id => 10], $this->admin->id);

        $costProduct->refresh();
        $this->assertEquals(20, $costProduct->stock_quantity);
        $this->assertEquals(150.00, (float) $costProduct->cost_price);
    }

    public function test_opening_stock_backfill_balances_ledger(): void
    {
        $newProduct = Product::create([
            'name'           => 'PTZ Speed Dome Camera',
            'sku'            => 'PTZ-CAM-' . uniqid(),
            'cost_price'     => 25000.00,
            'unit_price'     => 35000.00,
            'stock_quantity' => 8,
            'is_active'      => true,
        ]);

        // When created via ProductController or StockService, an opening movement is recorded
        $movement = app(StockService::class)->recordOpening($newProduct, 8, 25000.00, $this->admin->id);

        $this->assertEquals(StockMovement::TYPE_OPENING, $movement->movement_type);
        $this->assertEquals(8, $movement->qty);
        $this->assertEquals(8, $movement->balance_after);

        // Sum of all stock movements for this product equals current stock
        $totalLedgerSum = StockMovement::where('product_id', $newProduct->id)->sum('qty');
        $this->assertEquals($newProduct->fresh()->stock_quantity, $totalLedgerSum);
    }

    public function test_technician_and_worker_forbidden_403_on_goods_receipts(): void
    {
        $po = PurchaseOrder::create([
            'po_number'   => 'PO-' . date('Ymd') . '-' . rand(1000, 9999),
            'order_date'  => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'status'      => PurchaseOrder::STATUS_SENT,
            'subtotal'    => 1000.00,
            'total'       => 1180.00,
            'created_by'  => $this->admin->id,
        ]);

        $grn = GoodsReceipt::create([
            'grn_number'        => 'GRN-' . date('Ymd') . '-' . rand(1000, 9999),
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'received_date'     => now()->toDateString(),
            'received_by'       => $this->admin->id,
        ]);

        // Technician forbidden 403
        $this->actingAs($this->technician)->get(route('goods-receipts.index'))->assertForbidden();
        $this->actingAs($this->technician)->get(route('goods-receipts.show', $grn))->assertForbidden();
        $this->actingAs($this->technician)->get(route('goods-receipts.create', ['purchase_order_id' => $po->id]))->assertForbidden();
        $this->actingAs($this->technician)->post(route('goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'received_date'     => now()->toDateString(),
        ])->assertForbidden();
        $this->actingAs($this->technician)->get(route('goods-receipts.pdf', $grn))->assertForbidden();

        // Worker forbidden 403
        $this->actingAs($this->worker)->get(route('goods-receipts.index'))->assertForbidden();
        $this->actingAs($this->worker)->get(route('goods-receipts.show', $grn))->assertForbidden();
    }

    public function test_technician_and_worker_see_only_on_hand_quantity_never_costs_in_stock_ledger(): void
    {
        $movement = StockMovement::create([
            'product_id'     => $this->product->id,
            'movement_type'  => StockMovement::TYPE_RECEIPT,
            'type'           => 'in',
            'qty'            => 10,
            'quantity'       => 10,
            'balance_after'  => 20,
            'unit_cost'      => 4500.00,
            'note'           => 'Procurement batch',
            'created_by'     => $this->admin->id,
        ]);

        // Technician can view stock ledger quantities, but cost column is hidden
        $responseTech = $this->actingAs($this->technician)->get(route('inventory.ledger'));
        $responseTech->assertOk();
        $responseTech->assertSee($this->product->name);
        $responseTech->assertSee('+10');
        $responseTech->assertSee('20');
        $responseTech->assertDontSee('Unit Cost');
        $responseTech->assertDontSee('Valuation Change');

        // Admin can view stock ledger with full unit costs
        $responseAdmin = $this->actingAs($this->admin)->get(route('inventory.ledger'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Unit Cost');
        $responseAdmin->assertSee('Valuation Change');
        $responseAdmin->assertSee('4,500.00');
    }

    public function test_goods_receipt_pdf_generation(): void
    {
        $po = PurchaseOrder::create([
            'po_number'   => 'PO-' . date('Ymd') . '-' . rand(1000, 9999),
            'order_date'  => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'status'      => PurchaseOrder::STATUS_SENT,
            'subtotal'    => 2000.00,
            'total'       => 2360.00,
            'created_by'  => $this->admin->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id'        => $this->product->id,
            'item_name'         => $this->product->name,
            'quantity_ordered'  => 2,
            'quantity_received' => 0,
            'unit_cost'         => 1000.00,
            'total_cost'        => 2000.00,
        ]);

        $grn = $po->inwardStock([$poItem->id => 2], $this->admin->id);

        $response = $this->actingAs($this->admin)->get(route('goods-receipts.pdf', $grn));
        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }
}

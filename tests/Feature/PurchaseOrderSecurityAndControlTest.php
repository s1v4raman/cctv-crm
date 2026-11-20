<?php

namespace Tests\Feature;

use App\Helpers\GstHelper;
use App\Models\GatewaySetting;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\VendorPayment;
use App\Services\DocumentSequenceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;
use Tests\TestCase;

class PurchaseOrderSecurityAndControlTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $technician;
    protected User $worker;
    protected Supplier $localSupplier;
    protected Supplier $interstateSupplier;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin User']);
        $this->staff = User::factory()->create(['role' => 'staff', 'name' => 'Store Staff']);
        $this->technician = User::factory()->create(['role' => 'technician', 'name' => 'Field Technician']);
        $this->worker = User::factory()->create(['role' => 'worker', 'name' => 'Daily Worker']);

        $settings = GatewaySetting::getSettings();
        $settings->update([
            'company_trade_name'             => 'Precision IT Systems',
            'company_legal_name'             => 'Precision IT Systems Pvt Ltd',
            'company_gstin'                  => '33AAACP1234A1Z5',
            'company_state'                  => 'Tamil Nadu',
            'company_state_code'             => '33',
            'company_address'                => 'Chennai, Tamil Nadu',
            'po_auto_approve_limit'          => 25000.00,
            'po_receiving_tolerance_percent' => 10.00,
            'po_alert_on_goods_received'     => false,
        ]);

        $this->localSupplier = Supplier::create([
            'name'         => 'Local TN Supplier',
            'company_name' => 'Tamil Nadu Surveillance Hub',
            'phone'        => '9876543210',
            'gst_number'   => '33AABCT1234A1Z1', // Tamil Nadu (33)
            'state'        => 'Tamil Nadu',
        ]);

        $this->interstateSupplier = Supplier::create([
            'name'         => 'Interstate Supplier',
            'company_name' => 'Mumbai Electronics Ltd',
            'phone'        => '9876543211',
            'gst_number'   => '27AAACG9876B1Z2', // Maharashtra (27)
            'state'        => 'Maharashtra',
        ]);

        $this->product = Product::create([
            'sku'            => 'IP-CAM-4MP',
            'name'           => 'Hikvision 4MP IP Camera',
            'cost_price'     => 2000.00,
            'unit_price'     => 3200.00,
            'tax_rate'       => 18.00,
            'stock_quantity' => 10,
            'is_active'      => true,
        ]);
    }

    public function test_technician_and_worker_are_forbidden_403_on_all_supplier_and_po_routes(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->localSupplier->id,
            'po_number'   => 'PO-20261119-0001',
            'order_date'  => now()->toDateString(),
            'status'      => 'draft',
            'total'       => 5000.00,
        ]);

        $testedRoutes = [
            route('suppliers.index'),
            route('suppliers.create'),
            route('suppliers.show', $this->localSupplier),
            route('suppliers.suggest'),
            route('purchase-orders.index'),
            route('purchase-orders.create'),
            route('purchase-orders.show', $po),
            route('finance.payables.index'),
        ];

        foreach ([$this->technician, $this->worker] as $unauthorizedUser) {
            foreach ($testedRoutes as $targetUrl) {
                $response = $this->actingAs($unauthorizedUser)->get($targetUrl);
                $this->assertEquals(403, $response->status(), "Expected 403 for {$unauthorizedUser->role} on {$targetUrl}");
            }
        }
    }

    public function test_technician_cannot_view_cost_price_in_product_search_and_index(): void
    {
        $response = $this->actingAs($this->technician)->getJson(route('products.search', ['q' => 'Camera']));
        $response->assertOk();
        $data = $response->json();
        $this->assertNotEmpty($data);
        $firstItem = $data[0];

        $this->assertEquals(0, $firstItem['unit_price'] ?? 0);
        $this->assertArrayNotHasKey('cost_price', $firstItem);
    }

    public function test_new_po_starts_as_draft_and_staff_can_submit_for_approval(): void
    {
        $payload = [
            'supplier_id' => $this->localSupplier->id,
            'order_date'  => now()->toDateString(),
            'tax_percent' => 18,
            'items'       => [
                [
                    'product_id'       => $this->product->id,
                    'item_name'        => $this->product->name,
                    'sku'              => $this->product->sku,
                    'unit_cost'        => 2000.00,
                    'quantity_ordered' => 20, // Total = 40,000 + 18% = 47,200
                ]
            ],
        ];

        $this->actingAs($this->staff)->post(route('purchase-orders.store'), $payload);

        $po = PurchaseOrder::where('supplier_id', $this->localSupplier->id)->first();
        $this->assertNotNull($po);
        $this->assertEquals('draft', $po->status);

        // Staff submits for approval
        $this->actingAs($this->staff)->post(route('purchase-orders.submit', $po));
        $po->refresh();
        $this->assertEquals('pending_approval', $po->status);
    }

    public function test_only_admin_can_approve_or_reject_purchase_order(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->localSupplier->id,
            'po_number'   => 'PO-20261119-0002',
            'order_date'  => now()->toDateString(),
            'status'      => 'pending_approval',
            'total'       => 15000.00,
        ]);

        // Staff cannot approve
        $staffResponse = $this->actingAs($this->staff)->post(route('purchase-orders.approve', $po));
        $staffResponse->assertForbidden();

        // Admin can approve
        $adminResponse = $this->actingAs($this->admin)->post(route('purchase-orders.approve', $po));
        $adminResponse->assertRedirect();
        $po->refresh();
        $this->assertEquals('approved', $po->status);
        $this->assertEquals($this->admin->id, $po->approved_by);
        $this->assertNotNull($po->approved_at);

        // Test rejection flow on another PO
        $po2 = PurchaseOrder::create([
            'supplier_id' => $this->localSupplier->id,
            'po_number'   => 'PO-20261119-0003',
            'order_date'  => now()->toDateString(),
            'status'      => 'pending_approval',
            'total'       => 20000.00,
        ]);

        $this->actingAs($this->admin)->post(route('purchase-orders.reject', $po2), [
            'rejection_reason' => 'Rate is too high, negotiate discount',
        ]);
        $po2->refresh();
        $this->assertEquals('rejected', $po2->status);
        $this->assertEquals($this->admin->id, $po2->rejected_by);
        $this->assertEquals('Rate is too high, negotiate discount', $po2->rejection_reason);
    }

    public function test_admin_auto_approves_po_at_or_below_limit(): void
    {
        // PO within auto-approve limit of 25,000
        $payloadUnder = [
            'supplier_id' => $this->localSupplier->id,
            'order_date'  => now()->toDateString(),
            'tax_percent' => 18,
            'items'       => [
                [
                    'product_id'       => $this->product->id,
                    'item_name'        => $this->product->name,
                    'unit_cost'        => 2000.00,
                    'quantity_ordered' => 5, // 10,000 + 1,800 = 11,800 <= 25,000
                ]
            ],
        ];

        $this->actingAs($this->admin)->post(route('purchase-orders.store'), $payloadUnder);
        $poUnder = PurchaseOrder::latest('id')->first();
        $this->assertEquals('approved', $poUnder->status);
        $this->assertEquals($this->admin->id, $poUnder->approved_by);

        // PO exceeding limit of 25,000
        $payloadOver = [
            'supplier_id' => $this->localSupplier->id,
            'order_date'  => now()->toDateString(),
            'tax_percent' => 18,
            'items'       => [
                [
                    'product_id'       => $this->product->id,
                    'item_name'        => $this->product->name,
                    'unit_cost'        => 2000.00,
                    'quantity_ordered' => 20, // 40,000 + 7,200 = 47,200 > 25,000
                ]
            ],
        ];

        $this->actingAs($this->admin)->post(route('purchase-orders.store'), $payloadOver);
        $poOver = PurchaseOrder::latest('id')->first();
        $this->assertEquals('draft', $poOver->status);
    }

    public function test_state_machine_transition_map_and_illegal_jump_rejection(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->localSupplier->id,
            'po_number'   => 'PO-20261119-0004',
            'order_date'  => now()->toDateString(),
            'status'      => 'draft',
            'total'       => 5000.00,
        ]);

        // Attempt illegal jump to closed or received from draft
        $response = $this->actingAs($this->admin)->patch(route('purchase-orders.updateStatus', $po), [
            'status' => 'closed',
        ]);
        $response->assertSessionHasErrors('status');
        $po->refresh();
        $this->assertEquals('draft', $po->status);

        // Attempt manual jump to 'received'
        $response = $this->actingAs($this->admin)->patch(route('purchase-orders.updateStatus', $po), [
            'status' => 'received',
        ]);
        $response->assertSessionHasErrors('status');
        $po->refresh();
        $this->assertEquals('draft', $po->status);
    }

    public function test_cancellation_is_blocked_if_goods_received_or_payments_exist(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->localSupplier->id,
            'po_number'   => 'PO-20261119-0005',
            'order_date'  => now()->toDateString(),
            'status'      => 'sent',
            'total'       => 10000.00,
        ]);

        $item = $po->items()->create([
            'item_name'         => 'Dome Camera',
            'unit_cost'         => 1000.00,
            'quantity_ordered'  => 10,
            'quantity_received' => 2, // Stock already received
            'total_cost'        => 10000.00,
        ]);

        // Attempt to cancel
        $response = $this->actingAs($this->admin)->patch(route('purchase-orders.updateStatus', $po), [
            'status' => 'cancelled',
        ]);
        $response->assertSessionHasErrors('status');
        $po->refresh();
        $this->assertEquals('sent', $po->status);

        // Test payment block
        $po2 = PurchaseOrder::create([
            'supplier_id' => $this->localSupplier->id,
            'po_number'   => 'PO-20261119-0006',
            'order_date'  => now()->toDateString(),
            'status'      => 'sent',
            'total'       => 10000.00,
            'amount_paid' => 2000.00,
        ]);

        $response = $this->actingAs($this->admin)->patch(route('purchase-orders.updateStatus', $po2), [
            'status' => 'cancelled',
        ]);
        $response->assertSessionHasErrors('status');
    }

    public function test_approved_or_sent_po_cannot_be_edited(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->localSupplier->id,
            'po_number'   => 'PO-20261119-0007',
            'order_date'  => now()->toDateString(),
            'status'      => 'approved',
            'total'       => 5000.00,
        ]);

        $response = $this->actingAs($this->admin)->put(route('purchase-orders.update', $po), [
            'supplier_id' => $this->localSupplier->id,
            'order_date'  => now()->toDateString(),
            'items'       => [
                ['item_name' => 'Changed', 'unit_cost' => 100, 'quantity_ordered' => 1]
            ]
        ]);

        // Policy denies update on approved PO (403)
        $response->assertForbidden();
    }

    public function test_temporary_signed_po_pdf_route_expires_after_14_days(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->localSupplier->id,
            'po_number'   => 'PO-20261119-0008',
            'order_date'  => now()->toDateString(),
            'status'      => 'approved',
            'total'       => 5000.00,
        ]);

        $validUrl = URL::temporarySignedRoute('purchase-orders.public-pdf', now()->addDays(14), ['purchase_order' => $po]);

        // Access now -> 200 OK
        $response = $this->get($validUrl);
        $response->assertOk();
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');

        // Travel 15 days ahead -> 403 Forbidden (Expired signature)
        Carbon::setTestNow(now()->addDays(15));
        $expiredResponse = $this->get($validUrl);
        $expiredResponse->assertForbidden();

        Carbon::setTestNow(); // Reset
    }

    public function test_gstin_validation_and_state_derivation(): void
    {
        // Valid Tamil Nadu GSTIN
        $validGstin = '33AABCT1234A1Z1';
        $this->assertTrue(GstHelper::isValidGstin($validGstin));
        $this->assertEquals('33', GstHelper::extractStateCode($validGstin));
        $this->assertEquals('Tamil Nadu', GstHelper::getStateName('33'));

        // Valid Maharashtra GSTIN
        $mhGstin = '27AAACG9876B1Z2';
        $this->assertTrue(GstHelper::isValidGstin($mhGstin));
        $this->assertEquals('27', GstHelper::extractStateCode($mhGstin));
        $this->assertEquals('Maharashtra', GstHelper::getStateName('27'));

        // Invalid GSTINs
        $this->assertFalse(GstHelper::isValidGstin('INVALID_GSTIN'));
        $this->assertFalse(GstHelper::isValidGstin('33AAAA1234')); // too short
    }

    public function test_interstate_igst_vs_intrastate_cgst_sgst_tax_split(): void
    {
        // Company state is 33 (Tamil Nadu)
        // 1. Intrastate (Supplier in 33)
        $intraSplit = GstHelper::calculateTaxSplit(10000.00, 18.00, '33', '33');
        $this->assertFalse($intraSplit['is_interstate']);
        $this->assertEquals(9.00, $intraSplit['cgst_rate']);
        $this->assertEquals(900.00, $intraSplit['cgst_amount']);
        $this->assertEquals(9.00, $intraSplit['sgst_rate']);
        $this->assertEquals(900.00, $intraSplit['sgst_amount']);
        $this->assertEquals(0.00, $intraSplit['igst_amount']);
        $this->assertEquals(1800.00, $intraSplit['total_tax']);

        // 2. Interstate (Supplier in 27 Maharashtra)
        $interSplit = GstHelper::calculateTaxSplit(10000.00, 18.00, '27', '33');
        $this->assertTrue($interSplit['is_interstate']);
        $this->assertEquals(0.00, $interSplit['cgst_amount']);
        $this->assertEquals(0.00, $interSplit['sgst_amount']);
        $this->assertEquals(18.00, $interSplit['igst_rate']);
        $this->assertEquals(1800.00, $interSplit['igst_amount']);
        $this->assertEquals(1800.00, $interSplit['total_tax']);
    }

    public function test_document_sequence_generates_sequential_po_numbers_without_collisions(): void
    {
        $num1 = DocumentSequenceService::nextPurchaseOrderNumber('2026-11-19');
        $num2 = DocumentSequenceService::nextPurchaseOrderNumber('2026-11-19');
        $num3 = DocumentSequenceService::nextPurchaseOrderNumber('2026-11-19');

        $this->assertEquals('PO-20261119-0001', $num1);
        $this->assertEquals('PO-20261119-0002', $num2);
        $this->assertEquals('PO-20261119-0003', $num3);
    }

    public function test_inward_stock_prevents_over_receipt_beyond_tolerance_and_enforces_full_line_completion(): void
    {
        // 10% tolerance configured
        $po = PurchaseOrder::create([
            'supplier_id' => $this->localSupplier->id,
            'po_number'   => 'PO-20261119-0009',
            'order_date'  => now()->toDateString(),
            'status'      => 'sent',
            'total'       => 20000.00,
        ]);

        $item1 = $po->items()->create([
            'product_id'        => $this->product->id,
            'item_name'         => $this->product->name,
            'unit_cost'         => 2000.00,
            'quantity_ordered'  => 10, // Max allowed with 10% tolerance = 11
            'quantity_received' => 0,
            'total_cost'        => 20000.00,
        ]);

        // Attempting to inward 15 units (exceeds 10 + 10% = 11) must throw exception
        $this->expectException(InvalidArgumentException::class);
        $po->inwardStock([$item1->id => 15]);
    }

    public function test_inward_stock_transitions_to_received_only_when_all_lines_are_complete(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->localSupplier->id,
            'po_number'   => 'PO-20261119-0010',
            'order_date'  => now()->toDateString(),
            'status'      => 'sent',
            'total'       => 20000.00,
        ]);

        $item1 = $po->items()->create([
            'product_id'        => $this->product->id,
            'item_name'         => $this->product->name,
            'unit_cost'         => 2000.00,
            'quantity_ordered'  => 5,
            'quantity_received' => 0,
            'total_cost'        => 10000.00,
        ]);

        $item2 = $po->items()->create([
            'product_id'        => $this->product->id,
            'item_name'         => 'Auxiliary Power Supply',
            'unit_cost'         => 1000.00,
            'quantity_ordered'  => 10,
            'quantity_received' => 0,
            'total_cost'        => 10000.00,
        ]);

        // Inward all units of item1 only -> should be partially_received
        $po->inwardStock([$item1->id => 5]);
        $po->refresh();
        $this->assertEquals('partially_received', $po->status);

        // Inward all units of item2 -> now all lines are complete -> status becomes 'received'
        $po->inwardStock([$item2->id => 10]);
        $po->refresh();
        $this->assertEquals('received', $po->status);
    }
}

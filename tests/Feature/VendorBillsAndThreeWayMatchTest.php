<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\VendorBill;
use App\Models\VendorBillItem;
use App\Models\VendorPayment;
use App\Services\BillPaymentService;
use App\Services\ThreeWayMatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VendorBillsAndThreeWayMatchTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $technician;
    protected User $worker;
    protected Supplier $supplier;
    protected Product $productA;
    protected Product $productB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->technician = User::factory()->create(['role' => 'technician']);
        $this->worker = User::factory()->create(['role' => 'worker']);

        $this->supplier = Supplier::create([
            'name'       => 'SecureDistro Chennai',
            'email'      => 'accounts@securedistro.in',
            'phone'      => '9876543210',
            'gst_number' => '33ABCDE1234F1Z5',
            'address'    => '100 Mount Road, Chennai',
            'state'      => 'Tamil Nadu',
            'is_active'  => true,
        ]);

        $this->productA = Product::create([
            'name'           => '4K Dome Camera',
            'sku'            => 'CAM-4K-DOME',
            'purchase_price' => 5000.00,
            'selling_price'  => 7500.00,
            'stock_quantity' => 0,
            'gst_rate'       => 18.00,
            'hsn_code'       => '85258900',
            'track_serial'   => false,
            'is_active'      => true,
        ]);

        $this->productB = Product::create([
            'name'           => '16CH NVR Box',
            'sku'            => 'NVR-16CH',
            'purchase_price' => 12000.00,
            'selling_price'  => 16000.00,
            'stock_quantity' => 0,
            'gst_rate'       => 18.00,
            'hsn_code'       => '85219090',
            'track_serial'   => false,
            'is_active'      => true,
        ]);
    }

    private function createSamplePOAndReceipt(int $orderedQtyA = 10, int $receivedQtyA = 10): array
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'po_number'   => 'PO-TEST-001',
            'order_date'  => now()->toDateString(),
            'status'      => PurchaseOrder::STATUS_SENT,
            'subtotal'    => $orderedQtyA * 5000.00,
            'tax_percent' => 18.00,
            'tax_amount'  => ($orderedQtyA * 5000.00) * 0.18,
            'total'       => ($orderedQtyA * 5000.00) * 1.18,
            'created_by'  => $this->admin->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id'        => $this->productA->id,
            'item_name'         => $this->productA->name,
            'sku'               => $this->productA->sku,
            'quantity_ordered'  => $orderedQtyA,
            'quantity_received' => $receivedQtyA,
            'unit_cost'         => 5000.00,
            'tax_percent'       => 18.00,
            'tax_amount'        => ($orderedQtyA * 5000.00) * 0.18,
            'total_cost'        => ($orderedQtyA * 5000.00) * 1.18,
        ]);

        $grn = GoodsReceipt::create([
            'grn_number'        => 'GRN-TEST-001',
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'received_date'     => now()->toDateString(),
            'received_by'       => $this->admin->id,
            'delivery_note_no'  => 'DN-9988',
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id'       => $grn->id,
            'purchase_order_item_id' => $poItem->id,
            'product_id'             => $this->productA->id,
            'qty_received'           => $receivedQtyA,
            'qty_rejected'           => 0,
            'unit_cost'              => 5000.00,
        ]);

        return [$po, $poItem, $grn];
    }

    public function test_duplicate_vendor_invoice_number_blocked_per_supplier(): void
    {
        VendorBill::create([
            'bill_number'       => 'BILL-001',
            'vendor_invoice_no' => 'INV-2026-99',
            'supplier_id'       => $this->supplier->id,
            'invoice_date'      => now()->toDateString(),
            'due_date'          => now()->addDays(30)->toDateString(),
            'subtotal'          => 10000.00,
            'total'             => 11800.00,
            'status'            => VendorBill::STATUS_DRAFT,
            'match_status'      => VendorBill::MATCH_NOT_CHECKED,
            'created_by'        => $this->admin->id,
        ]);

        // Attempt duplicate invoice number for the same supplier
        $response = $this->actingAs($this->admin)->post(route('vendor-bills.store'), [
            'supplier_id'       => $this->supplier->id,
            'vendor_invoice_no' => 'INV-2026-99',
            'invoice_date'      => now()->toDateString(),
            'due_date'          => now()->addDays(30)->toDateString(),
            'items'             => [
                [
                    'product_id' => $this->productA->id,
                    'item_name'  => $this->productA->name,
                    'quantity'   => 2,
                    'unit_cost'  => 5000.00,
                    'tax_rate'   => 18.00,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('vendor_invoice_no');
    }

    public function test_three_way_match_passes_when_qty_and_price_match_po_and_grn(): void
    {
        [$po, $poItem, $grn] = $this->createSamplePOAndReceipt(10, 10);

        $bill = VendorBill::create([
            'bill_number'       => 'BILL-MATCH-01',
            'vendor_invoice_no' => 'INV-MATCH-01',
            'supplier_id'       => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'invoice_date'      => now()->toDateString(),
            'due_date'          => now()->addDays(30)->toDateString(),
            'subtotal'          => 50000.00,
            'total'             => 59000.00,
            'status'            => VendorBill::STATUS_DRAFT,
            'created_by'        => $this->admin->id,
        ]);

        VendorBillItem::create([
            'vendor_bill_id'         => $bill->id,
            'purchase_order_item_id' => $poItem->id,
            'product_id'             => $this->productA->id,
            'item_name'              => $this->productA->name,
            'quantity'               => 10,
            'unit_cost'              => 5000.00,
            'tax_rate'               => 18.00,
            'tax_amount'             => 9000.00,
            'total_amount'           => 59000.00,
        ]);

        $matcher = app(ThreeWayMatchService::class);
        $result = $matcher->verifyBill($bill);

        $this->assertTrue($result['matched']);
        $this->assertEquals(VendorBill::MATCH_MATCHED, $bill->fresh()->match_status);
        $this->assertEquals(VendorBill::STATUS_APPROVED, $bill->fresh()->status);
    }

    public function test_three_way_match_fails_when_billed_qty_exceeds_received_qty(): void
    {
        // 10 ordered, but only 5 received so far
        [$po, $poItem, $grn] = $this->createSamplePOAndReceipt(10, 5);

        $bill = VendorBill::create([
            'bill_number'       => 'BILL-QTY-FAIL',
            'vendor_invoice_no' => 'INV-OVERBILL-QTY',
            'supplier_id'       => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'invoice_date'      => now()->toDateString(),
            'due_date'          => now()->addDays(30)->toDateString(),
            'subtotal'          => 50000.00,
            'total'             => 59000.00,
            'status'            => VendorBill::STATUS_DRAFT,
            'created_by'        => $this->admin->id,
        ]);

        // Bill tries to bill for 10 units when only 5 were accepted at warehouse
        VendorBillItem::create([
            'vendor_bill_id'         => $bill->id,
            'purchase_order_item_id' => $poItem->id,
            'product_id'             => $this->productA->id,
            'item_name'              => $this->productA->name,
            'quantity'               => 10,
            'unit_cost'              => 5000.00,
            'tax_rate'               => 18.00,
            'tax_amount'             => 9000.00,
            'total_amount'           => 59000.00,
        ]);

        $matcher = app(ThreeWayMatchService::class);
        $result = $matcher->verifyBill($bill);

        $this->assertFalse($result['matched']);
        $this->assertEquals(VendorBill::MATCH_MISMATCH, $bill->fresh()->match_status);
        $this->assertEquals(VendorBill::STATUS_ON_HOLD, $bill->fresh()->status);
        $this->assertStringContainsString('Billed 10 unit(s) exceeds accepted goods receipt quantity of 5 unit(s)', $bill->fresh()->match_notes);
    }

    public function test_three_way_match_fails_when_billed_price_exceeds_po_price(): void
    {
        [$po, $poItem, $grn] = $this->createSamplePOAndReceipt(10, 10);

        $bill = VendorBill::create([
            'bill_number'       => 'BILL-PRICE-FAIL',
            'vendor_invoice_no' => 'INV-PRICE-HIKE',
            'supplier_id'       => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'invoice_date'      => now()->toDateString(),
            'due_date'          => now()->addDays(30)->toDateString(),
            'subtotal'          => 60000.00,
            'total'             => 70800.00,
            'status'            => VendorBill::STATUS_DRAFT,
            'created_by'        => $this->admin->id,
        ]);

        // PO rate was 5000.00, Vendor invoices at 6000.00
        VendorBillItem::create([
            'vendor_bill_id'         => $bill->id,
            'purchase_order_item_id' => $poItem->id,
            'product_id'             => $this->productA->id,
            'item_name'              => $this->productA->name,
            'quantity'               => 10,
            'unit_cost'              => 6000.00,
            'tax_rate'               => 18.00,
            'tax_amount'             => 10800.00,
            'total_amount'           => 70800.00,
        ]);

        $matcher = app(ThreeWayMatchService::class);
        $result = $matcher->verifyBill($bill);

        $this->assertFalse($result['matched']);
        $this->assertEquals(VendorBill::MATCH_MISMATCH, $bill->fresh()->match_status);
        $this->assertEquals(VendorBill::STATUS_ON_HOLD, $bill->fresh()->status);
        $this->assertStringContainsString('Billed rate ₹6,000.00 exceeds PO agreed rate ₹5,000.00', $bill->fresh()->match_notes);
    }

    public function test_admin_can_override_on_hold_bill_with_written_reason(): void
    {
        $bill = VendorBill::create([
            'bill_number'       => 'BILL-HOLD-01',
            'vendor_invoice_no' => 'INV-HOLD-01',
            'supplier_id'       => $this->supplier->id,
            'invoice_date'      => now()->toDateString(),
            'due_date'          => now()->addDays(30)->toDateString(),
            'subtotal'          => 10000.00,
            'total'             => 11800.00,
            'status'            => VendorBill::STATUS_ON_HOLD,
            'match_status'      => VendorBill::MATCH_MISMATCH,
            'match_notes'       => 'Price mismatch noted.',
            'created_by'        => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('vendor-bills.approve', $bill), [
            'approval_reason' => 'Authorized emergency freight surcharge agreed with distributor head.',
        ]);

        $response->assertRedirect();
        $this->assertEquals(VendorBill::STATUS_APPROVED, $bill->fresh()->status);
        $this->assertEquals($this->admin->id, $bill->fresh()->approved_by);
        $this->assertStringContainsString('Authorized emergency freight surcharge', $bill->fresh()->approval_reason);
    }

    public function test_non_admin_cannot_override_mismatched_bill(): void
    {
        $bill = VendorBill::create([
            'bill_number'       => 'BILL-HOLD-02',
            'vendor_invoice_no' => 'INV-HOLD-02',
            'supplier_id'       => $this->supplier->id,
            'invoice_date'      => now()->toDateString(),
            'due_date'          => now()->addDays(30)->toDateString(),
            'subtotal'          => 10000.00,
            'total'             => 11800.00,
            'status'            => VendorBill::STATUS_ON_HOLD,
            'match_status'      => VendorBill::MATCH_MISMATCH,
            'created_by'        => $this->admin->id,
        ]);

        $response = $this->actingAs($this->staff)->post(route('vendor-bills.approve', $bill), [
            'approval_reason' => 'Trying to approve as staff member.',
        ]);

        $response->assertStatus(403);
        $this->assertEquals(VendorBill::STATUS_ON_HOLD, $bill->fresh()->status);
    }

    public function test_over_payment_blocked_on_bill(): void
    {
        $bill = VendorBill::create([
            'bill_number'       => 'BILL-PAY-01',
            'vendor_invoice_no' => 'INV-PAY-01',
            'supplier_id'       => $this->supplier->id,
            'invoice_date'      => now()->toDateString(),
            'due_date'          => now()->addDays(30)->toDateString(),
            'subtotal'          => 10000.00,
            'total'             => 11800.00,
            'status'            => VendorBill::STATUS_APPROVED,
            'created_by'        => $this->admin->id,
        ]);

        $paymentService = app(BillPaymentService::class);

        // Attempting to disburse ₹15,000 against ₹11,800 bill
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds unpaid balance');

        $paymentService->recordPayment([
            'supplier_id'           => $this->supplier->id,
            'vendor_bill_id'        => $bill->id,
            'payment_date'          => now()->toDateString(),
            'payment_method'        => 'bank_transfer',
            'amount'                => 15000.00,
            'transaction_reference' => 'TXN-OVER-01',
        ], $this->admin->id);
    }

    public function test_single_payment_split_across_multiple_bills(): void
    {
        $bill1 = VendorBill::create([
            'bill_number'       => 'BILL-SPLIT-01',
            'vendor_invoice_no' => 'INV-SPLIT-01',
            'supplier_id'       => $this->supplier->id,
            'invoice_date'      => now()->toDateString(),
            'due_date'          => now()->addDays(30)->toDateString(),
            'subtotal'          => 10000.00,
            'total'             => 10000.00,
            'status'            => VendorBill::STATUS_APPROVED,
            'created_by'        => $this->admin->id,
        ]);

        $bill2 = VendorBill::create([
            'bill_number'       => 'BILL-SPLIT-02',
            'vendor_invoice_no' => 'INV-SPLIT-02',
            'supplier_id'       => $this->supplier->id,
            'invoice_date'      => now()->toDateString(),
            'due_date'          => now()->addDays(30)->toDateString(),
            'subtotal'          => 20000.00,
            'total'             => 20000.00,
            'status'            => VendorBill::STATUS_APPROVED,
            'created_by'        => $this->admin->id,
        ]);

        $paymentService = app(BillPaymentService::class);

        // One payment of ₹25,000: fully covers Bill 1 (₹10,000) and partially covers Bill 2 (₹15,000)
        $payment = $paymentService->recordPayment([
            'supplier_id'           => $this->supplier->id,
            'payment_date'          => now()->toDateString(),
            'payment_method'        => 'bank_transfer',
            'amount'                => 25000.00,
            'transaction_reference' => 'NEFT-SPLIT-100',
            'allocations'           => [
                $bill1->id => 10000.00,
                $bill2->id => 15000.00,
            ],
        ], $this->admin->id);

        $this->assertEquals(25000.00, (float) $payment->amount);
        $this->assertEquals(VendorBill::STATUS_PAID, $bill1->fresh()->status);
        $this->assertEquals(VendorBill::STATUS_PARTIALLY_PAID, $bill2->fresh()->status);
        $this->assertEquals(5000.00, $bill2->fresh()->balanceDue());
    }

    public function test_advance_payment_against_po_adjusted_to_later_bill(): void
    {
        [$po, $poItem, $grn] = $this->createSamplePOAndReceipt(10, 10);

        // Advance payment of ₹20,000 recorded against PO
        $paymentService = app(BillPaymentService::class);
        $advancePayment = $paymentService->recordPayment([
            'supplier_id'           => $this->supplier->id,
            'purchase_order_id'     => $po->id,
            'kind'                  => VendorPayment::KIND_ADVANCE,
            'payment_date'          => now()->toDateString(),
            'payment_method'        => 'bank_transfer',
            'amount'                => 20000.00,
            'transaction_reference' => 'ADV-TXN-001',
        ], $this->admin->id);

        $this->assertEquals(VendorPayment::KIND_ADVANCE, $advancePayment->kind);
        $this->assertEquals(20000.00, (float) $advancePayment->amount);

        // Later, vendor sends final bill for ₹59,000
        $bill = VendorBill::create([
            'bill_number'       => 'BILL-ADV-ADJUST',
            'vendor_invoice_no' => 'INV-FINAL-01',
            'supplier_id'       => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'invoice_date'      => now()->toDateString(),
            'due_date'          => now()->addDays(30)->toDateString(),
            'subtotal'          => 50000.00,
            'total'             => 59000.00,
            'status'            => VendorBill::STATUS_APPROVED,
            'created_by'        => $this->admin->id,
        ]);

        // Adjust the ₹20,000 advance against this bill
        $allocation = $paymentService->adjustAdvanceToBill($advancePayment, $bill, 20000.00, $this->admin->id);

        $this->assertEquals(20000.00, (float) $allocation->amount);
        $this->assertEquals(VendorBill::STATUS_PARTIALLY_PAID, $bill->fresh()->status);
        $this->assertEquals(39000.00, $bill->fresh()->balanceDue());
    }

    public function test_invoice_file_stored_privately_and_served_via_authorized_route(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('vendor_invoice.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('vendor-bills.store'), [
            'supplier_id'       => $this->supplier->id,
            'vendor_invoice_no' => 'INV-FILE-001',
            'invoice_date'      => now()->toDateString(),
            'due_date'          => now()->addDays(30)->toDateString(),
            'invoice_file'      => $file,
            'items'             => [
                [
                    'product_id' => $this->productA->id,
                    'item_name'  => $this->productA->name,
                    'quantity'   => 1,
                    'unit_cost'  => 5000.00,
                    'tax_rate'   => 18.00,
                ],
            ],
        ]);

        $response->assertRedirect();
        $bill = VendorBill::where('vendor_invoice_no', 'INV-FILE-001')->firstOrFail();

        $this->assertNotNull($bill->invoice_file_path);
        $this->assertStringStartsWith('vendor_invoices/', $bill->invoice_file_path);
        Storage::disk('local')->assertExists($bill->invoice_file_path);

        // Public/guest cannot download invoice file
        auth()->logout();
        $guestResponse = $this->get(route('vendor-bills.invoice-file', $bill));
        $guestResponse->assertRedirect(route('login'));

        // Admin can download invoice file
        $adminDownload = $this->actingAs($this->admin)->get(route('vendor-bills.invoice-file', $bill));
        $adminDownload->assertStatus(200);
    }

    public function test_technician_and_worker_forbidden_403_on_vendor_bills(): void
    {
        $bill = VendorBill::create([
            'bill_number'       => 'BILL-403-TEST',
            'vendor_invoice_no' => 'INV-403-TEST',
            'supplier_id'       => $this->supplier->id,
            'invoice_date'      => now()->toDateString(),
            'due_date'          => now()->addDays(30)->toDateString(),
            'subtotal'          => 5000.00,
            'total'             => 5900.00,
            'status'            => VendorBill::STATUS_APPROVED,
            'created_by'        => $this->admin->id,
        ]);

        // Technician forbidden
        $this->actingAs($this->technician)->get(route('vendor-bills.index'))->assertStatus(403);
        $this->actingAs($this->technician)->get(route('vendor-bills.show', $bill))->assertStatus(403);
        $this->actingAs($this->technician)->post(route('vendor-bills.store'), [])->assertStatus(403);

        // Worker forbidden
        $this->actingAs($this->worker)->get(route('vendor-bills.index'))->assertStatus(403);
        $this->actingAs($this->worker)->get(route('vendor-bills.show', $bill))->assertStatus(403);
        $this->actingAs($this->worker)->post(route('vendor-bills.store'), [])->assertStatus(403);
    }
}

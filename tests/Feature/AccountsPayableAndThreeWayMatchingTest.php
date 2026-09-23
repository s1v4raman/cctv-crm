<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\VendorPayment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountsPayableAndThreeWayMatchingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Finance Admin']);
        $this->staff = User::factory()->create(['role' => 'staff', 'name' => 'Store Staff']);
        $this->technician = User::factory()->create(['role' => 'technician', 'name' => 'Field Technician']);
    }

    public function test_non_admin_is_forbidden_from_accounts_payable(): void
    {
        $this->actingAs($this->staff)
            ->get(route('finance.payables.index'))
            ->assertForbidden();

        $this->actingAs($this->technician)
            ->get(route('finance.payables.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_accounts_payable_dashboard_and_aging_buckets(): void
    {
        $supplierA = Supplier::create([
            'name'                => 'Hikvision Official Distributor',
            'company_name'        => 'Hikvision India Pvt Ltd',
            'phone'               => '9876543210',
            'email'               => 'ap@hikvision-india.test',
            'gst_number'          => '29AABCH1234F1Z5',
            'credit_period_days'  => 30,
        ]);

        $supplierB = Supplier::create([
            'name'                => 'CP Plus Direct',
            'company_name'        => 'CP Plus Security Ltd',
            'phone'               => '9876543211',
            'email'               => 'orders@cpplus.test',
            'gst_number'          => '29AABCC5678F1Z8',
            'credit_period_days'  => 15,
        ]);

        // PO 1: Current / Not overdue (Bucket 0-30), Total: 50,000, Paid: 10,000, Outstanding: 40,000
        $po1 = PurchaseOrder::create([
            'po_number'              => 'PO-2026-0001',
            'supplier_id'            => $supplierA->id,
            'order_date'             => Carbon::now()->subDays(10),
            'expected_delivery_date' => Carbon::now()->subDays(5),
            'due_date'               => Carbon::now()->addDays(20),
            'status'                 => 'ordered',
            'payment_status'         => 'partially_paid',
            'total'                  => 50000,
            'amount_paid'            => 10000,
            'supplier_invoice_no'    => 'HIK-INV-1001',
            'supplier_invoice_date'  => Carbon::now()->subDays(10),
        ]);

        // PO 2: 45 days overdue (Bucket 31-60), Total: 30,000, Paid: 0, Outstanding: 30,000
        $po2 = PurchaseOrder::create([
            'po_number'              => 'PO-2026-0002',
            'supplier_id'            => $supplierA->id,
            'order_date'             => Carbon::now()->subDays(75),
            'due_date'               => Carbon::now()->subDays(45),
            'status'                 => 'ordered',
            'payment_status'         => 'unpaid',
            'total'                  => 30000,
            'amount_paid'            => 0,
            'supplier_invoice_no'    => 'HIK-INV-0950',
            'supplier_invoice_date'  => Carbon::now()->subDays(75),
        ]);

        // PO 3: 75 days overdue (Bucket 61-90), Total: 20,000, Paid: 0, Outstanding: 20,000
        $po3 = PurchaseOrder::create([
            'po_number'              => 'PO-2026-0003',
            'supplier_id'            => $supplierB->id,
            'order_date'             => Carbon::now()->subDays(105),
            'due_date'               => Carbon::now()->subDays(75),
            'status'                 => 'ordered',
            'payment_status'         => 'unpaid',
            'total'                  => 20000,
            'amount_paid'            => 0,
            'supplier_invoice_no'    => 'CPP-INV-4401',
            'supplier_invoice_date'  => Carbon::now()->subDays(105),
        ]);

        // PO 4: 100 days overdue (Bucket 90+), Total: 15,000, Paid: 0, Outstanding: 15,000
        $po4 = PurchaseOrder::create([
            'po_number'              => 'PO-2026-0004',
            'supplier_id'            => $supplierB->id,
            'order_date'             => Carbon::now()->subDays(130),
            'due_date'               => Carbon::now()->subDays(100),
            'status'                 => 'ordered',
            'payment_status'         => 'unpaid',
            'total'                  => 15000,
            'amount_paid'            => 0,
            'supplier_invoice_no'    => 'CPP-INV-3900',
            'supplier_invoice_date'  => Carbon::now()->subDays(130),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('finance.payables.index'));

        $response->assertOk();
        $response->assertViewHas('summary');
        $response->assertViewHas('suppliers');
        $response->assertViewHas('threeWayAudit');
        $response->assertViewHas('disbursements');

        $summary = $response->viewData('summary');
        $this->assertEquals(105000, $summary['total_outstanding_payable']); // 40k + 30k + 20k + 15k
        $this->assertEquals(40000, $summary['aging_0_30']);
        $this->assertEquals(30000, $summary['aging_31_60']);
        $this->assertEquals(20000, $summary['aging_61_90']);
        $this->assertEquals(15000, $summary['aging_90_plus']);
        $this->assertGreaterThan(0, $summary['dpo_days']);

        $response->assertSee('Hikvision India Pvt Ltd');
        $response->assertSee('CP Plus Security Ltd');
    }

    public function test_three_way_matching_engine_calculates_matched_partial_and_unbilled_correctly(): void
    {
        $supplier = Supplier::create([
            'name'         => 'Dahua Systems',
            'company_name' => 'Dahua Tech India',
            'phone'        => '9888877777',
            'email'        => 'dahua@test.com',
        ]);

        $productA = Product::create([
            'name'       => '4MP IP Dome Camera',
            'sku'        => 'DH-IPC-HDBW',
            'brand'      => 'Dahua',
            'category'   => 'camera',
            'unit_price' => 2500,
            'stock_qty'  => 10,
        ]);

        $productB = Product::create([
            'name'       => '8 Ch 4K NVR',
            'sku'        => 'DH-NVR-4108',
            'brand'      => 'Dahua',
            'category'   => 'nvr',
            'unit_price' => 8000,
            'stock_qty'  => 5,
        ]);

        // Case 1: Fully matched PO (ordered = 10, received = 10, invoice present)
        $poMatched = PurchaseOrder::create([
            'po_number'           => 'PO-MATCH-01',
            'supplier_id'         => $supplier->id,
            'order_date'          => Carbon::now()->subDays(5),
            'status'              => 'received',
            'payment_status'      => 'unpaid',
            'total'               => 25000,
            'amount_paid'         => 0,
            'supplier_invoice_no' => 'DAH-INV-5001',
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $poMatched->id,
            'product_id'        => $productA->id,
            'item_name'         => $productA->name,
            'quantity_ordered'  => 10,
            'quantity_received' => 10,
            'unit_cost'         => 2500,
            'total_cost'        => 25000,
        ]);

        // Case 2: Partial receipt (ordered = 10, received = 4, invoice present)
        $poPartial = PurchaseOrder::create([
            'po_number'           => 'PO-PARTIAL-02',
            'supplier_id'         => $supplier->id,
            'order_date'          => Carbon::now()->subDays(4),
            'status'              => 'partially_received',
            'payment_status'      => 'unpaid',
            'total'               => 25000,
            'amount_paid'         => 0,
            'supplier_invoice_no' => 'DAH-INV-5002',
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $poPartial->id,
            'product_id'        => $productA->id,
            'item_name'         => $productA->name,
            'quantity_ordered'  => 10,
            'quantity_received' => 4,
            'unit_cost'         => 2500,
            'total_cost'        => 25000,
        ]);

        // Case 3: Pending inward (ordered = 5, received = 0, no invoice)
        $poPending = PurchaseOrder::create([
            'po_number'           => 'PO-PENDING-03',
            'supplier_id'         => $supplier->id,
            'order_date'          => Carbon::now()->subDays(3),
            'status'              => 'ordered',
            'payment_status'      => 'unpaid',
            'total'               => 40000,
            'amount_paid'         => 0,
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $poPending->id,
            'product_id'        => $productB->id,
            'item_name'         => $productB->name,
            'quantity_ordered'  => 5,
            'quantity_received' => 0,
            'unit_cost'         => 8000,
            'total_cost'        => 40000,
        ]);

        // Case 4: Unbilled (received = 5, but supplier_invoice_no is empty)
        $poUnbilled = PurchaseOrder::create([
            'po_number'           => 'PO-UNBILLED-04',
            'supplier_id'         => $supplier->id,
            'order_date'          => Carbon::now()->subDays(2),
            'status'              => 'received',
            'payment_status'      => 'unpaid',
            'total'               => 40000,
            'amount_paid'         => 0,
            'supplier_invoice_no' => null,
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $poUnbilled->id,
            'product_id'        => $productB->id,
            'item_name'         => $productB->name,
            'quantity_ordered'  => 5,
            'quantity_received' => 5,
            'unit_cost'         => 8000,
            'total_cost'        => 40000,
        ]);

        // Test model methods
        $this->assertEquals('matched', $poMatched->getThreeWayMatchStatus()['status']);
        $this->assertEquals('partial_receipt', $poPartial->getThreeWayMatchStatus()['status']);
        $this->assertEquals('pending_inward', $poPending->getThreeWayMatchStatus()['status']);
        $this->assertEquals('unbilled', $poUnbilled->getThreeWayMatchStatus()['status']);

        // Test dashboard view
        $response = $this->actingAs($this->admin)->get(route('finance.payables.index'));
        $response->assertOk();
        $response->assertSee('PO-MATCH-01');
        $response->assertSee('PO-PARTIAL-02');
        $response->assertSee('PO-PENDING-03');
        $response->assertSee('PO-UNBILLED-04');
        $response->assertSee('3-Way Verified (Passed)');
        $response->assertSee('Partial Receipt (In Progress)');
        $response->assertSee('Pending Inwarding (GRN)');
        $response->assertSee('Inwarded / Bill Pending');
    }

    public function test_admin_can_record_vendor_payment_disbursement_and_updates_po_balance(): void
    {
        $supplier = Supplier::create([
            'name'         => 'Seagate India',
            'company_name' => 'Seagate Storage Solutions',
            'phone'        => '9876500000',
            'email'        => 'ap@seagate.test',
        ]);

        $po = PurchaseOrder::create([
            'po_number'           => 'PO-DISBURSE-01',
            'supplier_id'         => $supplier->id,
            'order_date'          => Carbon::now()->subDays(10),
            'status'              => 'received',
            'payment_status'      => 'unpaid',
            'total'               => 50000,
            'amount_paid'         => 0,
            'supplier_invoice_no' => 'SEA-INV-9901',
        ]);

        // Record Partial Payment: 30,000
        $response = $this->actingAs($this->admin)
            ->post(route('finance.payables.payment'), [
                'purchase_order_id'     => $po->id,
                'supplier_id'           => $supplier->id,
                'amount'                => 30000,
                'payment_date'          => Carbon::now()->format('Y-m-d'),
                'payment_method'        => 'bank_transfer',
                'transaction_reference' => 'UTR987654321012',
                'notes'                 => 'Advance for Surveillance HDDs',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('vendor_payments', [
            'purchase_order_id'     => $po->id,
            'supplier_id'           => $supplier->id,
            'amount'                => 30000,
            'payment_method'        => 'bank_transfer',
            'transaction_reference' => 'UTR987654321012',
            'created_by'            => $this->admin->id,
        ]);

        $po->refresh();
        $this->assertEquals(30000, $po->amount_paid);
        $this->assertEquals('partially_paid', $po->payment_status);

        // Record Remaining Payment: 20,000 -> Should transition to 'paid'
        $response2 = $this->actingAs($this->admin)
            ->post(route('finance.payables.payment'), [
                'purchase_order_id'     => $po->id,
                'amount'                => 20000,
                'payment_date'          => Carbon::now()->format('Y-m-d'),
                'payment_method'        => 'neft',
                'transaction_reference' => 'NEFT0029384756',
                'notes'                 => 'Final settlement for PO-DISBURSE-01',
            ]);

        $response2->assertRedirect();
        $po->refresh();
        $this->assertEquals(50000, $po->amount_paid);
        $this->assertEquals('paid', $po->payment_status);
        $this->assertCount(2, $po->vendorPayments);
    }

    public function test_cannot_overpay_purchase_order_beyond_outstanding_balance(): void
    {
        $supplier = Supplier::create([
            'name'         => 'Matrix Telecom',
            'company_name' => 'Matrix Comsec',
            'phone'        => '9876500001',
            'email'        => 'ap@matrix.test',
        ]);

        $po = PurchaseOrder::create([
            'po_number'           => 'PO-OVERPAY-01',
            'supplier_id'         => $supplier->id,
            'order_date'          => Carbon::now()->subDays(5),
            'status'              => 'received',
            'payment_status'      => 'unpaid',
            'total'               => 10000,
            'amount_paid'         => 0,
            'supplier_invoice_no' => 'MAT-INV-1111',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('finance.payables.payment'), [
                'purchase_order_id'     => $po->id,
                'supplier_id'           => $supplier->id,
                'amount'                => 15000, // Greater than total of 10,000
                'payment_date'          => Carbon::now()->format('Y-m-d'),
                'payment_method'        => 'upi',
                'transaction_reference' => 'UPI9922883311',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['amount']);

        $po->refresh();
        $this->assertEquals(0, $po->amount_paid);
        $this->assertEquals('unpaid', $po->payment_status);
    }

    public function test_admin_can_view_supplier_statement_ledger(): void
    {
        $supplier = Supplier::create([
            'name'         => 'Western Digital',
            'company_name' => 'Western Digital Technologies',
            'phone'        => '9876500002',
            'email'        => 'ap@wdc.test',
            'gst_number'   => '29AAACW1234F1Z1',
        ]);

        // Bill 1: 40,000
        $po1 = PurchaseOrder::create([
            'po_number'             => 'PO-WD-001',
            'supplier_id'           => $supplier->id,
            'order_date'            => Carbon::now()->subDays(20),
            'status'                => 'received',
            'payment_status'        => 'partially_paid',
            'total'                 => 40000,
            'amount_paid'           => 15000,
            'supplier_invoice_no'   => 'WD-INV-401',
            'supplier_invoice_date' => Carbon::now()->subDays(20),
        ]);

        // Payment 1: 15,000 against PO 1
        VendorPayment::create([
            'payment_reference'     => 'VPAY-2026-0001',
            'supplier_id'           => $supplier->id,
            'purchase_order_id'     => $po1->id,
            'payment_date'          => Carbon::now()->subDays(15),
            'amount'                => 15000,
            'payment_method'        => 'bank_transfer',
            'transaction_reference' => 'UTR1122334455',
            'notes'                 => 'Cheque Clearance',
        ]);

        // Bill 2: 25,000
        PurchaseOrder::create([
            'po_number'             => 'PO-WD-002',
            'supplier_id'           => $supplier->id,
            'order_date'            => Carbon::now()->subDays(10),
            'status'                => 'received',
            'payment_status'        => 'unpaid',
            'total'                 => 25000,
            'amount_paid'           => 0,
            'supplier_invoice_no'   => 'WD-INV-402',
            'supplier_invoice_date' => Carbon::now()->subDays(10),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('finance.payables.supplier', $supplier->id));

        $response->assertOk();
        $response->assertViewHas('supplier');
        $response->assertViewHas('ledger');

        $ledger = $response->viewData('ledger');
        $this->assertEquals(65000, $ledger['total_billed']); // 40k + 25k
        $this->assertEquals(15000, $ledger['total_paid']);   // 15k
        $this->assertEquals(50000, $ledger['net_balance']);  // 50k
        $this->assertCount(3, $ledger['transactions']);

        $response->assertSee('Western Digital Technologies');
        $response->assertSee('PO-WD-001');
        $response->assertSee('PO-WD-002');
        $response->assertSee('VPAY-2026-0001');
    }

    public function test_admin_can_download_supplier_statement_pdf(): void
    {
        $supplier = Supplier::create([
            'name'         => 'Finolex Cables',
            'company_name' => 'Finolex Cables Ltd',
            'phone'        => '9876500003',
            'email'        => 'ap@finolex.test',
        ]);

        PurchaseOrder::create([
            'po_number'           => 'PO-FIN-001',
            'supplier_id'         => $supplier->id,
            'order_date'          => Carbon::now()->subDays(10),
            'status'              => 'received',
            'payment_status'      => 'unpaid',
            'total'               => 18000,
            'amount_paid'         => 0,
            'supplier_invoice_no' => 'FIN-INV-881',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('finance.payables.supplier.pdf', $supplier->id));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type') ?? '');
    }

    public function test_admin_can_download_accounts_payable_pdf_and_csv_reports(): void
    {
        $supplier = Supplier::create([
            'name'         => 'Honeywell Security',
            'company_name' => 'Honeywell Building Solutions',
            'phone'        => '9876500004',
            'email'        => 'ap@honeywell.test',
        ]);

        $po = PurchaseOrder::create([
            'po_number'           => 'PO-HON-001',
            'supplier_id'         => $supplier->id,
            'order_date'          => Carbon::now()->subDays(12),
            'status'              => 'received',
            'payment_status'      => 'unpaid',
            'total'               => 75000,
            'amount_paid'         => 0,
            'supplier_invoice_no' => 'HON-INV-301',
        ]);

        // PDF Report
        $pdfResponse = $this->actingAs($this->admin)
            ->get(route('finance.payables.export-pdf'));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('Content-Type') ?? '');

        // AP Aging CSV Export
        $csvResponse = $this->actingAs($this->admin)
            ->get(route('finance.payables.export-csv'));
        $csvResponse->assertOk();
        $this->assertStringContainsString('text/csv', $csvResponse->headers->get('Content-Type') ?? '');
        $this->assertStringContainsString('Honeywell Building Solutions', $csvResponse->getContent());

        // 3-Way Match CSV Export
        $threeWayCsvResponse = $this->actingAs($this->admin)
            ->get(route('finance.payables.export-three-way-csv'));
        $threeWayCsvResponse->assertOk();
        $this->assertStringContainsString('text/csv', $threeWayCsvResponse->headers->get('Content-Type') ?? '');
        $this->assertStringContainsString('PO-HON-001', $threeWayCsvResponse->getContent());
    }
}

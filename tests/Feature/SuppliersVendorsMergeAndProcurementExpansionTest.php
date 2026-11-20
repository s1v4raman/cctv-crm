<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReturn;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierDebitNote;
use App\Models\User;
use App\Services\PurchaseReturnService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuppliersVendorsMergeAndProcurementExpansionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $technician;
    protected User $worker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'  => 'admin',
            'email' => 'admin_exp_' . uniqid() . '@example.com',
        ]);

        $this->staff = User::factory()->create([
            'role'  => 'staff',
            'email' => 'staff_exp_' . uniqid() . '@example.com',
        ]);

        $this->technician = User::factory()->create([
            'role'  => 'technician',
            'email' => 'tech_exp_' . uniqid() . '@example.com',
        ]);

        $this->worker = User::factory()->create([
            'role'  => 'worker',
            'email' => 'worker_exp_' . uniqid() . '@example.com',
        ]);
    }

    public function test_supplier_model_has_type_and_status_and_debit_notes(): void
    {
        $supplier = Supplier::create([
            'name'         => 'Test Micro Distributor',
            'company_name' => 'Test Micro Distributor Pvt Ltd',
            'type'         => Supplier::TYPE_DISTRIBUTOR,
            'status'       => Supplier::STATUS_APPROVED,
        ]);

        $this->assertNotEmpty($supplier->name_key);
        $this->assertEquals(Supplier::TYPE_DISTRIBUTOR, $supplier->type);
        $this->assertEquals(Supplier::STATUS_APPROVED, $supplier->status);
    }

    public function test_bom_creates_or_uses_supplier_with_local_shop_type(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-2026-TEST1',
            'title'        => 'CCTV Installation Project',
            'company_name' => 'Hospital Client',
            'status'       => 'in_progress',
        ]);

        $response = $this->actingAs($this->admin)->post(route('projects.materials.store', $project), [
            'item_name'    => 'PVC Conduits 25mm',
            'quantity'     => 50,
            'unit'         => 'mtr',
            'unit_cost'    => 45.00,
            'source'       => 'local_purchase',
            'shop_name'    => 'New City Hardware & Electricals',
        ]);

        $response->assertRedirect();

        $supplier = Supplier::where('name_key', Supplier::normalizeNameKey('New City Hardware & Electricals'))->first();
        $this->assertNotNull($supplier);
        $this->assertEquals(Supplier::TYPE_LOCAL_SHOP, $supplier->type);
        $this->assertEquals(Supplier::STATUS_PENDING, $supplier->status);

        $material = ProjectMaterial::where('project_id', $project->id)->first();
        $this->assertNotNull($material);
        $this->assertEquals($supplier->id, $material->supplier_id);
        $this->assertEquals($supplier->id, $material->supplier->id);
    }

    public function test_create_purchase_order_from_bom_groups_by_supplier(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-2026-TEST2',
            'title'        => 'Network Revamp Project',
            'company_name' => 'Corporate Client',
            'status'       => 'in_progress',
        ]);

        $supplierA = Supplier::create([
            'name'         => 'Supplier Alpha',
            'company_name' => 'Supplier Alpha Electronics',
            'type'         => Supplier::TYPE_DISTRIBUTOR,
            'status'       => Supplier::STATUS_APPROVED,
        ]);

        $supplierB = Supplier::create([
            'name'         => 'Supplier Beta',
            'company_name' => 'Supplier Beta Hardware',
            'type'         => Supplier::TYPE_LOCAL_SHOP,
            'status'       => Supplier::STATUS_APPROVED,
        ]);

        $productA = Product::create([
            'name'           => '8 Channel NVR',
            'sku'            => 'NVR-8CH-TEST',
            'stock_quantity' => 1,
            'cost_price'     => 5000.00,
            'selling_price'  => 6500.00,
        ]);

        $productB = Product::create([
            'name'           => 'Cat6 Cable Roll 305m',
            'sku'            => 'CAT6-ROLL-TEST',
            'stock_quantity' => 0,
            'cost_price'     => 3500.00,
            'selling_price'  => 4500.00,
        ]);

        $bomItemA = ProjectMaterial::create([
            'project_id'    => $project->id,
            'product_id'    => $productA->id,
            'supplier_id'   => $supplierA->id,
            'item_name'     => '8 Channel NVR',
            'quantity'      => 4, // shortage of 3
            'unit'          => 'Nos',
            'unit_cost'     => 5000.00,
            'source'        => 'warehouse',
        ]);

        $bomItemB = ProjectMaterial::create([
            'project_id'    => $project->id,
            'product_id'    => $productB->id,
            'supplier_id'   => $supplierB->id,
            'item_name'     => 'Cat6 Cable Roll 305m',
            'quantity'      => 2, // local purchase
            'unit'          => 'Roll',
            'unit_cost'     => 3500.00,
            'source'        => 'local_purchase',
        ]);

        // Preview route
        $previewRes = $this->actingAs($this->staff)->get(route('projects.purchase-orders.bom-preview', $project));
        $previewRes->assertOk();
        $previewRes->assertJsonStructure([
            'project_id',
            'eligible_count',
            'items',
            'suppliers',
        ]);

        // Generate POs from BOM
        $generateRes = $this->actingAs($this->staff)->post(route('projects.purchase-orders.from-bom', $project), [
            'items' => [
                [
                    'bom_item_id' => $bomItemA->id,
                    'supplier_id' => $supplierA->id,
                    'product_id'  => $productA->id,
                    'quantity'    => 3,
                    'unit_cost'   => 5000.00,
                ],
                [
                    'bom_item_id' => $bomItemB->id,
                    'supplier_id' => $supplierB->id,
                    'product_id'  => $productB->id,
                    'quantity'    => 2,
                    'unit_cost'   => 3500.00,
                ],
            ],
        ]);

        $generateRes->assertRedirect();

        // 2 draft POs created, one per supplier
        $poA = PurchaseOrder::where('project_id', $project->id)->where('supplier_id', $supplierA->id)->first();
        $this->assertNotNull($poA);
        $this->assertEquals(PurchaseOrder::STATUS_DRAFT, $poA->status);
        $this->assertEquals(1, $poA->items()->count());
        $this->assertEquals($bomItemA->id, $poA->items()->first()->bom_item_id);

        $poB = PurchaseOrder::where('project_id', $project->id)->where('supplier_id', $supplierB->id)->first();
        $this->assertNotNull($poB);
        $this->assertEquals(PurchaseOrder::STATUS_DRAFT, $poB->status);
        $this->assertEquals($bomItemB->id, $poB->items()->first()->bom_item_id);
    }

    public function test_purchase_return_deducts_stock_and_creates_debit_note_reducing_balance(): void
    {
        $supplier = Supplier::create([
            'name'         => 'Hikvision Main Distributor',
            'company_name' => 'Hikvision Main Distributor India',
            'type'         => Supplier::TYPE_DISTRIBUTOR,
            'status'       => Supplier::STATUS_APPROVED,
        ]);

        $product = Product::create([
            'name'           => 'Dome Camera 2MP',
            'sku'            => 'CAM-DOME-2MP',
            'stock_quantity' => 10,
            'cost_price'     => 1200.00,
            'selling_price'  => 1600.00,
        ]);

        $po = PurchaseOrder::create([
            'po_number'    => 'PO-20261119-9001',
            'supplier_id'  => $supplier->id,
            'order_date'   => now(),
            'status'       => PurchaseOrder::STATUS_RECEIVED,
            'subtotal'     => 12000.00,
            'tax_amount'   => 2160.00,
            'total'        => 14160.00,
            'amount_paid'  => 0.00,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id'        => $product->id,
            'item_name'         => $product->name,
            'quantity_ordered'  => 10,
            'quantity_received' => 10,
            'unit_cost'         => 1200.00,
            'total_cost'        => 14160.00,
        ]);

        $grn = GoodsReceipt::create([
            'grn_number'        => 'GRN-20261119-9001',
            'purchase_order_id' => $po->id,
            'supplier_id'       => $supplier->id,
            'received_date'     => now(),
            'received_by'       => $this->admin->id,
        ]);

        $grnItem = GoodsReceiptItem::create([
            'goods_receipt_id'       => $grn->id,
            'purchase_order_item_id' => $poItem->id,
            'product_id'             => $product->id,
            'qty_received'           => 10,
            'qty_rejected'           => 0,
            'unit_cost'              => 1200.00,
        ]);

        // Initial balance payable equals 14,160
        $this->assertEquals(14160.00, $supplier->fresh()->balancePayable());

        // Return 2 units
        $service = app(PurchaseReturnService::class);
        $return = $service->createReturn([
            'goods_receipt_id' => $grn->id,
            'return_date'      => now()->format('Y-m-d'),
            'reason'           => 'Transit crack on lens',
            'items'            => [
                [
                    'goods_receipt_item_id' => $grnItem->id,
                    'product_id'            => $product->id,
                    'quantity'              => 2,
                    'unit_cost'             => 1200.00,
                    'tax_rate'              => 18.00,
                    'reason'                => 'Cracked lens',
                ],
            ],
        ], $this->admin->id);

        $this->assertEquals(PurchaseReturn::STATUS_DRAFT, $return->status);
        $this->assertEquals(2400.00, (float) $return->subtotal);
        $this->assertEquals(432.00, (float) $return->tax_amount);
        $this->assertEquals(2832.00, (float) $return->total_amount);

        // Confirm return
        $confirmedReturn = $service->confirmReturn($return, $this->admin->id);
        $this->assertEquals(PurchaseReturn::STATUS_CONFIRMED, $confirmedReturn->status);
        $this->assertNotEmpty($confirmedReturn->debit_note_number);

        // Stock must be reduced from 10 to 8
        $this->assertEquals(8, $product->fresh()->stock_quantity);

        // Stock movement of type return_to_vendor must exist
        $movement = StockMovement::where('reference_type', PurchaseReturn::class)
            ->where('reference_id', $confirmedReturn->id)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(StockMovement::TYPE_RETURN_TO_VENDOR, $movement->movement_type);
        $this->assertEquals(-2, $movement->qty);

        // Active Debit Note must exist
        $debitNote = SupplierDebitNote::where('purchase_return_id', $confirmedReturn->id)->first();
        $this->assertNotNull($debitNote);
        $this->assertEquals(SupplierDebitNote::STATUS_ACTIVE, $debitNote->status);
        $this->assertEquals(2832.00, (float) $debitNote->total_amount);

        // Balance payable must now be 14160 - 2832 = 11328
        $this->assertEquals(11328.00, $supplier->fresh()->balancePayable());
    }

    public function test_procurement_reports_accessible_by_office_staff(): void
    {
        $resStock = $this->actingAs($this->staff)->get(route('reports.procurement.index', ['tab' => 'stock']));
        $resStock->assertOk();

        $resPurchases = $this->actingAs($this->staff)->get(route('reports.procurement.index', ['tab' => 'purchases']));
        $resPurchases->assertOk();

        $resPayables = $this->actingAs($this->staff)->get(route('reports.procurement.index', ['tab' => 'payables']));
        $resPayables->assertOk();

        $resProjects = $this->actingAs($this->staff)->get(route('reports.procurement.index', ['tab' => 'projects']));
        $resProjects->assertOk();
    }

    public function test_csv_exports_admin_only_and_staff_forbidden(): void
    {
        // Admin can export CSV
        $resAdmin = $this->actingAs($this->admin)->get(route('reports.procurement.stock-on-hand.csv'));
        $resAdmin->assertOk();

        $resAdminPurchases = $this->actingAs($this->admin)->get(route('reports.procurement.purchases.csv', ['dimension' => 'supplier']));
        $resAdminPurchases->assertOk();

        $resAdminPayables = $this->actingAs($this->admin)->get(route('reports.procurement.vendor-outstanding.csv'));
        $resAdminPayables->assertOk();

        $resAdminProjects = $this->actingAs($this->admin)->get(route('reports.procurement.project-material-cost.csv'));
        $resAdminProjects->assertOk();

        // Staff is forbidden from CSV export (HTTP 403)
        $resStaff = $this->actingAs($this->staff)->get(route('reports.procurement.stock-on-hand.csv'));
        $resStaff->assertForbidden();

        $resStaffPurchases = $this->actingAs($this->staff)->get(route('reports.procurement.purchases.csv'));
        $resStaffPurchases->assertForbidden();

        $resStaffPayables = $this->actingAs($this->staff)->get(route('reports.procurement.vendor-outstanding.csv'));
        $resStaffPayables->assertForbidden();

        $resStaffProjects = $this->actingAs($this->staff)->get(route('reports.procurement.project-material-cost.csv'));
        $resStaffProjects->assertForbidden();
    }

    public function test_technician_and_worker_forbidden_from_all_procurement_endpoints(): void
    {
        $users = [$this->technician, $this->worker];

        foreach ($users as $user) {
            $this->actingAs($user)->get(route('purchase-returns.index'))->assertForbidden();
            $this->actingAs($user)->get(route('purchase-returns.create'))->assertForbidden();
            $this->actingAs($user)->post(route('purchase-returns.store'), [])->assertForbidden();
            $this->actingAs($user)->get(route('reports.procurement.index'))->assertForbidden();
            $this->actingAs($user)->get(route('reports.procurement.stock-on-hand.csv'))->assertForbidden();
        }
    }
}

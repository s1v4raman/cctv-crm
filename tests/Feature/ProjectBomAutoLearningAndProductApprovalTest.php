<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\Site;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectBomAutoLearningAndProductApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Site $site;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@precisionit.com',
        ]);

        $this->company = Company::firstOrCreate([
            'name' => 'Precision IT Systems',
        ], [
            'is_active' => true,
        ]);

        $this->site = Site::create([
            'site_code' => 'SITE-TEST-001',
            'name' => 'Test Tech Park',
            'address' => '123 Tech Zone, Coimbatore',
            'city' => 'Coimbatore',
            'client_name' => 'Tech Corp',
        ]);
    }

    public function test_wizard_create_page_passes_products_and_vendors_and_renders_datalists(): void
    {
        Product::create([
            'name' => 'Dome IP Camera 4MP',
            'name_key' => 'dome ip camera 4mp',
            'sku' => 'CAM-001',
            'unit' => 'Nos',
            'cost_price' => 1200,
            'unit_price' => 1800,
            'status' => 'approved',
        ]);

        Vendor::create([
            'name' => 'Hikvision Authorised Distributor',
            'name_key' => 'hikvision authorised distributor',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->get(route('projects.create'));

        $response->assertOk();
        $response->assertSee('id="bom-products-list"', false);
        $response->assertSee('id="bom-vendors-list"', false);
        $response->assertSee('Dome IP Camera 4MP');
        $response->assertSee('Hikvision Authorised Distributor');
        $response->assertSee('New item. Will be saved for next time.');
    }

    public function test_auto_learning_normalizes_names_and_persists_pending_items_on_submit(): void
    {
        $payload = [
            'action' => 'submit',
            'site_id' => $this->site->id,
            'title' => 'CCTV Security Setup',
            'project_type' => 'hardware_cctv',
            'priority' => 'high',
            'materials' => [
                [
                    'item_name' => '   4U   Rack  ',
                    'quantity' => 1,
                    'unit' => 'nos',
                    'source' => 'local_purchase',
                    'shop_name' => '  R.G.B.   Infotech  ',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('projects.store'), $payload);

        $response->assertRedirect();

        // Verify Product was auto-learned with trimmed collapsed lowercase name_key and pending status
        $product = Product::where('name_key', '4u rack')->first();
        $this->assertNotNull($product);
        $this->assertEquals('4U Rack', $product->name);
        $this->assertEquals('pending', $product->status);
        $this->assertEquals($this->admin->id, $product->created_by);

        // Verify Vendor was auto-learned with trimmed collapsed lowercase name_key and pending status
        $vendor = Vendor::where('name_key', 'r.g.b. infotech')->first();
        $this->assertNotNull($vendor);
        $this->assertEquals('R.G.B. Infotech', $vendor->name);
        $this->assertEquals('pending', $vendor->status);
        $this->assertEquals($this->admin->id, $vendor->created_by);

        // Verify ProjectMaterial was saved referencing product_id and vendor_id
        $material = ProjectMaterial::first();
        $this->assertNotNull($material);
        $this->assertEquals($product->id, $material->product_id);
        $this->assertEquals($vendor->id, $material->vendor_id);
        $this->assertEquals('4U Rack', $material->item_name);
        $this->assertEquals('local_purchase', $material->source);
    }

    public function test_name_normalization_collapses_case_and_whitespace_reusing_existing_record(): void
    {
        // Pre-create '4U Rack'
        $existingProduct = Product::create([
            'name' => '4U Rack',
            'name_key' => '4u rack',
            'sku' => 'RCK-4U',
            'status' => 'approved',
            'unit' => 'Nos',
            'cost_price' => 1500,
            'unit_price' => 2200,
        ]);

        $existingVendor = Vendor::create([
            'name' => 'R.G.B. Infotech',
            'name_key' => 'r.g.b. infotech',
            'status' => 'approved',
        ]);

        // Submit with different casing and spacing: '  4u    rack ' and '  R.g.B.   Infotech '
        $payload = [
            'action' => 'submit',
            'site_id' => $this->site->id,
            'title' => 'Warehouse Security Installation',
            'project_type' => 'hardware_cctv',
            'priority' => 'medium',
            'materials' => [
                [
                    'item_name' => '  4u    rack ',
                    'quantity' => 2,
                    'unit' => 'nos',
                    'source' => 'local_purchase',
                    'shop_name' => '  r.g.b.   infotech ',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('projects.store'), $payload);
        $response->assertRedirect();

        // Product count must remain 1
        $this->assertEquals(1, Product::where('name_key', '4u rack')->count());
        $this->assertEquals(1, Vendor::where('name_key', 'r.g.b. infotech')->count());

        $material = ProjectMaterial::latest('id')->first();
        $this->assertEquals($existingProduct->id, $material->product_id);
        $this->assertEquals($existingVendor->id, $material->vendor_id);
    }

    public function test_auto_learning_works_on_save_draft(): void
    {
        $payload = [
            'action' => 'save_draft',
            'site_id' => $this->site->id,
            'title' => 'Draft Project with Materials',
            'materials' => [
                [
                    'item_name' => 'Cat6 Patch Cord 1M',
                    'quantity' => 10,
                    'unit' => 'nos',
                    'source' => 'local_purchase',
                    'shop_name' => 'Local Computer Market',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('projects.store'), $payload);
        $response->assertRedirect();

        $product = Product::where('name_key', 'cat6 patch cord 1m')->first();
        $this->assertNotNull($product);
        $this->assertEquals('pending', $product->status);

        $vendor = Vendor::where('name_key', 'local computer market')->first();
        $this->assertNotNull($vendor);
        $this->assertEquals('pending', $vendor->status);

        $material = ProjectMaterial::first();
        $this->assertEquals($product->id, $material->product_id);
        $this->assertEquals($vendor->id, $material->vendor_id);
    }

    public function test_vendor_is_not_created_when_source_is_warehouse(): void
    {
        $payload = [
            'action' => 'submit',
            'site_id' => $this->site->id,
            'title' => 'Warehouse Source Project',
            'project_type' => 'hardware_cctv',
            'priority' => 'low',
            'materials' => [
                [
                    'item_name' => 'Internal Warehouse Cable Roll',
                    'quantity' => 1,
                    'unit' => 'roll',
                    'source' => 'warehouse',
                    'shop_name' => 'Some Vendor Name', // Should be ignored because source is warehouse
                ],
            ],
        ];

        $this->actingAs($this->admin)->post(route('projects.store'), $payload);

        $this->assertDatabaseMissing('vendors', [
            'name_key' => 'some vendor name',
        ]);

        $material = ProjectMaterial::first();
        $this->assertNull($material->vendor_id);
        $this->assertNotNull($material->product_id);
    }

    public function test_products_page_lists_pending_items_and_supports_filtering(): void
    {
        Product::create([
            'name' => 'Approved Product A',
            'name_key' => 'approved product a',
            'sku' => 'PRD-AAA',
            'status' => 'approved',
            'unit' => 'Nos',
            'cost_price' => 100,
            'unit_price' => 200,
        ]);

        Product::create([
            'name' => 'Pending Product B',
            'name_key' => 'pending product b',
            'sku' => 'PRD-BBB',
            'status' => 'pending',
            'unit' => 'Nos',
            'cost_price' => 0,
            'unit_price' => 0,
        ]);

        // Access all products
        $response = $this->actingAs($this->admin)->get(route('products.index'));
        $response->assertOk();
        $this->assertEquals(2, $response->viewData('products')->total());
        $response->assertSee('Approved Product A');
        $response->assertSee('Pending Product B');
        $response->assertSee('Pending Review');

        // Filter by pending only
        $pendingResponse = $this->actingAs($this->admin)->get(route('products.index', ['status' => 'pending']));
        $pendingResponse->assertOk();
        $this->assertEquals(1, $pendingResponse->viewData('products')->total());
        $this->assertEquals('Pending Product B', $pendingResponse->viewData('products')->first()->name);
    }

    public function test_admin_can_approve_pending_product_with_price_and_category(): void
    {
        $product = Product::create([
            'name' => 'Pending Router Switch',
            'name_key' => 'pending router switch',
            'sku' => 'PRD-RTR-01',
            'status' => 'pending',
            'unit' => 'Nos',
            'cost_price' => 0,
            'unit_price' => 0,
        ]);

        $response = $this->actingAs($this->admin)->post(route('products.approve', $product), [
            'category' => 'networking',
            'cost_price' => 2500,
            'unit_price' => 3800,
            'unit' => 'Nos',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $product->refresh();
        $this->assertEquals('approved', $product->status);
        $this->assertEquals('networking', $product->category);
        $this->assertEquals(2500.00, (float)$product->cost_price);
        $this->assertEquals(3800.00, (float)$product->unit_price);
    }

    public function test_admin_can_merge_duplicate_product_reassigning_materials(): void
    {
        $masterProduct = Product::create([
            'name' => '4U Server Rack Standard',
            'name_key' => '4u server rack standard',
            'sku' => 'RCK-MASTER',
            'status' => 'approved',
            'unit' => 'Nos',
            'cost_price' => 1800,
            'unit_price' => 2500,
        ]);

        $duplicateProduct = Product::create([
            'name' => '4u rack duplicate',
            'name_key' => '4u rack duplicate',
            'sku' => 'RCK-DUP',
            'status' => 'pending',
            'unit' => 'Nos',
            'cost_price' => 0,
            'unit_price' => 0,
        ]);

        $project = Project::create([
            'site_id' => $this->site->id,
            'project_code' => 'PRJ-2026-TEST',
            'title' => 'Test Project',
            'project_type' => 'hardware_cctv',
            'priority' => 'medium',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        $material = ProjectMaterial::create([
            'project_id' => $project->id,
            'product_id' => $duplicateProduct->id,
            'item_name' => '4u rack duplicate',
            'quantity' => 1,
            'unit' => 'nos',
            'source' => 'local_purchase',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('products.merge', $duplicateProduct), [
            'target_product_id' => $masterProduct->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        // Material should now point to masterProduct
        $material->refresh();
        $this->assertEquals($masterProduct->id, $material->product_id);

        // Duplicate product should be deleted
        $this->assertDatabaseMissing('products', [
            'id' => $duplicateProduct->id,
        ]);
    }
}

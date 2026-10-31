<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\ProjectMaterialIssue;
use App\Models\ProjectTask;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\TaskMaterialConsumption;
use App\Models\User;
use App\Models\Worker;
use App\Services\ProjectMaterialLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cable and hardware given to a site from the storeroom, used there, returned,
 * and what the storeroom still holds, per product.
 */
class ProjectStockByProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Project $project;
    private ProjectTask $task;
    private Product $cable;
    private Product $camera;
    private Worker $worker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->project = Project::create([
            'project_code' => 'PRJ-GREEN-001',
            'title' => 'Green Park School campus CCTV',
            'status' => 'in_progress',
            'created_by' => $this->admin->id,
            'per_metre_rate' => 8.00,
        ]);
        $this->task = ProjectTask::create([
            'project_id' => $this->project->id,
            'phase_name' => 'Phase 1: Cabling',
            'title' => 'Cable laying – Block A',
            'priority' => 'high',
            'status' => 'in_progress',
        ]);
        $this->cable = Product::create([
            'name' => 'CAT6 Cable', 'sku' => 'CAB-CAT6', 'category' => 'Cable', 'unit' => 'Mtr',
            'cost_price' => 18, 'unit_price' => 28, 'stock_quantity' => 900, 'min_stock_alert' => 300,
        ]);
        $this->camera = Product::create([
            'name' => '2MP IP Dome Camera', 'sku' => 'CAM-2MP-DOME', 'category' => 'Camera', 'unit' => 'Nos',
            'cost_price' => 1850, 'unit_price' => 2600, 'stock_quantity' => 40, 'min_stock_alert' => 10,
        ]);
        ProjectMaterial::create([
            'project_id' => $this->project->id, 'product_id' => $this->cable->id, 'item_name' => 'CAT6 Cable',
            'quantity' => 600, 'unit' => 'meters', 'source' => 'warehouse',
        ]);
        ProjectMaterial::create([
            'project_id' => $this->project->id, 'product_id' => $this->camera->id, 'item_name' => '2MP IP Dome Camera',
            'quantity' => 32, 'unit' => 'nos', 'source' => 'warehouse',
        ]);
        $this->worker = Worker::create(['name' => 'Murugan K', 'phone' => '9003044556', 'daily_rate' => 700, 'is_active' => true]);
    }

    private function give(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->admin)->post(route('projects.material-issues.store', $this->project), array_merge([
            'type' => 'issue',
            'product_id' => $this->cable->id,
            'quantity' => 305,
            'handled_by' => 'worker:' . $this->worker->id,
            'issued_on' => now()->toDateString(),
            'drum_or_batch_no' => 'D-0425',
        ], $overrides));
    }

    private function logUse(Product $product, float $qty, float $wastage = 0, bool $deduct = true): void
    {
        $this->actingAs($this->admin)->post(route('projects.material-consumptions.store', $this->project), [
            'project_task_id' => $this->task->id,
            'product_id' => $product->id,
            'item_name' => $product->name,
            'quantity_used' => $qty,
            'unit' => $product->id === $this->cable->id ? 'meters' : 'nos',
            'wastage_quantity' => $wastage,
            'deduct_from_stock' => $deduct ? 1 : 0,
        ])->assertRedirect();
    }

    public function test_giving_cable_to_site_takes_it_out_of_the_storeroom(): void
    {
        $this->give()->assertRedirect(route('projects.show', [$this->project, 'tab' => 'materials']));

        $this->assertSame(595, $this->cable->fresh()->stock_quantity);
        $issue = ProjectMaterialIssue::sole();
        $this->assertSame('issue', $issue->type);
        $this->assertSame(305, $issue->quantity);
        $this->assertSame($this->worker->id, $issue->worker_id);
        $this->assertSame('D-0425', $issue->drum_or_batch_no);

        $movement = StockMovement::find($issue->stock_movement_id);
        $this->assertSame('out', $movement->type);
        $this->assertSame(305, $movement->quantity);
        $this->assertSame(595, $movement->balance_after);
        $this->assertSame(Project::class, $movement->reference_type);
    }

    public function test_cannot_give_more_than_the_storeroom_holds(): void
    {
        $this->give(['quantity' => 901])->assertSessionHas('error');

        $this->assertSame(900, $this->cable->fresh()->stock_quantity);
        $this->assertSame(0, ProjectMaterialIssue::count());
    }

    public function test_usage_after_giving_is_not_taken_from_the_storeroom_twice(): void
    {
        $this->give();
        $this->logUse($this->cable, 200, 10);

        $this->assertSame(595, $this->cable->fresh()->stock_quantity);
        $use = TaskMaterialConsumption::sole();
        $this->assertFalse($use->deduct_from_stock);
        $this->assertNull($use->stock_movement_id);
    }

    public function test_usage_without_giving_still_deducts_and_counts_as_given(): void
    {
        $this->logUse($this->camera, 4);

        $this->assertSame(36, $this->camera->fresh()->stock_quantity);

        $row = collect(ProjectMaterialLedger::forProject($this->project->fresh())['rows'])->firstWhere('product_id', $this->camera->id);
        $this->assertEquals(4, $row['given']);
        $this->assertEquals(4, $row['used']);
        $this->assertEquals(0, $row['balance']);
    }

    public function test_ledger_works_out_given_used_balance_storeroom_and_on_order(): void
    {
        $this->give();                                    // 305 m out
        $this->give(['quantity' => 175]);                 // 480 m given
        $this->logUse($this->cable, 300, 20);             // pulled 300, wasted 20
        $this->give(['type' => 'return', 'quantity' => 60]); // 60 m back

        $supplier = Supplier::create(['name' => 'Chennai Security Distributors', 'phone' => '9840012345']);
        $po = PurchaseOrder::create([
            'supplier_id' => $supplier->id, 'po_number' => 'PO-20261101-0001', 'order_date' => now()->toDateString(),
            'status' => 'ordered', 'total' => 8540,
        ]);
        $po->items()->create([
            'product_id' => $this->cable->id, 'item_name' => 'CAT6 Cable', 'sku' => 'CAB-CAT6',
            'unit_cost' => 28, 'quantity_ordered' => 305, 'quantity_received' => 0, 'total_cost' => 8540,
        ]);

        $ledger = ProjectMaterialLedger::forProject($this->project->fresh());
        $row = collect($ledger['rows'])->firstWhere('product_id', $this->cable->id);

        $this->assertEquals(600, $row['planned']);
        $this->assertEquals(420, $row['given']);          // 480 − 60
        $this->assertEquals(300, $row['used']);
        $this->assertEquals(20, $row['wastage']);
        $this->assertEquals(100, $row['balance']);        // 420 − 300 − 20
        $this->assertEquals(480, $row['in_store']);       // 900 − 480 + 60
        $this->assertEquals(305, $row['on_order']);
        $this->assertSame('Cable', $row['category']);

        $this->assertEquals(420, $ledger['cable']['given']);
        $this->assertEquals(300, $ledger['cable']['pulled']);
        $this->assertEquals(100, $ledger['cable']['balance']);
        $this->assertEquals(480, $ledger['cable']['in_store']);
        $this->assertFalse($ledger['cable']['all_cable']);

        // Cable is listed first, and the history holds every hand-over and use
        $this->assertSame($this->cable->id, $ledger['rows'][0]['product_id']);
        $this->assertSame(['return', 'use', 'issue', 'issue'], array_column(
            array_values(array_filter($ledger['history'], fn ($h) => $h['key'] === 'p' . $this->cable->id)), 'kind'
        ));
    }

    public function test_cable_planned_in_rolls_is_shown_but_not_mixed_with_metres(): void
    {
        ProjectMaterial::create([
            'project_id' => $this->project->id, 'product_id' => $this->cable->id, 'item_name' => 'CAT6 Cable Roll (305m)',
            'quantity' => 1, 'unit' => 'roll', 'source' => 'warehouse',
        ]);

        $row = collect(ProjectMaterialLedger::forProject($this->project->fresh())['rows'])->firstWhere('product_id', $this->cable->id);

        $this->assertEquals(600, $row['planned']);
        $this->assertSame(['1 roll'], $row['planned_other']);
    }

    public function test_cannot_return_more_than_is_on_site(): void
    {
        $this->give(['quantity' => 100]);
        $this->logUse($this->cable, 70);

        $this->give(['type' => 'return', 'quantity' => 31])->assertSessionHas('error');
        $this->assertSame(800, $this->cable->fresh()->stock_quantity);

        $this->give(['type' => 'return', 'quantity' => 30])->assertSessionHasNoErrors();
        $this->assertSame(830, $this->cable->fresh()->stock_quantity);
    }

    public function test_worker_portal_metres_count_against_the_project_cable(): void
    {
        $user = User::factory()->create(['name' => $this->worker->name, 'role' => 'worker']);
        $this->worker->update(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('worker.work.log'), [
            'project_id' => $this->project->id,
            'cabling_metres' => 60,
            'drum_or_batch_no' => 'D-0425',
        ])->assertRedirect();

        $use = TaskMaterialConsumption::sole();
        $this->assertSame($this->cable->id, $use->product_id);

        $row = collect(ProjectMaterialLedger::forProject($this->project->fresh())['rows'])->firstWhere('product_id', $this->cable->id);
        $this->assertEquals(60, $row['used']);
    }

    public function test_admin_can_undo_a_hand_over_and_stock_goes_back(): void
    {
        $this->give();
        $issue = ProjectMaterialIssue::sole();

        $this->actingAs($this->admin)
            ->delete(route('projects.material-issues.destroy', [$this->project, $issue]))
            ->assertRedirect();

        $this->assertSame(900, $this->cable->fresh()->stock_quantity);
        $this->assertSame(0, ProjectMaterialIssue::count());
        $this->assertNull(StockMovement::find($issue->stock_movement_id));
    }

    public function test_only_the_office_gives_material_and_only_admin_undoes(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);
        $this->actingAs($technician)->post(route('projects.material-issues.store', $this->project), [
            'type' => 'issue', 'product_id' => $this->cable->id, 'quantity' => 10, 'issued_on' => now()->toDateString(),
        ])->assertForbidden();

        $staff = User::factory()->create(['role' => 'staff']);
        $this->actingAs($staff)->post(route('projects.material-issues.store', $this->project), [
            'type' => 'issue', 'product_id' => $this->cable->id, 'quantity' => 10, 'issued_on' => now()->toDateString(),
        ])->assertRedirect();
        $this->assertSame(890, $this->cable->fresh()->stock_quantity);

        $this->actingAs($staff)
            ->delete(route('projects.material-issues.destroy', [$this->project, ProjectMaterialIssue::sole()]))
            ->assertForbidden();
    }

    public function test_materials_tab_shows_the_cable_tiles_and_stock_by_product(): void
    {
        $this->give();
        $this->logUse($this->cable, 50);

        $this->actingAs($this->admin)->get(route('projects.show', [$this->project, 'tab' => 'materials']))
            ->assertOk()
            ->assertSeeInOrder(['Cable given to site', '305 m', 'Cable pulled', '50 m', 'Cable balance on site', '255 m', 'Cable in storeroom', '595 m'])
            ->assertSee('Stock by product')
            ->assertSee('Filter by category', false)
            ->assertSee('Give to site')
            ->assertSee('Return to storeroom');

        $technician = User::factory()->create(['role' => 'technician']);
        $this->actingAs($technician)->get(route('projects.show', [$this->project, 'tab' => 'materials']))
            ->assertOk()
            ->assertSee('Stock by product')
            ->assertDontSee('Give material to the site');
    }
}

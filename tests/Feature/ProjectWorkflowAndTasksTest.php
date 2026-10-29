<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskChecklist;
use App\Models\User;
use App\Services\ProjectWorkflowTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectWorkflowAndTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_workflow_templates_for_cctv_project(): void
    {
        $templates = ProjectWorkflowTemplateService::getTemplatesForType('hardware_cctv');

        $this->assertNotEmpty($templates);
        $this->assertArrayHasKey('cctv_commercial', $templates);
        $this->assertCount(5, $templates['cctv_commercial']['phases']);
    }

    public function test_can_apply_cctv_workflow_template_to_project(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-2026-TEST',
            'title' => 'Alpha CCTV Surveillance Site',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('projects.tasks.apply-template', $project), [
            'template_key' => 'cctv_commercial',
            'assigned_to' => $admin->id,
        ]);

        $response->assertRedirect(route('projects.show', [$project, 'tab' => 'tasks']));
        $this->assertGreaterThan(0, $project->tasks()->count());

        /** @var ProjectTask $firstTask */
        $firstTask = $project->tasks()->first();
        $this->assertNotNull($firstTask);
        $this->assertNotEmpty($firstTask->phase_name);
        $this->assertGreaterThan(0, $firstTask->checklists()->count());
    }

    public function test_can_toggle_checklist_item_and_update_task_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-2026-TEST2',
            'title' => 'Beta Access Control Site',
            'project_type' => 'hardware_attendance',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'phase_name' => 'Phase 1: Cabling',
            'title' => 'Pull Cat6 cables',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $checklist = ProjectTaskChecklist::create([
            'project_task_id' => $task->id,
            'item_text' => 'Verify cable continuity with tester',
            'is_completed' => false,
        ]);

        // Toggle checklist
        $res = $this->actingAs($admin)->postJson(route('tasks.checklists.toggle', $checklist));
        $res->assertOk();
        $this->assertTrue($checklist->fresh()->is_completed);

        // Update task status
        $res2 = $this->actingAs($admin)->patchJson(route('projects.tasks.status', [$project, $task]), [
            'status' => 'completed',
        ]);
        $res2->assertOk();
        $this->assertEquals('completed', $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_can_create_custom_task_with_checklists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-2026-TEST3',
            'title' => 'Gamma Custom Project',
            'project_type' => 'networking',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('projects.tasks.store', $project), [
            'phase_name' => 'Phase 2: Rack Termination',
            'title' => 'Dress and terminate 24-Port Cat6 Patch Panel',
            'description' => 'Ensure T568B sequence and label all patch ports',
            'priority' => 'urgent',
            'status' => 'in_progress',
            'estimated_hours' => 3.5,
            'checklist_items' => [
                'Punch down 24 Keystone Ports',
                'Affix P-Touch cable tags',
                'Run Fluke continuity test',
            ],
        ]);

        $response->assertRedirect(route('projects.show', [$project, 'tab' => 'tasks']));
        $this->assertDatabaseHas('project_tasks', [
            'project_id' => $project->id,
            'title' => 'Dress and terminate 24-Port Cat6 Patch Panel',
            'priority' => 'urgent',
        ]);

        $task = ProjectTask::where('project_id', $project->id)->first();
        $this->assertCount(3, $task->checklists);
    }

    public function test_kanban_view_displays_5_columns_and_tasks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-2026-KANBAN',
            'title' => 'City Hospital CCTV Setup',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        // Create tasks across all 5 statuses
        foreach (['pending', 'in_progress', 'under_review', 'completed', 'blocked'] as $st) {
            ProjectTask::create([
                'project_id' => $project->id,
                'phase_name' => 'Phase 1: Installation',
                'title' => "Task in {$st}",
                'priority' => 'high',
                'status' => $st,
                'blocker_reason' => $st === 'blocked' ? 'Cannot start Camera Mounting until Client Finishes False Ceiling Paint' : null,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('projects.show', [$project, 'tab' => 'tasks', 'view' => 'kanban']));
        $response->assertOk();
        $response->assertSee('Backlog / Pending');
        $response->assertSee('Assigned / In Progress');
        $response->assertSee('Under Review / QA');
        $response->assertSee('Completed');
        $response->assertSee('Blocked');
        $response->assertSee('Cannot start Camera Mounting until Client Finishes False Ceiling Paint');
    }

    public function test_can_flag_task_as_blocked_with_reason_and_dispatch_whatsapp_and_dashboard_alerts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-2026-BLOCK',
            'title' => 'Metro Station Surveillance',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'lead_technician_id' => $admin->id,
            'created_by' => $admin->id,
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'phase_name' => 'Phase 3: Camera Mounting',
            'title' => 'Mount 8 Dome Cameras at Entrance',
            'priority' => 'urgent',
            'status' => 'in_progress',
        ]);

        $blockerReason = 'Cannot start Camera Mounting until Client Finishes False Ceiling Paint';

        $response = $this->actingAs($admin)->postJson(route('projects.tasks.flag-blocker', [$project, $task]), [
            'blocker_reason' => $blockerReason,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertNotEmpty($response->json('whatsapp_url'));
        $this->assertStringContainsString('wa.me', $response->json('whatsapp_url'));

        // Check task is now blocked
        $taskFresh = $task->fresh();
        $this->assertEquals('blocked', $taskFresh->status);
        $this->assertEquals($blockerReason, $taskFresh->blocker_reason);
        $this->assertNotNull($taskFresh->blocked_at);
        $this->assertEquals($admin->id, $taskFresh->blocked_by);

        // Check NotificationLog created for WhatsApp and topbar notification bell
        $this->assertDatabaseHas('notification_logs', [
            'channel' => 'whatsapp',
            'event_type' => 'task_blocked',
            'reference_type' => ProjectTask::class,
            'reference_id' => $task->id,
        ]);
    }

    public function test_can_unblock_task_and_resume_execution(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-2026-UNBLOCK',
            'title' => 'Warehouse Security Grid',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'phase_name' => 'Phase 2: Cabling',
            'title' => 'Conduit laying',
            'priority' => 'medium',
            'status' => 'blocked',
            'blocker_reason' => 'Waiting for scaffold permit',
            'blocked_at' => now(),
            'blocked_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->postJson(route('projects.tasks.unblock', [$project, $task]));
        $response->assertOk();

        $taskFresh = $task->fresh();
        $this->assertEquals('in_progress', $taskFresh->status);
        $this->assertNull($taskFresh->blocker_reason);
        $this->assertNull($taskFresh->blocked_at);
        $this->assertNull($taskFresh->blocked_by);
    }

    public function test_can_set_task_dependency_and_view_in_timeline(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-2026-DEP',
            'title' => 'Campus IP Surveillance',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $parentTask = ProjectTask::create([
            'project_id' => $project->id,
            'phase_name' => 'Phase 1: Civil Work',
            'title' => 'Lay Underground PVC Pipes',
            'priority' => 'high',
            'status' => 'completed',
        ]);

        $childTask = ProjectTask::create([
            'project_id' => $project->id,
            'phase_name' => 'Phase 2: Cabling',
            'title' => 'Pull Armored Fiber Cable',
            'priority' => 'urgent',
            'status' => 'pending',
            'depends_on_task_id' => $parentTask->id,
        ]);

        $this->assertEquals($parentTask->id, $childTask->dependsOnTask->id);
        $this->assertTrue($parentTask->dependentTasks->contains($childTask));

        $response = $this->actingAs($admin)->get(route('projects.show', [$project, 'tab' => 'tasks', 'view' => 'gantt']));
        $response->assertOk();
        $response->assertSee('Lay Underground PVC Pipes');
        $response->assertSee('Pull Armored Fiber Cable');
    }

    public function test_can_reorder_tasks_batch_via_drag_and_drop(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-2026-REORDER',
            'title' => 'Plaza IP Surveillance',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $task1 = ProjectTask::create([
            'project_id' => $project->id,
            'phase_name' => 'Phase 1',
            'title' => 'Task 1',
            'priority' => 'low',
            'status' => 'pending',
            'order_column' => 1,
        ]);

        $task2 = ProjectTask::create([
            'project_id' => $project->id,
            'phase_name' => 'Phase 1',
            'title' => 'Task 2',
            'priority' => 'medium',
            'status' => 'pending',
            'order_column' => 2,
        ]);

        $response = $this->actingAs($admin)->postJson(route('projects.tasks.reorder', $project), [
            'tasks' => [
                ['id' => $task1->id, 'order_column' => 2, 'status' => 'in_progress'],
                ['id' => $task2->id, 'order_column' => 1, 'status' => 'in_progress'],
            ],
        ]);

        $response->assertOk();
        $this->assertEquals(2, $task1->fresh()->order_column);
        $this->assertEquals('in_progress', $task1->fresh()->status);
        $this->assertEquals(1, $task2->fresh()->order_column);
        $this->assertEquals('in_progress', $task2->fresh()->status);
    }

    public function test_tasks_index_endpoint_redirects_to_kanban_view(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-2026-IDX',
            'title' => 'Index Redirect Project',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.tasks.index', $project));
        $response->assertRedirect(route('projects.show', [$project, 'tab' => 'tasks', 'view' => 'kanban']));
    }

    public function test_can_log_cable_and_material_consumption_against_task_with_bom_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-MAT-001',
            'title' => 'CCTV Cabling Deployment',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'phase_name' => 'Phase 2: Cabling',
            'title' => 'Pull Cat6 cables for Floor 1 cameras',
            'priority' => 'high',
            'status' => 'in_progress',
            'order_column' => 1,
        ]);

        $bomItem = \App\Models\ProjectMaterial::create([
            'project_id' => $project->id,
            'item_name' => 'D-Link Cat6 UTP Solid Cable',
            'quantity' => 300.00,
            'unit' => 'meters',
            'source' => 'warehouse',
        ]);

        $response = $this->actingAs($admin)->post(route('projects.material-consumptions.store', $project), [
            'project_task_id' => $task->id,
            'project_material_id' => $bomItem->id,
            'item_name' => 'D-Link Cat6 UTP Solid Cable',
            'quantity_used' => 125.50,
            'unit' => 'meters',
            'wastage_quantity' => 4.50,
            'drum_or_batch_no' => 'Drum #03 (100m-230m)',
            'notes' => 'Pulled along corridor tray to reception',
        ]);

        $response->assertRedirect(route('projects.show', ['project' => $project, 'tab' => 'materials']));

        $this->assertDatabaseHas('task_material_consumptions', [
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'project_material_id' => $bomItem->id,
            'quantity_used' => 125.50,
            'unit' => 'meters',
            'wastage_quantity' => 4.50,
            'drum_or_batch_no' => 'Drum #03 (100m-230m)',
        ]);

        // Verify BOM item calculation helpers
        $bomItem->refresh();
        $this->assertEquals(125.50, $bomItem->total_consumed);
        $this->assertEquals(4.50, $bomItem->total_wastage);
        $this->assertEquals(174.50, $bomItem->remaining_quantity);
        $this->assertEquals(41.8, $bomItem->consumption_percentage);
        $this->assertFalse($bomItem->is_over_consumed);
    }

    public function test_can_log_material_with_automatic_warehouse_stock_deduction(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-MAT-002',
            'title' => 'Warehouse Stock Test Site',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'phase_name' => 'Phase 3: Mounting',
            'title' => 'Mount 4MP Dome Cameras',
            'priority' => 'high',
            'status' => 'in_progress',
            'order_column' => 1,
        ]);

        $product = \App\Models\Product::create([
            'sku' => 'CAM-DOME-4MP',
            'name' => 'Hikvision 4MP Dome Camera',
            'category' => 'Cameras',
            'brand' => 'Hikvision',
            'unit' => 'nos',
            'cost_price' => 2800,
            'unit_price' => 3800,
            'stock_quantity' => 20,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->postJson(route('projects.tasks.materials.store', [$project, $task]), [
            'product_id' => $product->id,
            'item_name' => $product->name,
            'quantity_used' => 4,
            'unit' => 'nos',
            'deduct_from_stock' => 1,
            'notes' => 'Installed on North wall',
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('success'));

        // Stock quantity should now be 20 - 4 = 16
        $this->assertEquals(16, $product->fresh()->stock_quantity);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'job_installation',
            'quantity' => 4,
            'balance_after' => 16,
        ]);
    }

    public function test_can_revert_material_consumption_and_restore_warehouse_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-MAT-003',
            'title' => 'Stock Reversal Test Site',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'phase_name' => 'Phase 3: Mounting',
            'title' => 'Mount Cameras',
            'priority' => 'high',
            'status' => 'in_progress',
            'order_column' => 1,
        ]);

        $product = \App\Models\Product::create([
            'sku' => 'SW-POE-8P',
            'name' => '8-Port Gigabit PoE Switch',
            'category' => 'Networking',
            'unit' => 'nos',
            'cost_price' => 4500,
            'unit_price' => 6000,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        // Consume 2 switches
        $this->actingAs($admin)->post(route('projects.material-consumptions.store', $project), [
            'project_task_id' => $task->id,
            'product_id' => $product->id,
            'item_name' => $product->name,
            'quantity_used' => 2,
            'unit' => 'nos',
            'deduct_from_stock' => 1,
        ]);

        $this->assertEquals(8, $product->fresh()->stock_quantity);
        $consumption = \App\Models\TaskMaterialConsumption::where('project_id', $project->id)->first();
        $this->assertNotNull($consumption);

        // Delete / revert consumption
        $delResponse = $this->actingAs($admin)->delete(route('projects.material-consumptions.destroy', [$project, $consumption]));
        $delResponse->assertRedirect(route('projects.show', ['project' => $project, 'tab' => 'materials']));

        // Stock must be restored back to 10
        $this->assertEquals(10, $product->fresh()->stock_quantity);
        $this->assertDatabaseMissing('task_material_consumptions', ['id' => $consumption->id]);
    }

    public function test_can_view_materials_tab_with_bom_reconciliation_and_cable_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-MAT-VIEW',
            'title' => 'BOM Reconciliation View Test',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'phase_name' => 'Phase 2: Cabling',
            'title' => 'Pull Cat6 Cable',
            'priority' => 'medium',
            'status' => 'in_progress',
            'order_column' => 1,
        ]);

        $bom = \App\Models\ProjectMaterial::create([
            'project_id' => $project->id,
            'item_name' => 'Cat6 Cable Reel',
            'quantity' => 305.00,
            'unit' => 'meters',
            'source' => 'warehouse',
        ]);

        \App\Models\TaskMaterialConsumption::create([
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'project_material_id' => $bom->id,
            'item_name' => 'Cat6 Cable Reel',
            'quantity_used' => 200.00,
            'unit' => 'meters',
            'wastage_quantity' => 5.00,
            'drum_or_batch_no' => 'Reel-A01',
            'consumed_by_user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.show', ['project' => $project, 'tab' => 'materials']));

        $response->assertOk();
        $response->assertSee('What was planned for this site and how much has been used.');
        $response->assertSee('Cable pulled');
        $response->assertSee('200 m');
        $response->assertSee('Cat6 Cable Reel');
        $response->assertSee('Reel-A01');
    }
}

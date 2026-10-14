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
}

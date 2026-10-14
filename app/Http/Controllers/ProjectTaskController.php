<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectAuditLog;
use App\Models\ProjectTask;
use App\Models\ProjectTaskChecklist;
use App\Services\ProjectWorkflowTemplateService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectTaskController extends Controller
{
    /**
     * Apply a pre-built industry workflow template to the project.
     */
    public function applyTemplate(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'template_key' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        try {
            $count = ProjectWorkflowTemplateService::applyTemplateToProject(
                $project,
                $validated['template_key'],
                $validated['assigned_to'] ?? null
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully applied workflow template! {$count} tasks added.",
                    'tasks_count' => $count,
                ]);
            }

            return redirect()->route('projects.show', [$project, 'tab' => 'tasks'])
                ->with('status', "Successfully applied workflow template! {$count} tasks and checklists generated.");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('projects.show', [$project, 'tab' => 'tasks'])
                ->with('error', 'Error applying template: ' . $e->getMessage());
        }
    }

    /**
     * Store a new custom task on the project.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'phase_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,blocked',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0|max:999',
            'checklist_items' => 'nullable|array',
            'checklist_items.*' => 'nullable|string|max:255',
        ]);

        $maxOrder = (int) $project->tasks()->max('order_column') + 1;

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'phase_name' => $validated['phase_name'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'order_column' => $maxOrder,
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
            'completed_by' => $validated['status'] === 'completed' ? Auth::id() : null,
        ]);

        if (!empty($validated['checklist_items'])) {
            $order = 1;
            foreach ($validated['checklist_items'] as $item) {
                if (trim($item) !== '') {
                    ProjectTaskChecklist::create([
                        'project_task_id' => $task->id,
                        'item_text' => trim($item),
                        'is_completed' => false,
                        'order_column' => $order++,
                    ]);
                }
            }
        }

        ProjectAuditLog::create([
            'project_id' => $project->id,
            'auditable_type' => ProjectTask::class,
            'auditable_id' => $task->id,
            'user_id' => Auth::id(),
            'action' => 'created_task',
            'description' => "Created task '{$task->title}' in {$task->phase_name}.",
        ]);

        return redirect()->route('projects.show', [$project, 'tab' => 'tasks'])
            ->with('status', "Task '{$task->title}' created successfully.");
    }

    /**
     * Update an existing task.
     */
    public function update(Request $request, Project $project, ProjectTask $task): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'phase_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,blocked',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0|max:999',
            'actual_hours' => 'nullable|numeric|min:0|max:999',
        ]);

        $statusChanged = $task->status !== $validated['status'];
        if ($statusChanged && $validated['status'] === 'completed') {
            $validated['completed_at'] = now();
            $validated['completed_by'] = Auth::id();
        } elseif ($statusChanged && $task->status === 'completed') {
            $validated['completed_at'] = null;
            $validated['completed_by'] = null;
        }

        $task->update($validated);

        return redirect()->route('projects.show', [$project, 'tab' => 'tasks'])
            ->with('status', "Task '{$task->title}' updated successfully.");
    }

    /**
     * Quick toggle / update task status (supports AJAX for quick Kanban or dropdown).
     */
    public function updateStatus(Request $request, Project $project, ProjectTask $task): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,blocked',
        ]);

        $newStatus = $validated['status'];
        $task->status = $newStatus;

        if ($newStatus === 'completed') {
            $task->completed_at = now();
            $task->completed_by = Auth::id();
        } else {
            $task->completed_at = null;
            $task->completed_by = null;
        }

        $task->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $task->status,
                'status_badge' => $task->status_badge_classes,
                'completed_at' => $task->completed_at?->format('d M Y, h:i A'),
            ]);
        }

        return redirect()->route('projects.show', [$project, 'tab' => 'tasks'])
            ->with('status', "Task status updated to " . ucfirst(str_replace('_', ' ', $newStatus)));
    }

    /**
     * Delete a task.
     */
    public function destroy(Project $project, ProjectTask $task): RedirectResponse
    {
        $title = $task->title;
        $task->delete();

        return redirect()->route('projects.show', [$project, 'tab' => 'tasks'])
            ->with('status', "Task '{$title}' removed.");
    }

    /**
     * Toggle a checklist item's completion state (AJAX).
     */
    public function toggleChecklist(Request $request, ProjectTaskChecklist $checklist): JsonResponse
    {
        $checklist->is_completed = !$checklist->is_completed;
        $checklist->completed_at = $checklist->is_completed ? now() : null;
        $checklist->completed_by = $checklist->is_completed ? Auth::id() : null;
        $checklist->save();

        $task = $checklist->task;

        // Auto-check: If all checklist items are now completed, suggest or mark task completed
        $progress = $task->checklist_progress;

        return response()->json([
            'success' => true,
            'is_completed' => $checklist->is_completed,
            'progress' => $progress,
            'task_id' => $task->id,
        ]);
    }

    /**
     * Add a checklist item to an existing task.
     */
    public function addChecklistItem(Request $request, ProjectTask $task): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'item_text' => 'required|string|max:255',
        ]);

        $maxOrder = (int) $task->checklists()->max('order_column') + 1;

        $item = ProjectTaskChecklist::create([
            'project_task_id' => $task->id,
            'item_text' => $validated['item_text'],
            'is_completed' => false,
            'order_column' => $maxOrder,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => $item,
                'progress' => $task->fresh()->checklist_progress,
            ]);
        }

        return back()->with('status', 'Checklist item added.');
    }

    /**
     * Delete a checklist item.
     */
    public function deleteChecklistItem(ProjectTaskChecklist $checklist): JsonResponse|RedirectResponse
    {
        $task = $checklist->task;
        $checklist->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'progress' => $task->fresh()->checklist_progress,
            ]);
        }

        return back()->with('status', 'Checklist item deleted.');
    }
}

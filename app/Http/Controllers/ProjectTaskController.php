<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectAuditLog;
use App\Models\ProjectMaterial;
use App\Models\ProjectTask;
use App\Models\ProjectTaskChecklist;
use App\Models\StockMovement;
use App\Models\TaskMaterialConsumption;
use App\Models\User;
use App\Services\ProjectWorkflowTemplateService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectTaskController extends Controller
{
    /**
     * Display or redirect to the project's task board / Gantt view.
     */
    public function index(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $view = $request->query('view', 'kanban');

        if ($request->wantsJson()) {
            $project->load([
                'tasks.assignedUser',
                'tasks.dependsOnTask',
                'tasks.blockedByUser',
                'tasks.checklists',
            ]);

            return response()->json([
                'success' => true,
                'project_id' => $project->id,
                'project_title' => $project->title,
                'tasks' => $project->tasks,
                'kanban_columns' => ProjectTask::kanbanColumns(),
            ]);
        }

        return redirect()->route('projects.show', [
            'project' => $project,
            'tab' => 'tasks',
            'view' => $view,
        ]);
    }

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
    public function store(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'phase_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,under_review,completed,blocked',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'estimated_hours' => 'nullable|numeric|min:0|max:999',
            'depends_on_task_id' => 'nullable|exists:project_tasks,id',
            'blocker_reason' => 'nullable|string|max:1000',
            'checklist_items' => 'nullable|array',
            'checklist_items.*' => 'nullable|string|max:255',
        ]);

        $maxOrder = (int) $project->tasks()->max('order_column') + 1;
        $isBlocked = $validated['status'] === 'blocked';
        $isCompleted = $validated['status'] === 'completed';

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'phase_name' => $validated['phase_name'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'start_date' => $validated['start_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'depends_on_task_id' => $validated['depends_on_task_id'] ?? null,
            'blocker_reason' => $isBlocked ? ($validated['blocker_reason'] ?? 'Blocked upon creation') : null,
            'blocked_at' => $isBlocked ? now() : null,
            'blocked_by' => $isBlocked ? Auth::id() : null,
            'order_column' => $maxOrder,
            'completed_at' => $isCompleted ? now() : null,
            'completed_by' => $isCompleted ? Auth::id() : null,
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

        $whatsappUrl = null;
        if ($isBlocked) {
            $whatsappUrl = $this->notifyManagersOfBlocker($project, $task, $task->blocker_reason ?? 'Task created as Blocked');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task->load('assignedUser', 'dependsOnTask'),
                'whatsapp_url' => $whatsappUrl,
                'message' => "Task '{$task->title}' created successfully.",
            ]);
        }

        return redirect()->route('projects.show', [$project, 'tab' => 'tasks'])
            ->with('status', "Task '{$task->title}' created successfully.");
    }

    /**
     * Update an existing task.
     */
    public function update(Request $request, Project $project, ProjectTask $task): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'phase_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,under_review,completed,blocked',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'estimated_hours' => 'nullable|numeric|min:0|max:999',
            'actual_hours' => 'nullable|numeric|min:0|max:999',
            'depends_on_task_id' => 'nullable|exists:project_tasks,id',
            'blocker_reason' => 'nullable|string|max:1000',
        ]);

        // Prevent self-dependency
        if (!empty($validated['depends_on_task_id']) && (int) $validated['depends_on_task_id'] === $task->id) {
            $validated['depends_on_task_id'] = null;
        }

        $oldStatus = $task->status;
        $newStatus = $validated['status'];
        $statusChanged = $oldStatus !== $newStatus;

        if ($statusChanged && $newStatus === 'completed') {
            $validated['completed_at'] = now();
            $validated['completed_by'] = Auth::id();
        } elseif ($statusChanged && $oldStatus === 'completed') {
            $validated['completed_at'] = null;
            $validated['completed_by'] = null;
        }

        $whatsappUrl = null;
        if ($statusChanged && $newStatus === 'blocked') {
            $validated['blocked_at'] = now();
            $validated['blocked_by'] = Auth::id();
            $validated['blocker_reason'] = $validated['blocker_reason'] ?? 'Flagged as blocked';
            $whatsappUrl = $this->notifyManagersOfBlocker($project, $task, $validated['blocker_reason']);
        } elseif ($statusChanged && $oldStatus === 'blocked') {
            $validated['blocked_at'] = null;
            $validated['blocked_by'] = null;
            $validated['blocker_reason'] = null;
        }

        $task->update($validated);

        ProjectAuditLog::create([
            'project_id' => $project->id,
            'auditable_type' => ProjectTask::class,
            'auditable_id' => $task->id,
            'user_id' => Auth::id(),
            'action' => 'updated_task',
            'description' => "Updated task '{$task->title}' (Status: {$task->status}).",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task->fresh(['assignedUser', 'dependsOnTask', 'blockedByUser']),
                'whatsapp_url' => $whatsappUrl,
                'message' => "Task '{$task->title}' updated successfully.",
            ]);
        }

        return redirect()->route('projects.show', [$project, 'tab' => 'tasks'])
            ->with('status', "Task '{$task->title}' updated successfully.");
    }

    /**
     * Quick toggle / update task status (supports Alpine / Sortable.js drag-and-drop).
     */
    public function updateStatus(Request $request, Project $project, ProjectTask $task): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,under_review,completed,blocked',
            'blocker_reason' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $task->status;
        $newStatus = $validated['status'];
        $task->status = $newStatus;

        $whatsappUrl = null;

        if ($newStatus === 'completed') {
            $task->completed_at = now();
            $task->completed_by = Auth::id();
        } else {
            $task->completed_at = null;
            $task->completed_by = null;
        }

        if ($newStatus === 'blocked') {
            $reason = !empty($validated['blocker_reason']) ? $validated['blocker_reason'] : ($task->blocker_reason ?: 'Task marked as blocked');
            $task->blocker_reason = $reason;
            $task->blocked_at = now();
            $task->blocked_by = Auth::id();
            $whatsappUrl = $this->notifyManagersOfBlocker($project, $task, $reason);
        } else {
            $task->blocker_reason = null;
            $task->blocked_at = null;
            $task->blocked_by = null;
        }

        $task->save();

        ProjectAuditLog::create([
            'project_id' => $project->id,
            'auditable_type' => ProjectTask::class,
            'auditable_id' => $task->id,
            'user_id' => Auth::id(),
            'action' => 'status_changed',
            'description' => "Changed status of '{$task->title}' from '{$oldStatus}' to '{$newStatus}'.",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task_id' => $task->id,
                'status' => $task->status,
                'status_badge' => $task->status_badge_classes,
                'completed_at' => $task->completed_at?->format('d M Y, h:i A'),
                'is_blocked' => $task->isBlocked(),
                'blocker_reason' => $task->blocker_reason,
                'whatsapp_url' => $whatsappUrl,
                'message' => "Task status changed to " . ucfirst(str_replace('_', ' ', $newStatus)),
            ]);
        }

        return redirect()->route('projects.show', [$project, 'tab' => 'tasks'])
            ->with('status', "Task status updated to " . ucfirst(str_replace('_', ' ', $newStatus)));
    }

    /**
     * Flag a task as blocked with an explicit reason, dispatching alerts.
     */
    public function flagBlocker(Request $request, Project $project, ProjectTask $task): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'blocker_reason' => 'required|string|max:1000',
        ]);

        $reason = $validated['blocker_reason'];
        $task->status = 'blocked';
        $task->blocker_reason = $reason;
        $task->blocked_at = now();
        $task->blocked_by = Auth::id();
        $task->save();

        ProjectAuditLog::create([
            'project_id' => $project->id,
            'auditable_type' => ProjectTask::class,
            'auditable_id' => $task->id,
            'user_id' => Auth::id(),
            'action' => 'flagged_blocker',
            'description' => "Flagged blocker on '{$task->title}': {$reason}",
        ]);

        $whatsappUrl = $this->notifyManagersOfBlocker($project, $task, $reason);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task->fresh(['assignedUser', 'blockedByUser', 'dependsOnTask']),
                'whatsapp_url' => $whatsappUrl,
                'message' => "Task flagged as blocked. Project manager and team notified.",
            ]);
        }

        return redirect()->route('projects.show', [$project, 'tab' => 'tasks'])
            ->with('status', "Task flagged as blocked. Project manager and team notified.")
            ->with('whatsapp_blocker_url', $whatsappUrl);
    }

    /**
     * Unblock a task and resume it.
     */
    public function unblock(Request $request, Project $project, ProjectTask $task): RedirectResponse|JsonResponse
    {
        $task->status = 'in_progress';
        $task->blocker_reason = null;
        $task->blocked_at = null;
        $task->blocked_by = null;
        $task->save();

        ProjectAuditLog::create([
            'project_id' => $project->id,
            'auditable_type' => ProjectTask::class,
            'auditable_id' => $task->id,
            'user_id' => Auth::id(),
            'action' => 'unblocked_task',
            'description' => "Unblocked task '{$task->title}' and resumed to In Progress.",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task->fresh(['assignedUser', 'dependsOnTask']),
                'message' => "Task '{$task->title}' unblocked and resumed.",
            ]);
        }

        return redirect()->route('projects.show', [$project, 'tab' => 'tasks'])
            ->with('status', "Task '{$task->title}' unblocked and resumed.");
    }

    /**
     * Batch reorder tasks (drag & drop persistence across columns or within a column).
     */
    public function reorder(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:project_tasks,id',
            'tasks.*.order_column' => 'required|integer',
            'tasks.*.status' => 'nullable|in:pending,in_progress,under_review,completed,blocked',
            'tasks.*.blocker_reason' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $alertUrls = [];
            foreach ($validated['tasks'] as $item) {
                $task = ProjectTask::where('project_id', $project->id)->find($item['id']);
                if (!$task) {
                    continue;
                }

                $task->order_column = (int) $item['order_column'];

                if (!empty($item['status']) && $task->status !== $item['status']) {
                    $newStatus = $item['status'];
                    $task->status = $newStatus;

                    if ($newStatus === 'completed') {
                        $task->completed_at = now();
                        $task->completed_by = Auth::id();
                    } elseif ($newStatus === 'blocked') {
                        $reason = $item['blocker_reason'] ?? $task->blocker_reason ?? 'Blocked via drag-and-drop';
                        $task->blocker_reason = $reason;
                        $task->blocked_at = now();
                        $task->blocked_by = Auth::id();
                        $waUrl = $this->notifyManagersOfBlocker($project, $task, $reason);
                        $alertUrls[] = $waUrl;
                    } else {
                        $task->blocker_reason = null;
                        $task->blocked_at = null;
                        $task->blocked_by = null;
                        if ($task->status !== 'completed') {
                            $task->completed_at = null;
                            $task->completed_by = null;
                        }
                    }
                }

                $task->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task positions and statuses synchronized successfully.',
                'whatsapp_urls' => array_filter($alertUrls),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Task reorder error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error reordering tasks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a task.
     */
    public function destroy(Project $project, ProjectTask $task): RedirectResponse
    {
        $title = $task->title;
        $task->delete();

        ProjectAuditLog::create([
            'project_id' => $project->id,
            'auditable_type' => ProjectTask::class,
            'auditable_id' => $task->id,
            'user_id' => Auth::id(),
            'action' => 'deleted_task',
            'description' => "Deleted task '{$title}'.",
        ]);

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

    /**
     * Send auto-alerts to Project Managers & Admins via NotificationLog (Dashboard Bell + WhatsApp link).
     */
    protected function notifyManagersOfBlocker(Project $project, ProjectTask $task, string $reason): ?string
    {
        try {
            // Find manager or admin to notify
            $recipientUser = $project->leadTechnician
                ?? $project->assignedUser
                ?? $project->creator
                ?? User::where('role', 'admin')->first();

            $recipientPhone = $recipientUser?->phone ?? config('services.whatsapp.default_phone', '919677257774');
            $recipientName = $recipientUser?->name ?? 'Project Manager';
            $flaggedBy = Auth::user()?->name ?? 'Technician';

            $projectCode = $project->project_code ?? ('PRJ-' . $project->id);
            $subject = "Task Blocked: {$task->title} [{$projectCode}]";
            $projectUrl = route('projects.show', [$project, 'tab' => 'tasks', 'view' => 'kanban']);

            $whatsappBody = "🚨 *TASK BLOCKED ALERT*\n\n"
                . "*Project:* {$project->title} ({$projectCode})\n"
                . "*Phase:* {$task->phase_name}\n"
                . "*Task:* {$task->title}\n"
                . "*Blocker Reason:* \"{$reason}\"\n"
                . "*Flagged By:* {$flaggedBy}\n"
                . "*Assigned To:* " . ($task->assignedUser?->name ?? 'Unassigned') . "\n\n"
                . "👉 *Review Board:* {$projectUrl}";

            // Create WhatsApp & Bell Notification Log (shows in topbar notification bell & WhatsApp archives)
            NotificationLog::create([
                'channel' => 'whatsapp',
                'event_type' => 'task_blocked',
                'recipient_type' => 'User',
                'recipient_name' => $recipientName,
                'recipient_phone' => $recipientPhone,
                'recipient_email' => $recipientUser?->email,
                'subject' => $subject,
                'message_body' => $whatsappBody,
                'action_url' => $projectUrl,
                'status' => 'sent',
                'reference_type' => ProjectTask::class,
                'reference_id' => $task->id,
                'sent_at' => now(),
                'created_by' => Auth::id(),
            ]);

            // Clean recipient phone for wa.me URL
            $phoneClean = preg_replace('/[^0-9]/', '', (string) $recipientPhone);
            if (!str_starts_with($phoneClean, '91') && strlen($phoneClean) === 10) {
                $phoneClean = '91' . $phoneClean;
            }

            return "https://wa.me/{$phoneClean}?text=" . urlencode($whatsappBody);
        } catch (\Exception $e) {
            Log::warning('Error creating task blocked alert notifications: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log material or cable consumption against a specific project task.
     */
    public function logMaterialConsumption(Request $request, Project $project, ProjectTask $task): RedirectResponse|JsonResponse
    {
        $request->merge(['project_task_id' => $task->id]);
        return $this->storeMaterialConsumption($request, $project);
    }

    /**
     * Store material / cable consumption record (BOM integration & stock deduction).
     */
    public function storeMaterialConsumption(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'project_task_id' => 'required|exists:project_tasks,id',
            'project_material_id' => 'nullable|exists:project_materials,id',
            'product_id' => 'nullable|exists:products,id',
            'item_name' => 'required|string|max:255',
            'quantity_used' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'wastage_quantity' => 'nullable|numeric|min:0',
            'drum_or_batch_no' => 'nullable|string|max:100',
            'consumed_at' => 'nullable|date',
            'deduct_from_stock' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $task = ProjectTask::where('project_id', $project->id)->findOrFail($validated['project_task_id']);

        // If project_material_id is provided, verify it belongs to this project
        $bomItem = null;
        if (!empty($validated['project_material_id'])) {
            $bomItem = ProjectMaterial::where('project_id', $project->id)->find($validated['project_material_id']);
            if ($bomItem && empty($validated['product_id']) && $bomItem->product_id) {
                $validated['product_id'] = $bomItem->product_id;
            }
        }

        DB::beginTransaction();
        try {
            $stockMovementId = null;
            $deductFromStock = !empty($validated['deduct_from_stock']) && !empty($validated['product_id']);

            if ($deductFromStock) {
                $product = Product::find($validated['product_id']);
                if ($product) {
                    $qtyToDeduct = (int) ceil((float)$validated['quantity_used'] + (float)($validated['wastage_quantity'] ?? 0));
                    $newStock = max(0, $product->stock_quantity - $qtyToDeduct);
                    $product->update(['stock_quantity' => $newStock]);

                    $movement = StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'job_installation',
                        'quantity' => $qtyToDeduct,
                        'balance_after' => $newStock,
                        'reference_type' => ProjectTask::class,
                        'reference_id' => $task->id,
                        'notes' => "Task consumption: {$task->title} (Project: {$project->project_code})",
                        'user_id' => Auth::id(),
                    ]);
                    $stockMovementId = $movement->id;
                }
            }

            $consumption = TaskMaterialConsumption::create([
                'project_id' => $project->id,
                'project_task_id' => $task->id,
                'project_material_id' => $validated['project_material_id'] ?? null,
                'product_id' => $validated['product_id'] ?? null,
                'item_name' => $validated['item_name'],
                'quantity_used' => $validated['quantity_used'],
                'unit' => strtolower($validated['unit']),
                'wastage_quantity' => $validated['wastage_quantity'] ?? 0.00,
                'drum_or_batch_no' => $validated['drum_or_batch_no'] ?? null,
                'consumed_by_user_id' => Auth::id(),
                'consumed_at' => $validated['consumed_at'] ?? now(),
                'deduct_from_stock' => $deductFromStock,
                'stock_movement_id' => $stockMovementId,
                'notes' => $validated['notes'] ?? null,
            ]);

            $wastageText = ($consumption->wastage_quantity > 0) ? " (Scrap/Wastage: {$consumption->wastage_quantity} {$consumption->unit})" : '';
            $stockText = $deductFromStock ? ' [Deducted from warehouse stock]' : '';

            ProjectAuditLog::logChange(
                $project,
                'material_consumed',
                null,
                null,
                "Logged {$consumption->quantity_used} {$consumption->unit} of '{$consumption->item_name}' consumed on task '{$task->title}'{$wastageText}{$stockText}"
            );

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Material consumption recorded successfully.',
                    'consumption' => $consumption->load(['task', 'projectMaterial', 'product', 'consumedBy']),
                ]);
            }

            return redirect()->route('projects.show', ['project' => $project, 'tab' => $request->input('redirect_tab', 'materials')])
                ->with('status', "Recorded {$consumption->quantity_used} {$consumption->unit} of '{$consumption->item_name}' consumed on task '{$task->title}'.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error recording material consumption: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to record consumption: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Failed to record consumption: ' . $e->getMessage());
        }
    }

    /**
     * Delete a material consumption entry and restore warehouse inventory if previously deducted.
     */
    public function destroyMaterialConsumption(Request $request, Project $project, TaskMaterialConsumption $consumption): RedirectResponse|JsonResponse
    {
        if ($consumption->project_id !== $project->id) {
            abort(404);
        }

        DB::beginTransaction();
        try {
            // Restore inventory if stock was deducted
            if ($consumption->deduct_from_stock && $consumption->stock_movement_id && $consumption->product_id) {
                $product = Product::find($consumption->product_id);
                $movement = StockMovement::find($consumption->stock_movement_id);
                if ($product && $movement) {
                    $product->increment('stock_quantity', $movement->quantity);
                    $movement->delete();
                }
            }

            $itemName = $consumption->item_name;
            $qty = $consumption->quantity_used;
            $unit = $consumption->unit;
            $taskTitle = $consumption->task?->title ?? 'Task';

            $consumption->delete();

            ProjectAuditLog::logChange(
                $project,
                'material_consumption_reverted',
                null,
                null,
                "Reverted consumption of {$qty} {$unit} of '{$itemName}' from task '{$taskTitle}'"
            );

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Material consumption entry deleted and stock restored.',
                ]);
            }

            return redirect()->route('projects.show', ['project' => $project, 'tab' => 'materials'])
                ->with('status', "Reverted consumption of {$qty} {$unit} of '{$itemName}'.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error deleting material consumption: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to revert consumption: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Failed to revert consumption: ' . $e->getMessage());
        }
    }
}

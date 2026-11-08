<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectAuditLog;
use App\Models\ProjectMaterialIssue;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Worker;
use App\Services\ProjectMaterialLedger;
use App\Services\StockAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Material handed from the storeroom to a project site, and returns from the site.
 * Each one moves storeroom stock, so only the office records them.
 */
class ProjectMaterialIssueController extends Controller
{
    public function store(Request $request, Project $project, StockAlertService $stockAlertService): RedirectResponse
    {
        abort_unless($request->user()->isOffice(), 403, 'Only the office can give material to a site or take it back.');

        // Normalize handled_by if sent as a pure numeric ID
        $rawHandled = $request->input('handled_by');
        if ($rawHandled && !str_contains($rawHandled, ':') && is_numeric($rawHandled)) {
            $request->merge(['handled_by' => 'worker:' . $rawHandled]);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:issue,return'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'handled_by' => ['nullable', 'string', 'regex:/^(worker|user):\d+$/'],
            'issued_on' => ['required', 'date', 'before_or_equal:today'],
            'drum_or_batch_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $isReturn = $validated['type'] === 'return';
            $qty = (int) ceil((float) $validated['quantity']);
            $product = Product::findOrFail($validated['product_id']);

            [$workerId, $userId] = [null, null];
            if (!empty($validated['handled_by'])) {
                [$kind, $id] = explode(':', $validated['handled_by']);
                if ($kind === 'worker') {
                    $workerId = Worker::whereKey($id)->value('id');
                } else {
                    $userId = User::whereKey($id)->value('id');
                }
                if (!$workerId && !$userId) {
                    return $this->backWithError($project, 'That person was not found.');
                }
            }

            if ($isReturn) {
                $onSite = ProjectMaterialLedger::siteBalance($project, $product->id);
                if ($qty > $onSite) {
                    return $this->backWithError($project, 'Only ' . $this->fmt(max(0, $onSite)) . " {$product->unit} of {$product->name} is on site to return.");
                }
            } elseif ($qty > (int)($product->stock_quantity ?? 0)) {
                return $this->backWithError($project, "Only " . ((int)($product->stock_quantity ?? 0)) . " {$product->unit} of {$product->name} is in the storeroom.");
            }

            $issue = DB::transaction(function () use ($project, $product, $validated, $isReturn, $qty, $workerId, $userId) {
                $product = Product::lockForUpdate()->findOrFail($product->id);
                $newStock = $isReturn ? (int)$product->stock_quantity + $qty : max(0, (int)$product->stock_quantity - $qty);
                $product->update(['stock_quantity' => $newStock]);

                $issue = ProjectMaterialIssue::create([
                    'project_id' => $project->id,
                    'product_id' => $product->id,
                    'type' => $isReturn ? 'return' : 'issue',
                    'quantity' => $qty,
                    'unit' => $product->unit ?? 'nos',
                    'worker_id' => $workerId,
                    'user_id' => $userId,
                    'issued_on' => $validated['issued_on'],
                    'drum_or_batch_no' => $validated['drum_or_batch_no'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                $who = $issue->handled_by_name;
                $movement = StockMovement::create([
                    'product_id' => $product->id,
                    'type' => $isReturn ? 'return' : 'out',
                    'quantity' => $qty,
                    'balance_after' => $newStock,
                    'reference_type' => Project::class,
                    'reference_id' => $project->id,
                    'notes' => ($isReturn ? 'Returned from site: ' : 'Given to site: ') . ($project->title ?? $project->project_code) . ($who ? " ({$who})" : ''),
                    'user_id' => Auth::id(),
                ]);
                $issue->update(['stock_movement_id' => $movement->id]);

                ProjectAuditLog::logChange(
                    $project,
                    $isReturn ? 'material_returned' : 'material_issued',
                    null,
                    null,
                    ($isReturn ? 'Returned ' : 'Gave ') . "{$qty} " . ($product->unit ?? 'units') . " of '{$product->name}'"
                        . ($isReturn ? ' to the storeroom' : ' to the site') . ($who ? ($isReturn ? " from {$who}" : " to {$who}") : '')
                );

                return $issue;
            });

            if (!$isReturn) {
                try {
                    $stockAlertService->checkAndTriggerAlert($product->fresh());
                } catch (\Throwable $e) {
                    Log::warning('Stock alert check failed: ' . $e->getMessage());
                }
            }

            $product->refresh();
            $handledName = $issue->handled_by_name;
            $message = $isReturn
                ? "{$qty} {$product->unit} of {$product->name} returned to the storeroom."
                : "{$qty} {$product->unit} of {$product->name} given to the site" . ($handledName ? " ({$handledName})" : '') . '.';

            return redirect()->route('projects.show', ['project' => $project, 'tab' => 'materials'])
                ->with('status', "{$message} Storeroom now has " . number_format((int)$product->stock_quantity) . " {$product->unit}.");
        } catch (\Illuminate\Validation\ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {
            Log::error('Material issue/return failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->backWithError($project, 'Could not complete material operation: ' . $e->getMessage());
        }
    }

    /**
     * Undo a hand-over entered by mistake, putting the storeroom stock back as it was.
     */
    public function destroy(Request $request, Project $project, ProjectMaterialIssue $issue): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403, 'Only an administrator can undo a material hand-over.');
        abort_unless($issue->project_id === $project->id, 404);

        try {
            DB::transaction(function () use ($project, $issue) {
                $product = Product::lockForUpdate()->find($issue->product_id);
                if ($product) {
                    $restored = $issue->isReturn()
                        ? max(0, (int)$product->stock_quantity - (int)$issue->quantity)
                        : (int)$product->stock_quantity + (int)$issue->quantity;
                    $product->update(['stock_quantity' => $restored]);
                }
                StockMovement::whereKey($issue->stock_movement_id)->delete();

                ProjectAuditLog::logChange(
                    $project,
                    'material_issue_undone',
                    null,
                    null,
                    'Undid ' . ($issue->isReturn() ? 'the return of ' : 'giving ') . "{$issue->quantity} {$issue->unit} of '" . ($product?->name ?? 'a product') . "'"
                );

                $issue->delete();
            });

            return redirect()->route('projects.show', ['project' => $project, 'tab' => 'materials'])
                ->with('status', 'Hand-over undone and storeroom stock put back.');
        } catch (\Throwable $e) {
            Log::error('Undo material issue failed: ' . $e->getMessage());
            return $this->backWithError($project, 'Could not undo hand-over: ' . $e->getMessage());
        }
    }

    private function backWithError(Project $project, string $message): RedirectResponse
    {
        return redirect()->route('projects.show', ['project' => $project, 'tab' => 'materials'])
            ->with('error', $message)
            ->withInput();
    }

    private function fmt(float $n): string
    {
        return rtrim(rtrim(number_format($n, 2), '0'), '.');
    }
}

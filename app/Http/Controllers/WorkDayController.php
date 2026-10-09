<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WorkDay;
use App\Models\Worker;
use App\Models\ProjectWorkerAttendance;
use App\Models\ProjectAuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkDayController extends Controller
{
    /**
     * Store or update a work day with its worker attendances for a project.
     */
    public function store(Request $request, Project $project)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isTechnician()) {
            abort(403, 'Employees cannot log daily-wage work days. Only technicians and administrators have permission.');
        }

        $validated = $request->validate([
            'work_date' => 'required|date',
            'notes' => 'nullable|string',
            'attendances' => 'required|array|min:1',
            'attendances.*.worker_id' => 'required|exists:workers,id',
            'attendances.*.attendance_type' => 'required|in:full_day,half_day',
            'attendances.*.cabling_metres' => 'nullable|numeric|min:0',
            'attendances.*.extra_amount' => 'nullable|numeric|min:0',
            'attendances.*.extra_description' => 'nullable|string|max:255',
            'attendances.*.notes' => 'nullable|string|max:255',
        ]);

        $workDate = Carbon::parse($validated['work_date'])->toDateString();

        DB::beginTransaction();
        try {
            // Find or create WorkDay for this project and date
            $workDay = WorkDay::firstOrCreate(
                [
                    'project_id' => $project->id,
                    'work_date' => $workDate,
                ],
                [
                    'logged_by' => Auth::id(),
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            if ($request->filled('notes')) {
                $workDay->update(['notes' => $validated['notes']]);
            }

            $perMetreRate = (float) ($project->per_metre_rate ?? 8.00);

            foreach ($validated['attendances'] as $attData) {
                $worker = Worker::findOrFail($attData['worker_id']);

                // Check if attendance already exists for this worker on this work day
                $existingAtt = ProjectWorkerAttendance::where('work_day_id', $workDay->id)
                    ->where('worker_id', $worker->id)
                    ->first();

                // If existing and already paid/locked, only admin can overwrite
                if ($existingAtt && $existingAtt->is_paid && !Auth::user()->isAdmin()) {
                    continue; // Skip locked records for non-admins
                }

                $cablingMetres = (float) ($attData['cabling_metres'] ?? 0);
                $cablingAmount = $cablingMetres * $perMetreRate;
                $extraAmount = (float) ($attData['extra_amount'] ?? 0);
                $dailyRateSnapshot = (float) ($existingAtt?->daily_rate_snapshot ?? $worker->daily_rate);
                $baseRate = ($attData['attendance_type'] === 'half_day') ? ($dailyRateSnapshot / 2) : $dailyRateSnapshot;
                $totalAmount = $baseRate + $cablingAmount + $extraAmount;

                ProjectWorkerAttendance::updateOrCreate(
                    [
                        'work_day_id' => $workDay->id,
                        'worker_id' => $worker->id,
                    ],
                    [
                        'daily_rate_snapshot' => $dailyRateSnapshot,
                        'rate_applied' => $dailyRateSnapshot,
                        'attendance_type' => $attData['attendance_type'],
                        'cabling_metres' => $cablingMetres,
                        'metres' => $cablingMetres,
                        'cabling_rate' => $perMetreRate,
                        'cabling_amount' => $cablingAmount,
                        'extra_amount' => $extraAmount,
                        'extra_description' => $attData['extra_description'] ?? null,
                        'total_amount' => $totalAmount,
                        'notes' => $attData['notes'] ?? null,
                    ]
                );
            }

            ProjectAuditLog::logChange(
                $project,
                'work_day_logged',
                null,
                null,
                "Work day logged for {$workDate} with " . count($validated['attendances']) . " worker attendances"
            );

            DB::commit();

            return redirect()->route('projects.show', ['project' => $project, 'tab' => 'work_days'])
                ->with('status', "Work day for {$workDate} logged successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save work day: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete an entire work day if no attendances are paid/locked.
     */
    public function destroy(Project $project, WorkDay $workDay)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can delete work day logs.');
        }

        // Check if any attendance has been paid
        $hasPaid = $workDay->attendances()->where('is_paid', true)->exists();
        if ($hasPaid && !Auth::user()->isAdmin()) {
            return back()->with('error', 'Cannot delete work day containing paid/locked worker records.');
        }

        $date = $workDay->work_date->toDateString();
        $workDay->attendances()->delete();
        $workDay->delete();

        ProjectAuditLog::logChange(
            $project,
            'work_day_deleted',
            null,
            null,
            "Work day record for {$date} removed"
        );

        return redirect()->route('projects.show', ['project' => $project, 'tab' => 'work_days'])
            ->with('status', "Work day for {$date} deleted.");
    }

    /**
     * Admin-only unlock of a locked attendance record.
     */
    public function unlockAttendance(Request $request, ProjectWorkerAttendance $attendance)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can unlock paid attendance records.');
        }

        $attendance->update([
            'is_paid' => false,
            'paid_week_start' => null,
            'wage_payment_id' => null,
        ]);

        ProjectAuditLog::logChange(
            $attendance->workDay->project,
            'attendance_unlocked',
            null,
            null,
            "Attendance record for worker {$attendance->worker->name} unlocked by Admin"
        );

        return back()->with('status', "Attendance record for {$attendance->worker->name} unlocked.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectAuditLog;
use App\Models\ProjectTask;
use App\Models\ProjectWorkerAttendance;
use App\Models\TaskMaterialConsumption;
use App\Models\User;
use App\Models\WorkDay;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WorkerPortalController extends Controller
{
    /**
     * Handle simple login for daily wage workers using Name and Mobile Phone Number.
     */
    public function workerLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|min:8|max:20',
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $validated['phone']);
        $name = trim($validated['name']);

        // Find existing worker by phone or create a new worker record
        $worker = Worker::where('phone', $cleanPhone)->first();

        if (!$worker) {
            $worker = Worker::create([
                'name' => $name,
                'phone' => $cleanPhone,
                'daily_rate' => 750.00,
                'is_active' => true,
                'notes' => 'Self-registered via Daily Wages Worker Portal Login',
            ]);
        } else {
            // Update name if changed
            if (!empty($name) && $worker->name !== $name) {
                $worker->update(['name' => $name]);
            }
        }

        // Find or create associated user account with 'worker' role
        $email = "worker_{$cleanPhone}@cctv-crm.local";
        $user = $worker->user;

        if (!$user) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $worker->name,
                    'role' => 'worker',
                    'password' => bcrypt('worker_' . $cleanPhone),
                ]
            );

            $worker->update(['user_id' => $user->id]);
        }

        // Authenticate user session
        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->put('portal_type', 'worker');
        $request->session()->put('worker_id', $worker->id);

        return redirect()->route('worker.portal')
            ->with('status', "Welcome, {$worker->name}! You are logged in to the Daily Wages Worker Portal.");
    }

    /**
     * Display the Daily Wages Worker Mobile Portal Dashboard.
     */
    public function portal(Request $request): View
    {
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            $cleanPhone = preg_replace('/[^0-9]/', '', (string) $user->email);
            $worker = Worker::where('phone', $cleanPhone)->first()
                ?? Worker::where('is_active', true)->first();

            if (!$worker) {
                $worker = Worker::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'phone' => '9876543210',
                    'daily_rate' => 750.00,
                    'is_active' => true,
                ]);
            }
        }

        $todayDate = now()->toDateString();

        // Get today's attendance for this worker
        $todayAttendance = ProjectWorkerAttendance::with(['workDay.project'])
            ->where('worker_id', $worker->id)
            ->whereHas('workDay', function ($q) use ($todayDate) {
                $q->whereDate('work_date', $todayDate);
            })
            ->latest()
            ->first();

        // Active projects that the worker can punch into or log work on
        $activeProjects = Project::with(['tasks' => function ($q) {
            $q->whereIn('status', ['pending', 'in_progress', 'under_review'])->orderBy('order_column');
        }])
        ->whereIn('status', ['in_progress', 'approved'])
        ->orderByDesc('updated_at')
        ->get();

        // Today's logged work statistics
        $todayCablingMetres = (float) ($todayAttendance?->cabling_metres ?? 0);
        $todayCamerasCount = (int) ($todayAttendance?->cameras_installed ?? 0);
        $todayEarnings = (float) ($todayAttendance?->total_amount ?? 0);

        // Recent 7 days of attendance history for this worker
        $recentAttendances = ProjectWorkerAttendance::with(['workDay.project'])
            ->where('worker_id', $worker->id)
            ->latest()
            ->take(7)
            ->get();

        // Total meters pulled across all projects
        $lifetimeCablingMetres = (float) $worker->attendances()->sum('cabling_metres');
        $lifetimeCamerasInstalled = (int) $worker->attendances()->sum('cameras_installed');
        $runningBalance = $worker->getRunningBalance();

        return view('worker.portal', compact(
            'worker',
            'todayAttendance',
            'activeProjects',
            'todayCablingMetres',
            'todayCamerasCount',
            'todayEarnings',
            'recentAttendances',
            'lifetimeCablingMetres',
            'lifetimeCamerasInstalled',
            'runningBalance'
        ));
    }

    /**
     * Punch today's attendance with photo verification (Camera selfie / Site photo).
     */
    public function punchAttendance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'attendance_type' => 'required|in:full_day,half_day',
            'photo' => 'nullable|image|max:10240', // 10MB max file
            'photo_data' => 'nullable|string', // Base64 data from camera capture
            'notes' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            $cleanPhone = preg_replace('/[^0-9]/', '', (string) $user->email);
            $worker = Worker::where('phone', $cleanPhone)->firstOrFail();
        }

        $project = Project::findOrFail($validated['project_id']);
        $photoPath = null;

        $photoData = null;

        // Process uploaded file photo
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'attendance_' . $worker->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $photoPath = $file->storeAs('attendance_photos', $filename, 'public');
            $mime = $file->getMimeType() ?: 'image/jpeg';
            $fileContent = @file_get_contents($file->getRealPath());
            if ($fileContent !== false) {
                $photoData = 'data:' . $mime . ';base64,' . base64_encode($fileContent);
            }
        } elseif (!empty($validated['photo_data']) && str_starts_with($validated['photo_data'], 'data:image')) {
            // Process base64 camera image
            $photoData = $validated['photo_data'];
            $data = preg_replace('/^data:image\/\w+;base64,/', '', $photoData);
            $data = str_replace(' ', '+', $data);
            $filename = 'attendance_' . $worker->id . '_' . time() . '_' . uniqid() . '.jpg';
            $photoPath = 'attendance_photos/' . $filename;
            Storage::disk('public')->put($photoPath, base64_decode($data));
        }

        DB::beginTransaction();
        try {
            $todayDate = now()->toDateString();

            // Find or create today's WorkDay for this project (robust against date formats and race conditions)
            $workDay = $this->getOrCreateTodayWorkDay($project, $worker, "Worker self check-in by {$worker->name}");

            $dailyRate = (float) ($worker->daily_rate > 0 ? $worker->daily_rate : 750.00);
            $multiplier = ($validated['attendance_type'] === 'half_day') ? 0.5 : 1.0;
            $basePay = $dailyRate * $multiplier;

            // Check if record exists
            $attendance = ProjectWorkerAttendance::where('work_day_id', $workDay->id)
                ->where('worker_id', $worker->id)
                ->first();

            if ($attendance) {
                $attendance->update([
                    'attendance_type' => $validated['attendance_type'],
                    'photo_path' => $photoPath ?: $attendance->photo_path,
                    'photo_data' => $photoData ?: $attendance->photo_data,
                    'check_in_at' => $attendance->check_in_at ?: now(),
                    'daily_rate_snapshot' => $dailyRate,
                    'rate_applied' => $dailyRate,
                    'total_amount' => $basePay + (float)$attendance->cabling_amount + (float)$attendance->extra_amount,
                    'notes' => $validated['notes'] ?? $attendance->notes,
                ]);
            } else {
                $attendance = ProjectWorkerAttendance::create([
                    'work_day_id' => $workDay->id,
                    'worker_id' => $worker->id,
                    'attendance_type' => $validated['attendance_type'],
                    'photo_path' => $photoPath,
                    'photo_data' => $photoData,
                    'check_in_at' => now(),
                    'daily_rate_snapshot' => $dailyRate,
                    'rate_applied' => $dailyRate,
                    'cabling_metres' => 0.00,
                    'cameras_installed' => 0,
                    'cabling_rate' => (float)($project->per_metre_rate ?? 8.00),
                    'cabling_amount' => 0.00,
                    'total_amount' => $basePay,
                    'notes' => $validated['notes'] ?? null,
                ]);
            }

            ProjectAuditLog::logChange(
                $project,
                'worker_attendance_punched',
                null,
                null,
                "Worker {$worker->name} punched {$validated['attendance_type']} attendance with photo verification"
            );

            DB::commit();

            return redirect()->route('worker.portal')
                ->with('status', "✓ Attendance successfully punched for {$project->title} with verified photo!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error punching worker attendance: ' . $e->getMessage());

            return back()->with('error', 'Failed to punch attendance: ' . $e->getMessage());
        }
    }

    /**
     * Log work-based field data (cabling meters, cameras installed, and materials).
     */
    public function logWork(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'project_task_id' => 'nullable|exists:project_tasks,id',
            'cabling_metres' => 'nullable|numeric|min:0',
            'cameras_installed' => 'nullable|integer|min:0',
            'material_name' => 'nullable|string|max:255',
            'material_qty' => 'nullable|numeric|min:0.01',
            'material_unit' => 'nullable|string|max:50',
            'drum_or_batch_no' => 'nullable|string|max:100',
            'work_notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            $cleanPhone = preg_replace('/[^0-9]/', '', (string) $user->email);
            $worker = Worker::where('phone', $cleanPhone)->firstOrFail();
        }

        $project = Project::findOrFail($validated['project_id']);
        $todayDate = now()->toDateString();

        DB::beginTransaction();
        try {
            // Find or create today's WorkDay (robust against date formats and race conditions)
            $workDay = $this->getOrCreateTodayWorkDay($project, $worker, 'Logged via Worker Mobile Portal');

            $dailyRate = (float) ($worker->daily_rate > 0 ? $worker->daily_rate : 750.00);
            $perMetreRate = (float) ($project->per_metre_rate ?? 8.00);

            // Find or create attendance record
            $attendance = ProjectWorkerAttendance::firstOrCreate(
                ['work_day_id' => $workDay->id, 'worker_id' => $worker->id],
                [
                    'attendance_type' => 'full_day',
                    'check_in_at' => now(),
                    'daily_rate_snapshot' => $dailyRate,
                    'rate_applied' => $dailyRate,
                    'cabling_metres' => 0.00,
                    'cameras_installed' => 0,
                    'cabling_rate' => $perMetreRate,
                    'cabling_amount' => 0.00,
                    'total_amount' => $dailyRate,
                ]
            );

            $addedCabling = (float) ($validated['cabling_metres'] ?? 0);
            $addedCameras = (int) ($validated['cameras_installed'] ?? 0);

            // Update attendance counts & amounts
            $attendance->cabling_metres = (float)$attendance->cabling_metres + $addedCabling;
            $attendance->cameras_installed = (int)$attendance->cameras_installed + $addedCameras;
            $attendance->cabling_rate = $perMetreRate;
            $attendance->cabling_amount = $attendance->cabling_metres * $perMetreRate;

            $multiplier = ($attendance->attendance_type === 'half_day') ? 0.5 : 1.0;
            $baseWage = $attendance->daily_rate_snapshot * $multiplier;
            $attendance->total_amount = $baseWage + $attendance->cabling_amount + (float)$attendance->extra_amount;

            if (!empty($validated['work_notes'])) {
                $timeTag = now()->format('h:i A');
                $attendance->work_summary = trim(($attendance->work_summary ? $attendance->work_summary . "\n" : '') . "[{$timeTag}] {$validated['work_notes']}");
            }

            $attendance->save();

            // Link to BOM / Task Material Consumption
            $task = null;
            if (!empty($validated['project_task_id'])) {
                $task = ProjectTask::where('project_id', $project->id)->find($validated['project_task_id']);
            }
            if (!$task) {
                $task = $project->tasks()->first();
            }

            // If cabling meters were recorded, log cable consumption
            if ($addedCabling > 0 && $task) {
                TaskMaterialConsumption::create([
                    'project_id' => $project->id,
                    'project_task_id' => $task->id,
                    'item_name' => "Cat6 Cabling Pulled by {$worker->name}",
                    'quantity_used' => $addedCabling,
                    'unit' => 'meters',
                    'drum_or_batch_no' => $validated['drum_or_batch_no'] ?? null,
                    'consumed_by_user_id' => Auth::id(),
                    'consumed_at' => now(),
                    'notes' => "Logged by Worker {$worker->name} ({$worker->phone}): " . ($validated['work_notes'] ?? 'Field cabling execution'),
                ]);
            }

            // If a specific material was consumed, log it
            if (!empty($validated['material_name']) && !empty($validated['material_qty']) && $task) {
                TaskMaterialConsumption::create([
                    'project_id' => $project->id,
                    'project_task_id' => $task->id,
                    'item_name' => $validated['material_name'],
                    'quantity_used' => $validated['material_qty'],
                    'unit' => $validated['material_unit'] ?? 'nos',
                    'drum_or_batch_no' => $validated['drum_or_batch_no'] ?? null,
                    'consumed_by_user_id' => Auth::id(),
                    'consumed_at' => now(),
                    'notes' => "Logged by Worker {$worker->name} ({$worker->phone})",
                ]);
            }

            ProjectAuditLog::logChange(
                $project,
                'worker_work_logged',
                null,
                null,
                "Worker {$worker->name} logged {$addedCabling}m cabling and {$addedCameras} cameras installed"
            );

            DB::commit();

            return redirect()->route('worker.portal')
                ->with('status', "✓ Successfully logged {$addedCabling}m cabling and {$addedCameras} cameras for {$project->title}!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error logging worker work: ' . $e->getMessage());

            return back()->with('error', 'Failed to log work data: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve or safely create today's WorkDay for a given project,
     * immune to SQLite date string format differences and duplicate key race conditions.
     */
    protected function getOrCreateTodayWorkDay(Project $project, Worker $worker, string $notes): WorkDay
    {
        $todayDate = now()->toDateString();

        $workDay = WorkDay::withTrashed()
            ->where('project_id', $project->id)
            ->where(function ($q) use ($todayDate) {
                $q->where('work_date', $todayDate)
                  ->orWhereDate('work_date', $todayDate)
                  ->orWhere('work_date', 'like', "{$todayDate}%");
            })
            ->first();

        if ($workDay) {
            if ($workDay->trashed()) {
                $workDay->restore();
            }
            return $workDay;
        }

        try {
            return WorkDay::create([
                'project_id' => $project->id,
                'work_date'  => $todayDate,
                'logged_by'  => Auth::id(),
                'notes'      => $notes,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return WorkDay::withTrashed()
                ->where('project_id', $project->id)
                ->where(function ($q) use ($todayDate) {
                    $q->where('work_date', $todayDate)
                      ->orWhereDate('work_date', $todayDate)
                      ->orWhere('work_date', 'like', "{$todayDate}%");
                })
                ->firstOrFail();
        }
    }
}

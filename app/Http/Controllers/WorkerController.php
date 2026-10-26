<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Models\ProjectWorkerAttendance;
use App\Models\WagePayment;
use App\Models\ProjectAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WorkerController extends Controller
{
    /**
     * Display a listing of daily-wage workers with financial balance highlights.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $statusFilter = $request->input('status', 'active');

        $query = Worker::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $workers = $query->withCount('attendances')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $activeWorkersCount = Worker::where('is_active', true)->count();
        $totalWorkersCount = Worker::count();

        // Calculate aggregate outstanding labor wage liability
        $totalOutstandingLiability = 0;
        foreach (Worker::where('is_active', true)->get() as $w) {
            $totalOutstandingLiability += $w->getRunningBalance();
        }

        return view('workers.index', compact(
            'workers',
            'search',
            'statusFilter',
            'activeWorkersCount',
            'totalWorkersCount',
            'totalOutstandingLiability'
        ));
    }

    /**
     * Show the form for creating a new worker.
     */
    public function create()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can create worker records with defined wage rates.');
        }

        return view('workers.create');
    }

    /**
     * Store a newly created worker in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isInternal()) {
            abort(403, 'Only authorized team members can register daily workers.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'daily_rate' => 'required|numeric|min:0',
            'skills' => 'nullable|array',
            'skills.*' => 'string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'photo' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'worker_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $photoPath = $file->storeAs('worker_photos', $filename, 'public');
            $validated['photo_path'] = $photoPath;
            $mime = $file->getMimeType() ?: 'image/jpeg';
            $fileContent = @file_get_contents($file->getRealPath());
            if ($fileContent !== false) {
                $validated['photo_data'] = 'data:' . $mime . ';base64,' . base64_encode($fileContent);
            }
        }

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;
        $validated['created_by'] = Auth::id();

        $worker = Worker::create($validated);

        ProjectAuditLog::logChange(
            $worker,
            'created',
            null,
            null,
            "Worker {$worker->name} registered with daily rate ₹{$worker->daily_rate}"
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'worker' => $worker,
                'message' => "Worker {$worker->name} registered successfully."
            ]);
        }

        return redirect()->route('workers.show', $worker)
            ->with('status', "Worker '{$worker->name}' added successfully!");
    }

    /**
     * Quick-add worker for technicians during work-day logging.
     * Matrix: Technician: Name and phone only. Employee: No.
     */
    public function quickStore(Request $request)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isTechnician()) {
            abort(403, 'Employees cannot add workers. Only technicians and administrators can register workers.');
        }

        if (Auth::user()->isTechnician()) {
            // Technician: Name and phone only, rate set to standard default
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:50',
            ]);
            $validated['daily_rate'] = $request->filled('daily_rate') ? (float)$request->input('daily_rate') : 700.00;
        } else {
            // Admin can set daily rate
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:50',
                'daily_rate' => 'required|numeric|min:0',
            ]);
        }

        $validated['is_active'] = true;

        $worker = Worker::create($validated);

        ProjectAuditLog::logChange(
            $worker,
            'quick_created',
            null,
            null,
            "Worker {$worker->name} quick-added by " . Auth::user()->name
        );

        return response()->json([
            'success' => true,
            'worker' => $worker,
            'message' => "Worker {$worker->name} registered."
        ]);
    }

    /**
     * Display worker profile and complete lifetime ledger timeline.
     */
    public function show(Worker $worker)
    {
        $attendances = ProjectWorkerAttendance::where('worker_id', $worker->id)
            ->with(['workDay.project'])
            ->join('work_days', 'project_worker_attendances.work_day_id', '=', 'work_days.id')
            ->orderByDesc('work_days.work_date')
            ->select('project_worker_attendances.*')
            ->paginate(20, ['*'], 'attendances_page');

        $payments = WagePayment::where('worker_id', $worker->id)
            ->with('settledBy')
            ->orderByDesc('payment_date')
            ->paginate(20, ['*'], 'payments_page');

        $totalEarned = $worker->getTotalEarnedLifetime();
        $totalPaid = $worker->getTotalPaidLifetime();
        $runningBalance = $worker->getRunningBalance();
        $totalDaysWorked = ProjectWorkerAttendance::where('worker_id', $worker->id)->count();

        return view('workers.show', compact(
            'worker',
            'attendances',
            'payments',
            'totalEarned',
            'totalPaid',
            'runningBalance',
            'totalDaysWorked'
        ));
    }

    /**
     * Show the form for editing the worker.
     */
    public function edit(Worker $worker)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can edit worker profiles and modify wage rates.');
        }

        return view('workers.edit', compact('worker'));
    }

    /**
     * Update the specified worker in storage.
     */
    public function update(Request $request, Worker $worker)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can update worker wage rates.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'daily_rate' => 'required|numeric|min:0',
            'skills' => 'nullable|array',
            'skills.*' => 'string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'photo' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($worker->photo_path && Storage::disk('public')->exists($worker->photo_path)) {
                Storage::disk('public')->delete($worker->photo_path);
            }
            $file = $request->file('photo');
            $filename = 'worker_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $photoPath = $file->storeAs('worker_photos', $filename, 'public');
            $validated['photo_path'] = $photoPath;
            $mime = $file->getMimeType() ?: 'image/jpeg';
            $fileContent = @file_get_contents($file->getRealPath());
            if ($fileContent !== false) {
                $validated['photo_data'] = 'data:' . $mime . ';base64,' . base64_encode($fileContent);
            }
        } elseif ($request->boolean('remove_photo')) {
            if ($worker->photo_path && Storage::disk('public')->exists($worker->photo_path)) {
                Storage::disk('public')->delete($worker->photo_path);
            }
            $validated['photo_path'] = null;
            $validated['photo_data'] = null;
        }

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : false;

        $worker->update($validated);

        return redirect()->route('workers.show', $worker)
            ->with('status', "Worker '{$worker->name}' details updated successfully.");
    }

    /**
     * Soft delete the specified worker.
     */
    public function destroy(Worker $worker)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can delete worker records.');
        }

        $name = $worker->name;
        $worker->delete();

        return redirect()->route('workers.index')
            ->with('status', "Worker '{$name}' removed.");
    }

    /**
     * Stream worker profile photo with local disk caching and database persistence fallback.
     */
    public function streamPhoto(Worker $worker)
    {
        // 1. If photo exists on local public disk, serve it directly
        if ($worker->photo_path) {
            $cleanPath = ltrim(str_replace('public/', '', $worker->photo_path), '/');
            if (Storage::disk('public')->exists($cleanPath)) {
                return response()->file(Storage::disk('public')->path($cleanPath), [
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                ]);
            }
        }

        // 2. If not on local disk, check photo_data in database
        if (!empty($worker->photo_data)) {
            $data = $worker->photo_data;
            $mime = 'image/jpeg';
            if (str_starts_with($data, 'data:')) {
                if (preg_match('/^data:([^;]+);base64,/', $data, $m)) {
                    $mime = $m[1];
                }
                $data = preg_replace('/^data:[^;]+;base64,/', '', $data);
            }
            $data = str_replace(' ', '+', $data);
            $binary = base64_decode($data);

            if ($binary) {
                if ($worker->photo_path) {
                    $cleanPath = ltrim(str_replace('public/', '', $worker->photo_path), '/');
                    try {
                        Storage::disk('public')->put($cleanPath, $binary);
                    } catch (\Throwable $e) {}
                }

                return response($binary, 200, [
                    'Content-Type' => $mime,
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                ]);
            }
        }

        abort(404, 'Worker photo not found');
    }

    /**
     * Fallback direct storage handler when web server falls through to Laravel on 404 for worker_photos/{filename}.
     */
    public function serveStoragePhoto(string $filename)
    {
        $path = 'worker_photos/' . $filename;
        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path), [
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        $worker = Worker::where('photo_path', 'LIKE', '%' . $filename)->first();
        if ($worker && !empty($worker->photo_data)) {
            $data = $worker->photo_data;
            $mime = 'image/jpeg';
            if (str_starts_with($data, 'data:')) {
                if (preg_match('/^data:([^;]+);base64,/', $data, $m)) {
                    $mime = $m[1];
                }
                $data = preg_replace('/^data:[^;]+;base64,/', '', $data);
            }
            $data = str_replace(' ', '+', $data);
            $binary = base64_decode($data);

            if ($binary) {
                try {
                    Storage::disk('public')->put($path, $binary);
                } catch (\Throwable $e) {}

                return response($binary, 200, [
                    'Content-Type' => $mime,
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                ]);
            }
        }

        abort(404, 'Worker photo not found');
    }
}

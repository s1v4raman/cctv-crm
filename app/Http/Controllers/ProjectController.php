<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects with company search, status filtering, and KPIs.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $statusFilter = $request->input('status', 'all');
        $priorityFilter = $request->input('priority', 'all');
        $assignedFilter = $request->input('assigned_to');

        // Overall Global KPIs (computed before filters)
        $totalProjectsCount = Project::count();
        $inProgressCount = Project::where('status', 'in_progress')->count();
        $completedCount = Project::where('status', 'completed')->count();
        $incompletedCount = Project::whereIn('status', ['incompleted', 'on_hold'])->count();
        $totalValuation = (float) Project::sum('budget');

        // Filtered Query
        $query = Project::with(['assignedUser', 'documents', 'lead'])
            ->withCount('documents');

        if ($search !== '') {
            $query->search($search);
        }

        if ($statusFilter && $statusFilter !== 'all') {
            $query->byStatus($statusFilter);
        }

        if ($priorityFilter && $priorityFilter !== 'all') {
            $query->where('priority', $priorityFilter);
        }

        if ($assignedFilter) {
            $query->where('assigned_to', $assignedFilter);
        }

        $projects = $query->orderByRaw("
            CASE 
                WHEN status = 'in_progress' THEN 1 
                WHEN status = 'incompleted' THEN 2 
                WHEN status = 'on_hold' THEN 3 
                WHEN status = 'completed' THEN 4 
                ELSE 5 
            END
        ")->latest('updated_at')
          ->paginate(12)
          ->withQueryString();

        // Technical team for assignments
        $teamMembers = User::whereIn('role', ['admin', 'staff', 'technician'])
            ->orderBy('name')
            ->get();

        $leads = Lead::orderBy('customer_name')->limit(60)->get();

        return view('projects.index', compact(
            'projects',
            'search',
            'statusFilter',
            'priorityFilter',
            'assignedFilter',
            'totalProjectsCount',
            'inProgressCount',
            'completedCount',
            'incompletedCount',
            'totalValuation',
            'teamMembers',
            'leads'
        ));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        $teamMembers = User::whereIn('role', ['admin', 'staff', 'technician'])->orderBy('name')->get();
        $leads = Lead::orderBy('customer_name')->get();

        // Auto-suggest next project code
        $nextCode = 'PRJ-' . date('Y') . '-' . str_pad(Project::count() + 1, 4, '0', STR_PAD_LEFT);

        return view('projects.create', compact('teamMembers', 'leads', 'nextCode'));
    }

    /**
     * Store a newly created project and its initial uploaded documents.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'project_code' => 'nullable|string|max:50|unique:projects,project_code',
            'lead_id' => 'nullable|exists:leads,id',
            'site_address' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:150',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:150',
            'description' => 'nullable|string',
            'status' => 'required|in:in_progress,completed,incompleted,on_hold,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'budget' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'documents.*' => 'nullable|file|max:30720', // max 30MB per file
        ]);

        if (empty($validated['project_code'])) {
            $validated['project_code'] = 'PRJ-' . date('Y') . '-' . str_pad(Project::count() + 1, 4, '0', STR_PAD_LEFT);
        }

        $validated['progress_percentage'] = $validated['progress_percentage'] ?? 0;
        $validated['created_by'] = auth()->id();

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now()->toDateString();
            if ($validated['progress_percentage'] < 100) {
                $validated['progress_percentage'] = 100;
            }
        }

        $project = Project::create($validated);

        // Handle uploaded documents / PDFs
        if ($request->hasFile('documents')) {
            $this->storeUploadedFiles($request->file('documents'), $project);
        }

        return redirect()->route('projects.show', $project)
            ->with('status', "Project #{$project->project_code} for '{$project->company_name}' successfully created!");
    }

    /**
     * Display the specified project dashboard with documents & status tracking.
     */
    public function show(Project $project)
    {
        $project->load(['documents.uploader', 'assignedUser', 'creator', 'lead']);

        $teamMembers = User::whereIn('role', ['admin', 'staff', 'technician'])->orderBy('name')->get();

        return view('projects.show', compact('project', 'teamMembers'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        $teamMembers = User::whereIn('role', ['admin', 'staff', 'technician'])->orderBy('name')->get();
        $leads = Lead::orderBy('customer_name')->get();

        return view('projects.edit', compact('project', 'teamMembers', 'leads'));
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'project_code' => 'required|string|max:50|unique:projects,project_code,' . $project->id,
            'lead_id' => 'nullable|exists:leads,id',
            'site_address' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:150',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:150',
            'description' => 'nullable|string',
            'status' => 'required|in:in_progress,completed,incompleted,on_hold,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'budget' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'documents.*' => 'nullable|file|max:30720',
        ]);

        if ($validated['status'] === 'completed') {
            if (!$project->completed_at) {
                $validated['completed_at'] = now()->toDateString();
            }
            if (($validated['progress_percentage'] ?? 0) < 100) {
                $validated['progress_percentage'] = 100;
            }
        } elseif ($validated['status'] !== 'completed') {
            $validated['completed_at'] = null;
        }

        $project->update($validated);

        if ($request->hasFile('documents')) {
            $this->storeUploadedFiles($request->file('documents'), $project);
        }

        return redirect()->route('projects.show', $project)
            ->with('status', "Project #{$project->project_code} updated successfully.");
    }

    /**
     * Remove the specified project from storage along with its documents.
     */
    public function destroy(Project $project)
    {
        $code = $project->project_code;

        // Delete physical files
        foreach ($project->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('status', "Project #{$code} and all attached documents were permanently removed.");
    }

    /**
     * 1-Click Status & Progress update from listing or project view.
     */
    public function updateStatus(Request $request, Project $project)
    {
        $validated = $request->validate([
            'status' => 'required|in:in_progress,completed,incompleted,on_hold,cancelled',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($request->has('progress_percentage')) {
            $updateData['progress_percentage'] = (int) $validated['progress_percentage'];
        }

        if ($validated['status'] === 'completed') {
            $updateData['completed_at'] = now()->toDateString();
            $updateData['progress_percentage'] = 100;
        } elseif ($project->status === 'completed' && $validated['status'] !== 'completed') {
            $updateData['completed_at'] = null;
        }

        $project->update($updateData);

        return back()->with('status', "Project #{$project->project_code} status changed to '{$project->status_label}'.");
    }

    /**
     * Upload additional documents and PDFs to an existing project.
     */
    public function uploadDocuments(Request $request, Project $project)
    {
        $request->validate([
            'document_title' => 'nullable|string|max:255',
            'documents' => 'required|array|min:1',
            'documents.*' => 'required|file|max:30720', // 30MB
            'notes' => 'nullable|string|max:500',
        ]);

        $customTitle = $request->input('document_title');
        $notes = $request->input('notes');

        $this->storeUploadedFiles($request->file('documents'), $project, $customTitle, $notes);

        return back()->with('status', 'Files successfully uploaded and linked to this project.');
    }

    /**
     * Download a project document with its original filename.
     */
    public function downloadDocument(Project $project, ProjectDocument $document)
    {
        if ($document->project_id !== $project->id) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'The requested file could not be found on storage.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Inline preview for PDFs and Images in browser.
     */
    public function viewDocument(Project $project, ProjectDocument $document)
    {
        if ($document->project_id !== $project->id) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'The requested file could not be found on storage.');
        }

        return Storage::disk('public')->response($document->file_path, $document->file_name);
    }

    /**
     * Delete an attached document.
     */
    public function deleteDocument(Project $project, ProjectDocument $document)
    {
        if ($document->project_id !== $project->id) {
            abort(404);
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('status', "Document '{$document->file_name}' was deleted.");
    }

    /**
     * Export project register to CSV.
     */
    public function exportCsv(Request $request)
    {
        $query = Project::with(['assignedUser']);

        if ($search = trim($request->input('search', ''))) {
            $query->search($search);
        }
        if ($status = $request->input('status')) {
            $query->byStatus($status);
        }
        if ($priority = $request->input('priority')) {
            if ($priority !== 'all') {
                $query->where('priority', $priority);
            }
        }

        $projects = $query->orderBy('company_name')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="projects_register_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($projects) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Project Code',
                'Company Name',
                'Project Title',
                'Status',
                'Priority',
                'Progress %',
                'Budget (INR)',
                'Site Address',
                'Contact Person',
                'Contact Phone',
                'Start Date',
                'Target Deadline',
                'Completed Date',
                'Assigned Lead Engineer',
                'Created At',
            ]);

            foreach ($projects as $prj) {
                fputcsv($file, [
                    $prj->project_code,
                    $prj->company_name,
                    $prj->title,
                    $prj->status_label,
                    ucfirst($prj->priority),
                    $prj->progress_percentage . '%',
                    number_format((float) $prj->budget, 2, '.', ''),
                    $prj->site_address ?? 'N/A',
                    $prj->contact_person ?? 'N/A',
                    $prj->contact_phone ?? 'N/A',
                    $prj->start_date ? $prj->start_date->format('d-m-Y') : 'N/A',
                    $prj->deadline ? $prj->deadline->format('d-m-Y') : 'N/A',
                    $prj->completed_at ? $prj->completed_at->format('d-m-Y') : 'N/A',
                    $prj->assignedUser ? $prj->assignedUser->name : 'Unassigned',
                    $prj->created_at->format('d-m-Y H:i'),
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Helper to store uploaded files into storage and create records.
     */
    protected function storeUploadedFiles(array $files, Project $project, ?string $customTitle = null, ?string $notes = null): void
    {
        $folder = 'project_documents/' . $project->id;

        foreach ($files as $index => $file) {
            if (!$file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $size = $file->getSize();

            $title = $customTitle;
            if (empty($title)) {
                $title = pathinfo($originalName, PATHINFO_FILENAME);
            } elseif (count($files) > 1) {
                $title = $customTitle . ' (' . ($index + 1) . ')';
            }

            $storedName = time() . '_' . uniqid() . '.' . $ext;
            $path = $file->storeAs($folder, $storedName, 'public');

            ProjectDocument::create([
                'project_id' => $project->id,
                'title' => $title,
                'file_path' => $path,
                'file_name' => $originalName,
                'file_size' => $size,
                'file_type' => $ext,
                'uploaded_by' => auth()->id(),
                'notes' => $notes,
            ]);
        }
    }
}

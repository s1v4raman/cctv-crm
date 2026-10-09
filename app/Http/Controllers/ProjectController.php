<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectAuditLog;
use App\Models\ProjectDocument;
use App\Models\ProjectMaterial;
use App\Models\Site;
use App\Models\User;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $typeFilter = $request->input('type', 'all');
        $companyFilter = $request->input('company', 'all');
        $assignedFilter = $request->input('assigned') ?: $request->input('assigned_to');

        // Overall Global KPIs (computed before filters)
        $totalProjectsCount = Project::count();
        $pendingApprovalCount = Project::where('status', 'pending_approval')->count();
        $inProgressCount = Project::where('status', 'in_progress')->count();
        $completedCount = Project::where('status', 'completed')->count();
        $draftCount = Project::where('status', 'draft')->count();
        $incompletedCount = Project::whereIn('status', ['incompleted', 'on_hold'])->count();
        $totalValuation = (float) Project::sum('budget');

        // Project Type Segmentation Counts & Valuations
        $hardwareCctvCount = Project::where('project_type', 'hardware_cctv')->count();
        $hardwareAttendanceCount = Project::where('project_type', 'hardware_attendance')->count();
        $softwareWebCount = Project::where('project_type', 'software_web')->count();
        $hybridCount = Project::where('project_type', 'hybrid')->count();
        $hardwareValuation = (float) Project::whereIn('project_type', ['hardware_cctv', 'hardware_attendance'])->sum('budget');
        $softwareValuation = (float) Project::where('project_type', 'software_web')->sum('budget');

        // Filtered Query
        $query = Project::with(['assignedUser', 'leadTechnician', 'company', 'site', 'documents'])
            ->withCount(['documents', 'materials', 'workDays', 'ipDevices']);

        if ($search !== '') {
            $query->search($search);
        }

        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($priorityFilter && $priorityFilter !== 'all') {
            $query->where('priority', $priorityFilter);
        }

        if ($typeFilter && $typeFilter !== 'all') {
            $query->byType($typeFilter);
        }

        if ($companyFilter && $companyFilter !== 'all') {
            $query->where('company_id', $companyFilter);
        }

        if ($assignedFilter) {
            $query->where(function ($q) use ($assignedFilter) {
                $q->where('assigned_to', $assignedFilter)
                  ->orWhere('lead_technician_id', $assignedFilter)
                  ->orWhere('created_by', $assignedFilter);
            });
        }

        $projects = $query->orderByRaw("
            CASE 
                WHEN status = 'pending_approval' THEN 1
                WHEN status = 'in_progress' THEN 2 
                WHEN status = 'approved' THEN 3
                WHEN status = 'draft' THEN 4
                WHEN status = 'on_hold' THEN 5 
                WHEN status = 'completed' THEN 6 
                ELSE 7 
            END
        ")->latest('updated_at')
          ->paginate(12)
          ->withQueryString();

        $companies = Company::where('is_active', true)->get();
        $teamMembers = User::whereIn('role', ['admin', 'staff', 'technician'])->orderBy('name')->get();
        $leads = Lead::orderBy('customer_name')->limit(60)->get();
        $projectTypes = Project::projectTypeOptions();

        return view('projects.index', compact(
            'projects',
            'search',
            'statusFilter',
            'priorityFilter',
            'typeFilter',
            'companyFilter',
            'assignedFilter',
            'totalProjectsCount',
            'pendingApprovalCount',
            'inProgressCount',
            'completedCount',
            'draftCount',
            'incompletedCount',
            'hardwareCctvCount',
            'hardwareAttendanceCount',
            'softwareWebCount',
            'hybridCount',
            'hardwareValuation',
            'softwareValuation',
            'totalValuation',
            'companies',
            'projectTypes',
            'teamMembers',
            'leads'
        ));
    }

    /**
     * Show the 4-step Project Creation Wizard.
     */
    public function create()
    {
        $teamMembers = User::whereIn('role', ['admin', 'staff', 'technician'])->orderBy('name')->get();
        $technicians = User::where('role', 'technician')->orWhere('role', 'admin')->orderBy('name')->get();
        $companies = Company::where('is_active', true)->get();
        $sites = Site::latest()->limit(50)->get();
        $leads = Lead::orderBy('customer_name')->get();
        $projectTypes = Project::projectTypeOptions();

        // Auto-suggest next project code
        $nextCode = 'PRJ-' . date('Y') . '-' . str_pad(Project::count() + 1, 4, '0', STR_PAD_LEFT);

        return view('projects.create_wizard', compact(
            'teamMembers',
            'technicians',
            'companies',
            'sites',
            'leads',
            'nextCode',
            'projectTypes'
        ));
    }

    /**
     * Store project from the 4-step wizard (or draft auto-save).
     */
    public function store(Request $request)
    {
        $isDraft = $request->input('action') === 'save_draft';

        $rules = [
            'site_id' => 'nullable|exists:sites,id',
            'project_code' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'project_type' => 'required|in:hardware_cctv,hardware_attendance,networking,access_control,software_web,hybrid',
            'lead_technician_id' => 'nullable|exists:users,id',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'nullable|string|max:50',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'company_name' => 'nullable|string|max:255',
            'po_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'requirements' => 'nullable|array',
            'hardware_specs' => 'nullable|array',
            'software_specs' => 'nullable|array',
            'materials' => 'nullable|array',
            'materials.*.item_name' => 'required|string|max:255',
            'materials.*.quantity' => 'required|numeric|min:0.01',
            'materials.*.unit' => 'required|string|max:50',
            'materials.*.source' => 'required|in:warehouse,local_purchase',
            'materials.*.shop_name' => 'nullable|string|max:255',
            'materials.*.notes' => 'nullable|string|max:255',
        ];

        $validated = $request->validate($rules);

        $site = null;
        if (!empty($validated['site_id'])) {
            $site = Site::find($validated['site_id']);
        }

        $validated['company_name'] = $validated['company_name'] ?? ($site?->client_name ?? $site?->name ?? 'Direct Client');
        $validated['site_address'] = $site?->address ?? ($validated['site_address'] ?? null);
        $validated['contact_person'] = $site?->contact_person ?? $site?->client_name;
        $validated['contact_phone'] = $site?->contact_phone ?? $site?->client_phone;
        $validated['contact_email'] = $site?->client_email;

        $validated['project_code'] = $request->input('project_code') ?: ('PRJ-' . date('Y') . '-' . str_pad(Project::count() + 1, 4, '0', STR_PAD_LEFT));
        $validated['created_by'] = Auth::id();
        $validated['assigned_to'] = $validated['lead_technician_id'] ?? Auth::id();

        // Default status
        if ($isDraft) {
            $validated['status'] = 'draft';
        } elseif ($request->filled('status')) {
            $validated['status'] = $request->input('status');
        } else {
            // If created by Admin and company is provided, can directly be approved
            if (Auth::user()->isAdmin() && $request->filled('company_id')) {
                $validated['company_id'] = $request->input('company_id');
                $validated['approved_by'] = Auth::id();
                $validated['approved_on'] = now();
                $validated['approved_value'] = $validated['budget'] ?? null;
                $validated['status'] = 'in_progress';
            } else {
                $validated['status'] = 'pending_approval';
            }
        }

        DB::beginTransaction();
        try {
            $project = Project::create($validated);

            // Store BOM Materials if any
            if (!empty($validated['materials'])) {
                foreach ($validated['materials'] as $mat) {
                    ProjectMaterial::create([
                        'project_id' => $project->id,
                        'item_name' => $mat['item_name'],
                        'quantity' => $mat['quantity'],
                        'unit' => $mat['unit'],
                        'source' => $mat['source'],
                        'shop_name' => $mat['shop_name'] ?? null,
                        'notes' => $mat['notes'] ?? null,
                        'status' => 'pending',
                    ]);
                }
            }

            ProjectAuditLog::logChange(
                $project,
                'created',
                null,
                $project->status,
                "Project {$project->project_code} created with status '{$project->status}'"
            );

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'project' => $project,
                    'redirect' => route('projects.show', $project),
                ]);
            }

            return redirect()->route('projects.show', $project)
                ->with('status', "Project #{$project->project_code} created successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating project: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified project dashboard with 5 tabs.
     */
    public function show(Project $project, Request $request)
    {
        $project->load([
            'site',
            'company',
            'leadTechnician',
            'assignedUser',
            'creator',
            'approver',
            'materials',
            'documents.uploader',
            'workDays.attendances.worker',
            'ipDevices',
            'auditLogs.user',
        ]);

        $activeTab = $request->input('tab', 'overview');
        $companies = Company::where('is_active', true)->get();
        $workers = Worker::where('is_active', true)->orderBy('name')->get();
        $technicians = User::whereIn('role', ['admin', 'technician', 'staff'])->orderBy('name')->get();

        // Calculate Work Days & Wages KPI for Tab 4
        $totalManDays = 0;
        $totalCablingMetres = 0;
        $totalLaborCost = 0;

        foreach ($project->workDays as $wd) {
            foreach ($wd->attendances as $att) {
                $totalManDays += ($att->attendance_type === 'half_day' ? 0.5 : 1);
                $totalCablingMetres += (float) $att->cabling_metres;
                $totalLaborCost += (float) $att->total_amount;
            }
        }

        // Uninvoiced DC count for Tab 3
        $uninvoicedDcCount = $project->documents()
            ->where('document_type', 'delivery_challan')
            ->where('is_invoiced', false)
            ->count();

        return view('projects.show', compact(
            'project',
            'activeTab',
            'companies',
            'workers',
            'technicians',
            'totalManDays',
            'totalCablingMetres',
            'totalLaborCost',
            'uninvoicedDcCount'
        ));
    }

    /**
     * Admin Approval Workflow: Assigns executing company and approves project.
     */
    public function approve(Request $request, Project $project)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can approve projects.');
        }

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'per_metre_rate' => 'required|numeric|min:0',
            'approved_value' => 'nullable|numeric|min:0',
            'po_number' => 'nullable|string|max:100',
        ]);

        $oldStatus = $project->status;
        $company = Company::findOrFail($validated['company_id']);

        $project->update([
            'company_id' => $company->id,
            'per_metre_rate' => $validated['per_metre_rate'],
            'approved_value' => $validated['approved_value'] ?? $project->budget,
            'po_number' => $validated['po_number'] ?? $project->po_number,
            'approved_by' => Auth::id(),
            'approved_on' => now(),
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        ProjectAuditLog::logChange(
            $project,
            'approved',
            $oldStatus,
            'approved',
            "Approved by " . Auth::user()->name . " and assigned to {$company->name} (Per-metre rate: ₹{$project->per_metre_rate})"
        );

        return back()->with('status', "Project #{$project->project_code} approved and assigned to '{$company->name}'!");
    }

    /**
     * Admin Rejection Workflow: Records rejection reason.
     */
    public function reject(Request $request, Project $project)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can reject projects.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $oldStatus = $project->status;

        $project->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        ProjectAuditLog::logChange(
            $project,
            'rejected',
            $oldStatus,
            'rejected',
            "Rejected by " . Auth::user()->name . ". Reason: {$validated['rejection_reason']}"
        );

        return back()->with('status', "Project #{$project->project_code} was marked as rejected.");
    }

    /**
     * Add a material line item to project BOM (Tab 2).
     */
    public function addMaterial(Request $request, Project $project)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'source' => 'required|in:warehouse,local_purchase',
            'shop_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255',
        ]);

        $validated['project_id'] = $project->id;
        $validated['status'] = 'pending';

        $material = ProjectMaterial::create($validated);

        ProjectAuditLog::logChange(
            $project,
            'material_added',
            null,
            null,
            "Material '{$material->item_name}' ({$material->quantity} {$material->unit}) added to BOM"
        );

        return redirect()->route('projects.show', ['project' => $project, 'tab' => 'materials'])
            ->with('status', "Item '{$material->item_name}' added to Bill of Materials!");
    }

    /**
     * Delete a material line item from BOM.
     */
    public function deleteMaterial(Project $project, ProjectMaterial $material)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can delete materials from Bill of Materials.');
        }

        $name = $material->item_name;
        $material->delete();

        ProjectAuditLog::logChange(
            $project,
            'material_deleted',
            null,
            null,
            "Material '{$name}' removed from BOM"
        );

        return redirect()->route('projects.show', ['project' => $project, 'tab' => 'materials'])
            ->with('status', "Item '{$name}' removed from Bill of Materials.");
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        if (!Auth::user()->isAdmin()) {
            if ($project->created_by !== Auth::id()) {
                abort(403, 'You can only edit projects created by yourself.');
            }
            if (!in_array($project->status, ['draft', 'pending_approval'])) {
                abort(403, 'Projects can only be edited before administrator approval.');
            }
        }

        $teamMembers = User::whereIn('role', ['admin', 'staff', 'technician'])->orderBy('name')->get();
        $companies = Company::where('is_active', true)->get();
        $sites = Site::all();
        $leads = Lead::orderBy('customer_name')->get();
        $projectTypes = Project::projectTypeOptions();

        return view('projects.edit', compact('project', 'teamMembers', 'companies', 'sites', 'leads', 'projectTypes'));
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project)
    {
        if (!Auth::user()->isAdmin()) {
            if ($project->created_by !== Auth::id()) {
                abort(403, 'You can only edit projects created by yourself.');
            }
            if (!in_array($project->status, ['draft', 'pending_approval'])) {
                abort(403, 'Projects can only be edited before administrator approval.');
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'project_type' => 'required|in:hardware_cctv,hardware_attendance,networking,access_control,software_web,hybrid',
            'site_id' => 'nullable|exists:sites,id',
            'company_id' => 'nullable|exists:companies,id',
            'lead_technician_id' => 'nullable|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'lead_id' => 'nullable|exists:leads,id',
            'company_name' => 'nullable|string|max:255',
            'site_address' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'per_metre_rate' => 'nullable|numeric|min:0',
            'po_number' => 'nullable|string|max:100',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:draft,pending_approval,approved,in_progress,completed,on_hold,rejected,cancelled',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'hardware_specs' => 'nullable|array',
            'software_specs' => 'nullable|array',
            'requirements' => 'nullable|array',
        ]);

        // Keep lead_technician_id and assigned_to in sync
        if (!empty($validated['lead_technician_id']) && empty($validated['assigned_to'])) {
            $validated['assigned_to'] = $validated['lead_technician_id'];
        } elseif (!empty($validated['assigned_to']) && empty($validated['lead_technician_id'])) {
            $validated['lead_technician_id'] = $validated['assigned_to'];
        }

        $oldStatus = $project->status;

        if ($validated['status'] === 'completed') {
            if (!$project->completed_at) {
                $validated['completed_at'] = now()->toDateString();
            }
            $validated['progress_percentage'] = 100;
        } elseif ($validated['status'] !== 'completed') {
            $validated['completed_at'] = null;
        }

        $project->update($validated);

        if ($oldStatus !== $project->status) {
            ProjectAuditLog::logChange(
                $project,
                'status_changed',
                $oldStatus,
                $project->status,
                "Status changed from '{$oldStatus}' to '{$project->status}'"
            );
        }

        return redirect()->route('projects.show', $project)
            ->with('status', "Project #{$project->project_code} updated successfully.");
    }

    /**
     * Remove the specified project from storage (soft delete).
     */
    public function destroy(Project $project)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can delete projects.');
        }

        $code = $project->project_code;

        ProjectAuditLog::logChange(
            $project,
            'deleted',
            $project->status,
            'deleted',
            "Project #{$code} soft-deleted by " . Auth::user()->name
        );

        $project->delete();

        return redirect()->route('projects.index')
            ->with('status', "Project #{$code} was successfully removed.");
    }

    /**
     * 1-Click Status & Progress update from listing or project view.
     */
    public function updateStatus(Request $request, Project $project)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,pending_approval,approved,in_progress,completed,on_hold,rejected,cancelled',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
        ]);

        if (!Auth::user()->isAdmin()) {
            if (Auth::user()->isTechnician()) {
                // Technician: Request only per permission spec
                ProjectAuditLog::logChange(
                    $project,
                    'status_request',
                    $project->status,
                    $validated['status'],
                    "Status update to '{$validated['status']}' requested by Technician " . Auth::user()->name
                );
                return back()->with('status', "Status change request submitted to Administrator for review.");
            }
            abort(403, 'Employees cannot change project status. Only administrators can approve or transition status.');
        }

        $oldStatus = $project->status;
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

        ProjectAuditLog::logChange(
            $project,
            'status_changed',
            $oldStatus,
            $validated['status'],
            "Status updated to '{$validated['status']}'"
        );

        return back()->with('status', "Project #{$project->project_code} status changed to '{$project->status_label}'.");
    }

    /**
     * Export projects list to CSV.
     */
    public function exportCsv(Request $request)
    {
        $projects = Project::with(['leadTechnician', 'company', 'site'])->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="SecureVision_Projects_Portfolio_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($projects) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Project Code',
                'Executing Company',
                'Site / Client',
                'Title',
                'Project Type',
                'Hardware Terminals/Cameras',
                'Status',
                'Priority',
                'Budget (₹)',
                'Approved Value (₹)',
                'Lead Technician',
                'Start Date',
                'Deadline',
                'Created At',
            ]);

            foreach ($projects as $prj) {
                $specsSummary = '—';
                if (!empty($prj->hardware_specs)) {
                    $hw = $prj->hardware_specs;
                    $terminals = $hw['terminal_count'] ?? 0;
                    $cams = $hw['camera_count'] ?? 0;
                    $brand = $hw['device_brand'] ?? 'General';
                    $specsSummary = "{$terminals} Terminals, {$cams} Cams ({$brand})";
                } elseif (!empty($prj->software_specs)) {
                    $specsSummary = $prj->software_specs['webpage_url'] ?? 'Web Specs';
                }

                fputcsv($file, [
                    $prj->project_code,
                    $prj->company?->name ?? 'Unassigned',
                    $prj->site?->name ?? $prj->company_name,
                    $prj->title,
                    $prj->project_type_label,
                    $specsSummary,
                    $prj->status_label,
                    ucfirst($prj->priority),
                    $prj->budget ? number_format($prj->budget, 2) : '0.00',
                    $prj->approved_value ? number_format($prj->approved_value, 2) : '0.00',
                    $prj->leadTechnician?->name ?? 'Unassigned',
                    $prj->start_date ? $prj->start_date->format('d-m-Y') : 'N/A',
                    $prj->deadline ? $prj->deadline->format('d-m-Y') : 'N/A',
                    $prj->created_at->format('d-m-Y H:i'),
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * 1-Click Interactive Demo Access Link for external testers/clients.
     */
    public function demoAccess(Request $request)
    {
        $reviewer = User::firstOrCreate(
            ['email' => 'reviewer@cctvcrm.com'],
            [
                'name' => 'Project Reviewer (Interactive Demo)',
                'password' => bcrypt('reviewer123'),
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );

        Auth::login($reviewer);

        return redirect()->route('projects.index')->with(
            'status',
            'Interactive Testing Mode Active! You can now create new projects, upload documents, update milestones, and test full operations.'
        );
    }
}

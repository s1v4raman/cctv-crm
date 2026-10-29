<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectAuditLog;
use App\Models\ProjectDocument;
use App\Models\ProjectMaterial;
use App\Models\Site;
use App\Models\TaskMaterialConsumption;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        $companies = $this->getExecutingCompanies();
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
        $companies = $this->getExecutingCompanies();
        $sites = Site::latest()->limit(50)->get();
        $leads = Lead::orderBy('customer_name')->get();
        $projectTypes = Project::projectTypeOptions();
        $products = Product::select('id', 'name', 'name_key', 'unit', 'cost_price', 'unit_price')->orderBy('name')->get();
        $vendors = Vendor::select('id', 'name', 'name_key')->orderBy('name')->get();

        $savedProductsMap = $products->mapWithKeys(function ($p) {
            $key = $p->name_key ?: Product::normalizeNameKey($p->name);
            return [$key => ['id' => $p->id, 'name' => $p->name, 'unit' => $p->unit]];
        })->all();

        $savedVendorsSet = $vendors->map(function ($v) {
            return $v->name_key ?: Vendor::normalizeNameKey($v->name);
        })->filter()->unique()->values()->all();

        // Auto-suggest next project code safely
        $year = date('Y');
        $lastProject = Project::withTrashed()
            ->where('project_code', 'like', "PRJ-{$year}-%")
            ->orderByDesc('id')
            ->first();
        $nextSeq = 1;
        if ($lastProject && preg_match('/PRJ-\d{4}-(\d+)/', $lastProject->project_code, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }
        do {
            $nextCode = 'PRJ-' . $year . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
            $nextSeq++;
        } while (Project::withTrashed()->where('project_code', $nextCode)->exists());

        return view('projects.create_wizard', compact(
            'teamMembers',
            'technicians',
            'companies',
            'sites',
            'leads',
            'nextCode',
            'projectTypes',
            'products',
            'vendors',
            'savedProductsMap',
            'savedVendorsSet'
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
            'title' => $isDraft ? 'nullable|string|max:255' : 'required|string|max:255',
            'project_type' => $isDraft ? 'nullable|string' : 'required|in:hardware_cctv,hardware_attendance,networking,access_control,software_web,hybrid',
            'lead_technician_id' => 'nullable|exists:users,id',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'priority' => $isDraft ? 'nullable|string' : 'required|in:low,medium,high,urgent',
            'status' => 'nullable|string|max:50',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'company_name' => 'nullable|string|max:255',
            'po_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'requirements' => 'nullable|array',
            'hardware_specs' => 'nullable|array',
            'software_specs' => 'nullable|array',
            'materials' => 'nullable|array',
            'materials.*.item_name' => $isDraft ? 'nullable|string|max:255' : 'required|string|max:255',
            'materials.*.quantity' => $isDraft ? 'nullable|numeric|min:0' : 'required|numeric|min:0.01',
            'materials.*.unit' => $isDraft ? 'nullable|string|max:50' : 'required|string|max:50',
            'materials.*.source' => $isDraft ? 'nullable|string' : 'required|in:warehouse,local_purchase',
            'materials.*.shop_name' => 'nullable|string|max:255',
            'materials.*.notes' => 'nullable|string|max:255',
        ];

        $validated = $request->validate($rules);

        $site = null;
        if (!empty($validated['site_id'])) {
            $site = Site::find($validated['site_id']);
        }

        if ($isDraft) {
            $validated['title'] = $validated['title'] ?: ($site ? $site->name . ' - Draft Project' : 'Draft Project (' . date('d M Y') . ')');
            $validated['project_type'] = in_array($validated['project_type'] ?? null, ['hardware_cctv','hardware_attendance','networking','access_control','software_web','hybrid']) 
                ? $validated['project_type'] 
                : 'hardware_cctv';
            $validated['priority'] = in_array($validated['priority'] ?? null, ['low','medium','high','urgent']) 
                ? $validated['priority'] 
                : 'medium';
        }

        $validated['company_name'] = $validated['company_name'] ?? ($site?->client_name ?? $site?->name ?? 'Direct Client');
        $validated['site_address'] = $site?->address ?? ($validated['site_address'] ?? null);
        $validated['contact_person'] = $site?->contact_person ?? $site?->client_name;
        $validated['contact_phone'] = $site?->contact_phone ?? $site?->client_phone;
        $validated['contact_email'] = $site?->client_email;

        $proposedCode = $request->input('project_code');
        if (!$proposedCode || Project::withTrashed()->where('project_code', $proposedCode)->exists()) {
            $year = date('Y');
            $lastProject = Project::withTrashed()
                ->where('project_code', 'like', "PRJ-{$year}-%")
                ->orderByDesc('id')
                ->first();
            $nextSeq = 1;
            if ($lastProject && preg_match('/PRJ-\d{4}-(\d+)/', $lastProject->project_code, $matches)) {
                $nextSeq = ((int) $matches[1]) + 1;
            }
            do {
                $proposedCode = 'PRJ-' . $year . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
                $nextSeq++;
            } while (Project::withTrashed()->where('project_code', $proposedCode)->exists());
        }
        $validated['project_code'] = $proposedCode;
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
                $validated['approved_value'] = $validated['budget'] ?? 0.00;
                $validated['status'] = 'in_progress';
            } else {
                $validated['status'] = 'pending_approval';
            }
        }

        DB::beginTransaction();
        try {
            $project = Project::create($validated);

            // Store BOM Materials if any with auto-learning for products and vendors
            if (!empty($validated['materials'])) {
                foreach ($validated['materials'] as $mat) {
                    if (!empty($mat['item_name'])) {
                        $rawName = trim($mat['item_name']);
                        $cleanName = Product::cleanName($rawName);
                        $productKey = Product::normalizeNameKey($rawName);

                        if ($cleanName !== '') {
                            // Find or create product by normalized name_key
                            $product = Product::firstOrCreate(
                                ['name_key' => $productKey],
                                [
                                    'name' => $cleanName,
                                    'sku' => 'PRD-' . strtoupper(Str::random(6)),
                                    'status' => 'pending',
                                    'unit' => $mat['unit'] ?? 'nos',
                                    'created_by' => Auth::id(),
                                    'is_active' => true,
                                ]
                            );

                            $vendorId = null;
                            $source = $mat['source'] ?? 'warehouse';

                            // For local purchase with shop name, auto-learn vendor by normalized name_key
                            if ($source === 'local_purchase' && !empty($mat['shop_name'])) {
                                $rawShop = trim($mat['shop_name']);
                                $cleanShop = Vendor::cleanName($rawShop);
                                $vendorKey = Vendor::normalizeNameKey($rawShop);

                                if ($cleanShop !== '') {
                                    $vendor = Vendor::firstOrCreate(
                                        ['name_key' => $vendorKey],
                                        [
                                            'name' => $cleanShop,
                                            'status' => 'pending',
                                            'created_by' => Auth::id(),
                                        ]
                                    );
                                    $vendorId = $vendor->id;
                                }
                            }

                            ProjectMaterial::create([
                                'project_id' => $project->id,
                                'product_id' => $product->id,
                                'vendor_id' => $vendorId,
                                'item_name' => $cleanName,
                                'quantity' => $mat['quantity'] ?? 1,
                                'unit' => $mat['unit'] ?? 'nos',
                                'source' => $source,
                                'shop_name' => $mat['shop_name'] ?? null,
                                'notes' => $mat['notes'] ?? null,
                                'status' => 'pending',
                            ]);
                        }
                    }
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
            'materials.product',
            'materials.consumptions.task',
            'materials.consumptions.consumedBy',
            'materialConsumptions.task',
            'materialConsumptions.projectMaterial',
            'materialConsumptions.product',
            'materialConsumptions.consumedBy',
            'documents.uploader',
            'workDays.attendances.worker',
            'ipDevices',
            'auditLogs.user',
            'tasks.checklists.completedByUser',
            'tasks.assignedUser',
            'tasks.completedByUser',
            'tasks.dependsOnTask',
            'tasks.blockedByUser',
            'tasks.materialConsumptions',
        ]);

        $activeTab = $request->input('tab', 'overview');
        $activeTaskView = $request->input('view', 'kanban');
        $companies = $this->getExecutingCompanies();
        $workers = Worker::where('is_active', true)->orderBy('name')->get();
        $technicians = User::whereIn('role', ['admin', 'technician', 'staff'])->orderBy('name')->get();
        $availableProducts = Product::where('is_active', true)->orderBy('name')->get();

        // Calculate BOM & Material Consumption metrics for Tab 2 & 6
        $totalBomQuantity = (float) $project->materials->sum('quantity');
        $totalConsumedQuantity = (float) $project->materialConsumptions->sum('quantity_used');
        $totalCableMeters = (float) $project->materialConsumptions->where('unit', 'meters')->sum('quantity_used');
        $totalWastageQuantity = (float) $project->materialConsumptions->sum('wastage_quantity');

        // Calculate Work Days & Wages KPI for Tab 4 and Tab 1 Overview
        $totalManDays = 0;
        $totalCablingMetres = 0;
        $totalLaborCost = 0;
        $totalLaborPaid = 0;
        $totalLaborPendingPay = 0;
        $workerAnalysis = [];

        foreach ($project->workDays as $wd) {
            foreach ($wd->attendances as $att) {
                $days = ($att->attendance_type === 'half_day' ? 0.5 : 1.0);
                $totalManDays += $days;
                $totalCablingMetres += (float) $att->cabling_metres;
                $rowAmount = (float) $att->total_amount;
                $totalLaborCost += $rowAmount;

                if ($att->is_paid) {
                    $totalLaborPaid += $rowAmount;
                } else {
                    $totalLaborPendingPay += $rowAmount;
                }

                $wId = $att->worker_id ?: ('unknown_' . $att->id);
                if (!isset($workerAnalysis[$wId])) {
                    $workerAnalysis[$wId] = [
                        'worker' => $att->worker ?? (object) [
                            'id' => $att->worker_id,
                            'name' => 'Worker #' . ($att->worker_id ?: $att->id),
                            'daily_rate' => (float) $att->daily_rate_snapshot,
                            'phone' => '—',
                        ],
                        'shifts_count' => 0,
                        'man_days' => 0.0,
                        'cabling_metres' => 0.0,
                        'cameras_installed' => 0,
                        'total_earned' => 0.0,
                        'amount_paid' => 0.0,
                        'cost_to_pay' => 0.0,
                    ];
                }
                $workerAnalysis[$wId]['shifts_count'] += 1;
                $workerAnalysis[$wId]['man_days'] += $days;
                $workerAnalysis[$wId]['cabling_metres'] += (float) $att->cabling_metres;
                $workerAnalysis[$wId]['cameras_installed'] += (int) $att->cameras_installed;
                $workerAnalysis[$wId]['total_earned'] += $rowAmount;
                if ($att->is_paid) {
                    $workerAnalysis[$wId]['amount_paid'] += $rowAmount;
                } else {
                    $workerAnalysis[$wId]['cost_to_pay'] += $rowAmount;
                }
            }
        }

        // Uninvoiced DC count for Tab 3
        $uninvoicedDcCount = $project->documents()
            ->where('document_type', 'delivery_challan')
            ->where('is_invoiced', false)
            ->count();

        // Tasks & Workflow Templates for Tab 6
        $tasksCount = $project->tasks->count();
        $completedTasksCount = $project->tasks->where('status', 'completed')->count();
        $inProgressTasksCount = $project->tasks->where('status', 'in_progress')->count();
        $underReviewTasksCount = $project->tasks->where('status', 'under_review')->count();
        $pendingTasksCount = $project->tasks->where('status', 'pending')->count();
        $blockedTasksCount = $project->tasks->where('status', 'blocked')->count();
        $taskCompletionPercentage = $tasksCount > 0 ? (int) round(($completedTasksCount / $tasksCount) * 100) : 0;
        $tasksByPhase = $project->tasks->groupBy('phase_name');
        $kanbanColumns = \App\Models\ProjectTask::kanbanColumns();
        $workflowTemplates = \App\Services\ProjectWorkflowTemplateService::getTemplatesForType($project->project_type ?? 'hardware_cctv');
        $allWorkflowTemplates = \App\Services\ProjectWorkflowTemplateService::getAllTemplates();

        return view('projects.show', compact(
            'project',
            'activeTab',
            'activeTaskView',
            'companies',
            'workers',
            'technicians',
            'totalManDays',
            'totalCablingMetres',
            'totalLaborCost',
            'totalLaborPaid',
            'totalLaborPendingPay',
            'workerAnalysis',
            'uninvoicedDcCount',
            'tasksCount',
            'completedTasksCount',
            'inProgressTasksCount',
            'underReviewTasksCount',
            'pendingTasksCount',
            'blockedTasksCount',
            'taskCompletionPercentage',
            'tasksByPhase',
            'kanbanColumns',
            'workflowTemplates',
            'allWorkflowTemplates',
            'availableProducts',
            'totalBomQuantity',
            'totalConsumedQuantity',
            'totalCableMeters',
            'totalWastageQuantity'
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
            'approved_value' => !is_null($validated['approved_value'] ?? null) && $validated['approved_value'] !== '' ? (float) $validated['approved_value'] : (float) ($project->budget ?? 0.0),
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
     * Assign or reassign lead technician to project directly.
     */
    public function assignTechnician(Request $request, Project $project)
    {
        $validated = $request->validate([
            'lead_technician_id' => 'required|exists:users,id',
        ]);

        $project->update([
            'lead_technician_id' => $validated['lead_technician_id'],
            'assigned_to' => $validated['lead_technician_id'],
        ]);

        $tech = User::find($validated['lead_technician_id']);

        ProjectAuditLog::logChange(
            $project,
            'technician_assigned',
            null,
            null,
            "Lead technician assigned: {$tech->name}"
        );

        return redirect()->route('projects.show', ['project' => $project, 'tab' => 'overview'])
            ->with('status', "Lead Technician updated to {$tech->name} successfully.");
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
            'product_id' => 'nullable|exists:products,id',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'unit_cost' => 'nullable|numeric|min:0',
            'source' => 'required|in:warehouse,local_purchase,company_stock,bought_from_shop',
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
     * Retrieve active executing companies with fallback auto-seeding.
     */
    protected function getExecutingCompanies()
    {
        $companies = Company::where('is_active', true)->get();
        if ($companies->isEmpty()) {
            Company::firstOrCreate(['name' => 'Precision IT Systems'], ['code' => 'PRECISION', 'is_active' => true]);
            Company::firstOrCreate(['name' => 'NP Solutions'], ['code' => 'NPSOL', 'is_active' => true]);
            Company::firstOrCreate(['name' => 'Linepix'], ['code' => 'LINEPIX', 'is_active' => true]);
            $companies = Company::where('is_active', true)->get();
        }
        return $companies;
    }

    /**
     * 1-Click Interactive Demo Access Link for external testers/clients.
     * Local development only: it signs the visitor in without a password.
     */
    public function demoAccess(Request $request)
    {
        abort_unless(app()->environment('local'), 404);

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

<?php

namespace App\Http\Controllers;

use App\Models\ExpenseClaim;
use App\Models\InstallationJob;
use App\Models\NotificationLog;
use App\Models\ServiceTicket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExpenseClaimController extends Controller
{
    /**
     * Display the Expense & Travel Claims Hub.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        // Admin and staff manage everyone's requests (User::canDecideFor stops self-approval)
        $canManage = $user->isOffice();

        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $categoryFilter = $request->input('category');
        $statusFilter = $request->input('status');
        $employeeFilter = $request->input('employee_id');
        $tab = $request->input('tab', $canManage ? 'pending' : 'my_claims');

        // Target claim auto-focus from notification alerts
        $targetClaim = null;
        if ($claimId = $request->input('claim_id')) {
            $targetClaim = ExpenseClaim::find($claimId);
        } elseif ($claimNo = $request->input('claim_no')) {
            $targetClaim = ExpenseClaim::where('claim_no', $claimNo)->first();
        }

        if ($targetClaim) {
            $selectedMonth = $targetClaim->expense_date->format('Y-m');
            $tab = $canManage ? 'all_claims' : 'my_claims';
        }

        $monthCarbon = Carbon::parse($selectedMonth . '-01');
        $monthStart = $monthCarbon->copy()->startOfMonth();
        $monthEnd = $monthCarbon->copy()->endOfMonth();

        // Pending Claims for Admin Approval Queue
        $pendingQuery = ExpenseClaim::with(['user', 'site', 'project.site', 'installationJob.quotation.lead', 'serviceTicket'])
            ->pending()
            ->orderBy('created_at', 'asc');
        $pendingClaims = $pendingQuery->get();
        $pendingCount = $pendingClaims->count();
        $pendingTotalAmount = (float) $pendingClaims->sum('amount');

        // My Claims (for logged-in user)
        $myQuery = ExpenseClaim::with(['site', 'project.site', 'installationJob.quotation.lead', 'serviceTicket', 'actioner'])
            ->forUser($user->id)
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->orderByDesc('expense_date');

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected', 'paid', 'cancelled'])) {
            $myQuery->where('status', $statusFilter);
        }
        if ($categoryFilter) {
            $myQuery->where('expense_category', $categoryFilter);
        }
        $myClaims = $myQuery->paginate(12, ['*'], 'my_page')->withQueryString();

        // Master Company-wide Expenses (for Admin / Finance team)
        $allQuery = ExpenseClaim::with(['user', 'site', 'project.site', 'installationJob.quotation.lead', 'serviceTicket', 'actioner'])
            ->orderByDesc('expense_date');

        if (!$targetClaim) {
            $allQuery->whereBetween('expense_date', [$monthStart, $monthEnd]);
        } else {
            $allQuery->where(function($q) use ($monthStart, $monthEnd, $targetClaim) {
                $q->whereBetween('expense_date', [$monthStart, $monthEnd])
                  ->orWhere('id', $targetClaim->id);
            });
        }

        if ($search = trim((string) $request->input('search', ''))) {
            $allQuery->where(function($q) use ($search) {
                $q->where('claim_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected', 'paid', 'cancelled'])) {
            $allQuery->where('status', $statusFilter);
        }
        if ($categoryFilter) {
            $allQuery->where('expense_category', $categoryFilter);
        }
        if ($employeeFilter) {
            $allQuery->where('user_id', $employeeFilter);
        }
        $allClaims = $allQuery->paginate(15, ['*'], 'all_page')->withQueryString();

        // Macro KPIs for this month
        $monthClaims = ExpenseClaim::whereBetween('expense_date', [$monthStart, $monthEnd])->get();
        $totalClaimedMonth = (float) $monthClaims->sum('amount');
        $totalPaidMonth    = (float) $monthClaims->where('status', 'paid')->sum('amount');
        $totalApprovedMonth= (float) $monthClaims->where('status', 'approved')->sum('amount');
        $totalFuelMonth    = (float) $monthClaims->where('expense_category', 'fuel_travel')->sum('amount');
        $totalKmMonth      = (float) $monthClaims->where('expense_category', 'fuel_travel')->sum('travel_distance_km');

        // Workforce & Active Jobs, Sites, Projects for selectors
        $workforce = User::whereIn('role', ['admin', 'staff', 'technician'])->orderBy('name')->get();
        $availableSites = \App\Models\Site::orderBy('name')->get();
        $assignedProjects = \App\Models\Project::with(['site', 'company'])
            ->whereIn('status', ['in_progress', 'approved', 'pending_approval'])
            ->latest('updated_at')
            ->get();
        $activeJobs = InstallationJob::with('quotation.lead')
            ->whereIn('status', ['scheduled', 'in_progress', 'completed'])
            ->orderByDesc('created_at')
            ->take(25)
            ->get();
        $activeTickets = ServiceTicket::with('lead')
            ->whereIn('status', ['open', 'in_progress', 'resolved'])
            ->orderByDesc('created_at')
            ->take(25)
            ->get();

        return view('finance.expenses.index', compact(
            'user',
            'canManage',
            'tab',
            'pendingClaims',
            'pendingCount',
            'pendingTotalAmount',
            'myClaims',
            'allClaims',
            'totalClaimedMonth',
            'totalPaidMonth',
            'totalApprovedMonth',
            'totalFuelMonth',
            'totalKmMonth',
            'workforce',
            'availableSites',
            'assignedProjects',
            'activeJobs',
            'activeTickets',
            'selectedMonth',
            'statusFilter',
            'categoryFilter',
            'employeeFilter'
        ));
    }

    /**
     * Submit an Expense / Travel Claim (Technician & Staff Self-Service or Admin on-behalf).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'             => ['nullable', 'exists:users,id'],
            'expense_category'    => ['required', 'in:fuel_travel,hardware_tools,food_lodging,toll_parking,emergency_materials,other'],
            'expense_date'        => ['required', 'date', 'after_or_equal:today - 60 days'],
            'amount'              => ['nullable', 'numeric', 'min:0.01', 'max:500000'],
            'travel_distance_km'  => ['nullable', 'numeric', 'min:0.1', 'max:5000'],
            'travel_from'         => ['nullable', 'string', 'max:150'],
            'travel_to'           => ['nullable', 'string', 'max:150'],
            'rate_per_km'         => ['nullable', 'numeric', 'min:1', 'max:100'],
            'linked_target'       => ['nullable', 'string', 'max:100'],
            'site_id'             => ['nullable', 'exists:sites,id'],
            'project_id'          => ['nullable', 'exists:projects,id'],
            'installation_job_id' => ['nullable', 'exists:installation_jobs,id'],
            'service_ticket_id'   => ['nullable', 'exists:service_tickets,id'],
            'new_site_name'       => ['nullable', 'string', 'max:200'],
            'new_site_address'    => ['nullable', 'string', 'max:500'],
            'new_client_name'     => ['nullable', 'string', 'max:200'],
            'new_site_phone'      => ['nullable', 'string', 'max:50'],
            'description'         => ['nullable', 'string', 'max:1000'],
            'receipt'             => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $targetUserId = (Auth::user()->isOffice() && !empty($validated['user_id']))
            ? (int) $validated['user_id']
            : Auth::id();

        // 1. Resolve Installation Site & Project linkage (or auto-create new site if entered)
        $siteId = $validated['site_id'] ?? null;
        $projectId = $validated['project_id'] ?? null;
        $jobId = $validated['installation_job_id'] ?? null;
        $siteName = null;

        if (!empty($validated['new_site_name']) && !empty($validated['new_site_address'])) {
            $createdSite = \App\Models\Site::create([
                'site_code'      => \App\Models\Site::generateSiteCode(),
                'name'           => $validated['new_site_name'],
                'address'        => $validated['new_site_address'],
                'client_name'    => $validated['new_client_name'] ?? null,
                'contact_person' => $validated['new_client_name'] ?: 'Site Manager',
                'phone'          => $validated['new_site_phone'] ?: '-',
                'created_by'     => Auth::id(),
            ]);
            $siteId = $createdSite->id;
            $siteName = $createdSite->name;
        }

        if (!empty($validated['linked_target'])) {
            $parts = explode(':', $validated['linked_target'], 2);
            if (count($parts) === 2) {
                [$targetType, $targetId] = $parts;
                if ($targetType === 'project') {
                    $projectId = (int) $targetId;
                    $prj = \App\Models\Project::find($projectId);
                    if ($prj) {
                        $siteName = $prj->title;
                        if ($prj->site_id) {
                            $siteId = $siteId ?: $prj->site_id;
                        }
                    }
                } elseif ($targetType === 'site') {
                    $siteId = (int) $targetId;
                    $st = \App\Models\Site::find($siteId);
                    if ($st) $siteName = $st->name;
                } elseif ($targetType === 'job') {
                    $jobId = (int) $targetId;
                    $jb = \App\Models\InstallationJob::with('quotation.lead')->find($jobId);
                    if ($jb) $siteName = $jb->job_no . ' (' . ($jb->quotation?->lead?->customer_name ?? 'Client') . ')';
                }
            }
        }

        // Auto-fill travel_to from site / project if not manually given
        $travelTo = $validated['travel_to'] ?? null;
        if (empty($travelTo)) {
            if ($siteId && ($s = \App\Models\Site::find($siteId))) {
                $travelTo = $s->name . ($s->address ? ' (' . $s->address . ')' : '');
                $siteName = $siteName ?: $s->name;
            } elseif ($projectId && ($p = \App\Models\Project::find($projectId))) {
                $travelTo = $p->title . ($p->site_address ? ' (' . $p->site_address . ')' : '');
                $siteName = $siteName ?: $p->title;
            } elseif ($jobId && ($j = \App\Models\InstallationJob::with('quotation.lead')->find($jobId))) {
                $lead = $j->quotation?->lead;
                $travelTo = ($lead?->customer_name ?? 'Client') . ($lead?->site_address ? ' - ' . $lead->site_address : '');
                $siteName = $siteName ?: $j->job_no;
            }
        }

        // Description is optional; fallback to clean human-readable default if omitted
        $description = !empty(trim((string)($validated['description'] ?? '')))
            ? trim($validated['description'])
            : ($siteName ? "Site allowance for {$siteName}" : "Field travel & operational allowance");

        $amount = !empty($validated['amount']) ? (float) $validated['amount'] : 0.0;
        $km = !empty($validated['travel_distance_km']) ? (float) $validated['travel_distance_km'] : null;
        $rate = !empty($validated['rate_per_km']) ? (float) $validated['rate_per_km'] : 6.0;

        // Smart Fuel KM Auto-Calculation
        if ($validated['expense_category'] === 'fuel_travel') {
            if ($km !== null && $km > 0 && $amount <= 0) {
                $amount = round($km * $rate, 2);
            }
        }

        if ($amount <= 0) {
            return back()->withInput()->with('error', 'Please provide a valid claim amount or distance in kilometers.');
        }

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('expense_receipts', 'public');
        }

        $claim = ExpenseClaim::create([
            'claim_no'            => ExpenseClaim::generateClaimNumber($validated['expense_date']),
            'user_id'             => $targetUserId,
            'site_id'             => $siteId,
            'project_id'          => $projectId,
            'installation_job_id' => $jobId,
            'service_ticket_id'   => $validated['service_ticket_id'] ?? null,
            'expense_category'    => $validated['expense_category'],
            'expense_date'        => $validated['expense_date'],
            'amount'              => $amount,
            'travel_distance_km'  => $km,
            'travel_from'         => $validated['travel_from'] ?? null,
            'travel_to'           => $travelTo,
            'rate_per_km'         => $rate,
            'description'         => $description,
            'receipt_path'        => $receiptPath,
            'status'              => 'pending',
        ]);

        // Multi-channel alert dispatch for submitted expense claim
        try {
            $claim->load('user');
            $alertService = app(\App\Services\AlertNotificationService::class);
            $alertService->sendAlert('expense_submitted', $claim->user ?? Auth::user(), [
                'employee_name'  => $claim->user?->name ?? Auth::user()->name,
                'expense_number' => $claim->claim_no,
                'amount'         => '₹' . number_format($amount, 2),
                'category'       => $claim->category_label,
                'expense_date'   => $validated['expense_date'],
                'description'    => $validated['description'],
                'link'           => route('finance.expenses.index', ['tab' => 'all_claims', 'claim_id' => $claim->id, 'claim_no' => $claim->claim_no]),
            ], $claim);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Expense submission alert failed: " . $e->getMessage());
        }

        if (Auth::user()->isTechnician()) {
            return redirect()
                ->route('technician.dashboard')
                ->with('status', "✅ Travel allowance / extra work claim #{$claim->claim_no} for ₹" . number_format($amount, 2) . " submitted to Admin for review.");
        }

        return redirect()
            ->route('finance.expenses.index', ['tab' => 'my_claims'])
            ->with('status', "Expense claim #{$claim->claim_no} for ₹" . number_format($amount, 2) . " submitted successfully and is under review.");
    }

    /**
     * Admin or Owner Update Expense Claim Details (Cost, KM, Category, Notes).
     */
    public function update(Request $request, ExpenseClaim $claim): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isOffice() && $claim->user_id !== $user->id) {
            abort(403, 'Unauthorized. You cannot modify this expense claim.');
        }

        if (!$user->isOffice() && $claim->status !== 'pending') {
            return back()->with('error', 'Only pending claims can be edited.');
        }

        $validated = $request->validate([
            'user_id'             => ['nullable', 'exists:users,id'],
            'expense_category'    => ['required', 'in:fuel_travel,hardware_tools,food_lodging,toll_parking,emergency_materials,other'],
            'expense_date'        => ['required', 'date'],
            'amount'              => ['nullable', 'numeric', 'min:0.01', 'max:500000'],
            'travel_distance_km'  => ['nullable', 'numeric', 'min:0.1', 'max:5000'],
            'travel_from'         => ['nullable', 'string', 'max:150'],
            'travel_to'           => ['nullable', 'string', 'max:150'],
            'rate_per_km'         => ['nullable', 'numeric', 'min:1', 'max:100'],
            'linked_target'       => ['nullable', 'string', 'max:100'],
            'site_id'             => ['nullable', 'exists:sites,id'],
            'project_id'          => ['nullable', 'exists:projects,id'],
            'installation_job_id' => ['nullable', 'exists:installation_jobs,id'],
            'service_ticket_id'   => ['nullable', 'exists:service_tickets,id'],
            'description'         => ['nullable', 'string', 'max:1000'],
            'status'              => ['nullable', 'in:pending,approved,rejected,paid,cancelled'],
            'admin_notes'         => ['nullable', 'string', 'max:1000'],
            'receipt'             => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $siteId = $validated['site_id'] ?? $claim->site_id;
        $projectId = $validated['project_id'] ?? $claim->project_id;
        $jobId = $validated['installation_job_id'] ?? $claim->installation_job_id;

        if (!empty($validated['linked_target'])) {
            $parts = explode(':', $validated['linked_target'], 2);
            if (count($parts) === 2) {
                [$targetType, $targetId] = $parts;
                if ($targetType === 'project') {
                    $projectId = (int) $targetId;
                    $prj = \App\Models\Project::find($projectId);
                    if ($prj && $prj->site_id) {
                        $siteId = $prj->site_id;
                    }
                } elseif ($targetType === 'site') {
                    $siteId = (int) $targetId;
                } elseif ($targetType === 'job') {
                    $jobId = (int) $targetId;
                }
            }
        }

        $amount = !empty($validated['amount']) ? (float) $validated['amount'] : (float) $claim->amount;
        $km = isset($validated['travel_distance_km']) ? (float) $validated['travel_distance_km'] : $claim->travel_distance_km;
        $rate = !empty($validated['rate_per_km']) ? (float) $validated['rate_per_km'] : ($claim->rate_per_km ?: 6.0);

        // If category is fuel and distance/rate was edited, auto-recompute amount if not explicitly provided
        if ($validated['expense_category'] === 'fuel_travel') {
            if (empty($validated['amount']) && $km > 0) {
                $amount = round($km * $rate, 2);
            }
        }

        $desc = !empty(trim((string)($validated['description'] ?? '')))
            ? trim($validated['description'])
            : $claim->description;

        $updateData = [
            'expense_category'    => $validated['expense_category'],
            'expense_date'        => $validated['expense_date'],
            'amount'              => $amount,
            'travel_distance_km'  => $km,
            'travel_from'         => $validated['travel_from'] ?? $claim->travel_from,
            'travel_to'           => $validated['travel_to'] ?? $claim->travel_to,
            'rate_per_km'         => $rate,
            'site_id'             => $siteId,
            'project_id'          => $projectId,
            'installation_job_id' => $jobId,
            'service_ticket_id'   => $validated['service_ticket_id'] ?? $claim->service_ticket_id,
            'description'         => $desc,
        ];

        if ($user->isOffice() && !empty($validated['user_id'])) {
            $updateData['user_id'] = (int) $validated['user_id'];
        }

        if ($user->isOffice() && array_key_exists('admin_notes', $validated)) {
            $updateData['admin_notes'] = $validated['admin_notes'];
        }

        if ($user->isOffice() && !empty($validated['status'])) {
            $updateData['status'] = $validated['status'];
            if ($validated['status'] === 'approved' && $claim->status !== 'approved') {
                $updateData['actioned_by'] = Auth::id();
                $updateData['actioned_at'] = now();
            }
        }

        if ($request->hasFile('receipt')) {
            $updateData['receipt_path'] = $request->file('receipt')->store('expense_receipts', 'public');
        }

        $claim->update($updateData);

        return back()->with('status', "✅ Expense claim #{$claim->claim_no} updated successfully (Amount: ₹" . number_format($amount, 2) . "). Changes are now reflected for the employee.");
    }

    /**
     * Admin Permanently Delete Expense Claim.
     */
    public function destroy(ExpenseClaim $claim): RedirectResponse
    {
        if (!Auth::user()->isOffice()) {
            abort(403, 'Unauthorized. Only management can delete claims.');
        }

        $claimNo = $claim->claim_no;
        $claim->delete();

        return back()->with('status', "Expense claim #{$claimNo} has been deleted.");
    }

    /**
     * Admin 1-Click Approve Expense Claim (with optional amount adjustment).
     */
    public function approve(Request $request, ExpenseClaim $claim): RedirectResponse
    {
        if (!Auth::user()->canDecideFor($claim->user_id)) {
            abort(403, 'You cannot approve this expense claim.');
        }

        if ($claim->status !== 'pending') {
            return back()->with('error', 'This claim has already been processed.');
        }

        $updateData = [
            'status'      => 'approved',
            'actioned_by' => Auth::id(),
            'actioned_at' => now(),
        ];

        // Optional on-the-fly adjustment by admin
        if ($request->filled('amount') && (float) $request->input('amount') > 0) {
            $updateData['amount'] = (float) $request->input('amount');
        }
        if ($request->filled('travel_distance_km')) {
            $updateData['travel_distance_km'] = (float) $request->input('travel_distance_km');
        }
        if ($request->filled('rate_per_km')) {
            $updateData['rate_per_km'] = (float) $request->input('rate_per_km');
        }

        $claim->update($updateData);
        $claim->load('user');

        // Multi-channel alert to employee
        try {
            $alertService = app(\App\Services\AlertNotificationService::class);
            $alertService->sendAlert('expense_status_updated', $claim->user ?? Auth::user(), [
                'employee_name'  => $claim->user?->name ?? 'Employee',
                'expense_number' => $claim->claim_no,
                'amount'         => '₹' . number_format((float) $claim->amount, 2),
                'category'       => $claim->category_label,
                'status'         => 'Approved',
                'actioner_name'  => Auth::user()->name,
                'review_notes'   => 'Approved for payment disbursal.',
                'link'           => route('finance.expenses.index', ['tab' => 'all_claims', 'claim_id' => $claim->id, 'claim_no' => $claim->claim_no]),
            ], $claim);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Expense approval alert failed: " . $e->getMessage());
        }

        return back()->with('status', "✅ Claim #{$claim->claim_no} for {$claim->user?->name} (₹" . number_format((float) $claim->amount, 2) . ") has been APPROVED.");
    }

    /**
     * Admin Reject Expense Claim with Reason.
     */
    public function reject(Request $request, ExpenseClaim $claim): RedirectResponse
    {
        if (!Auth::user()->canDecideFor($claim->user_id)) {
            abort(403, 'You cannot reject this expense claim.');
        }

        if ($claim->status !== 'pending') {
            return back()->with('error', 'This claim has already been processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $claim->update([
            'status'           => 'rejected',
            'actioned_by'      => Auth::id(),
            'actioned_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $claim->load('user');

        // Multi-channel alert to employee
        try {
            $alertService = app(\App\Services\AlertNotificationService::class);
            $alertService->sendAlert('expense_status_updated', $claim->user ?? Auth::user(), [
                'employee_name'  => $claim->user?->name ?? 'Employee',
                'expense_number' => $claim->claim_no,
                'amount'         => '₹' . number_format((float) $claim->amount, 2),
                'category'       => $claim->category_label,
                'status'         => 'Rejected',
                'actioner_name'  => Auth::user()->name,
                'review_notes'   => $validated['rejection_reason'],
                'link'           => route('finance.expenses.index', ['tab' => 'all_claims', 'claim_id' => $claim->id, 'claim_no' => $claim->claim_no]),
            ], $claim);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Expense rejection alert failed: " . $e->getMessage());
        }

        return back()->with('status', "Claim #{$claim->claim_no} has been rejected.");
    }

    /**
     * Admin Record Disbursed / Settled Payment for Claim.
     */
    public function markPaid(Request $request, ExpenseClaim $claim): RedirectResponse
    {
        if (!Auth::user()->canDecideFor($claim->user_id)) {
            abort(403, 'You cannot mark this expense claim as paid.');
        }

        $validated = $request->validate([
            'payment_method'    => ['required', 'in:cash,upi,bank_transfer,payroll_addition'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $claim->update([
            'status'            => 'paid',
            'payment_method'    => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'paid_at'           => now(),
            'actioned_by'       => Auth::id(),
        ]);

        $claim->load('user');

        try {
            $alertService = app(\App\Services\AlertNotificationService::class);
            $alertService->sendAlert('expense_status_updated', $claim->user ?? Auth::user(), [
                'employee_name'  => $claim->user?->name ?? 'Employee',
                'expense_number' => $claim->claim_no,
                'amount'         => '₹' . number_format((float) $claim->amount, 2),
                'category'       => $claim->category_label,
                'status'         => 'Paid & Disbursed (' . ucfirst(str_replace('_', ' ', $validated['payment_method'])) . ')',
                'actioner_name'  => Auth::user()->name,
                'review_notes'   => 'Disbursed via ' . ucfirst(str_replace('_', ' ', $validated['payment_method'])) . ($claim->payment_reference ? ' (Ref: ' . $claim->payment_reference . ')' : ''),
            ], $claim);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Expense paid alert failed: " . $e->getMessage());
        }

        return back()->with('status', "🎉 Claim #{$claim->claim_no} marked as DISBURSED / PAID (₹" . number_format((float) $claim->amount, 2) . ") via " . ucfirst(str_replace('_', ' ', $validated['payment_method'])) . ".");
    }

    /**
     * Employee Cancel Pending Claim.
     */
    public function cancel(ExpenseClaim $claim): RedirectResponse
    {
        $user = Auth::user();

        if ($claim->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized. You can only cancel your own claims.');
        }

        if ($claim->status !== 'pending') {
            return back()->with('error', 'Only pending claims can be cancelled.');
        }

        $claim->update([
            'status' => 'cancelled',
        ]);

        return back()->with('status', "Claim #{$claim->claim_no} has been cancelled.");
    }
}

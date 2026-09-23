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
        $isAdmin = $user->isAdmin();

        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $categoryFilter = $request->input('category');
        $statusFilter = $request->input('status');
        $employeeFilter = $request->input('employee_id');
        $tab = $request->input('tab', $isAdmin ? 'pending' : 'my_claims');

        $monthCarbon = Carbon::parse($selectedMonth . '-01');
        $monthStart = $monthCarbon->copy()->startOfMonth();
        $monthEnd = $monthCarbon->copy()->endOfMonth();

        // Pending Claims for Admin Approval Queue
        $pendingQuery = ExpenseClaim::with(['user', 'installationJob', 'serviceTicket'])
            ->pending()
            ->orderBy('created_at', 'asc');
        $pendingClaims = $pendingQuery->get();
        $pendingCount = $pendingClaims->count();
        $pendingTotalAmount = (float) $pendingClaims->sum('amount');

        // My Claims (for logged-in user)
        $myQuery = ExpenseClaim::with(['installationJob', 'serviceTicket', 'actioner'])
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
        $allQuery = ExpenseClaim::with(['user', 'installationJob', 'serviceTicket', 'actioner'])
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->orderByDesc('expense_date');

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

        // Workforce & Active Jobs for selectors
        $workforce = User::whereIn('role', ['admin', 'staff', 'technician'])->orderBy('name')->get();
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
            'isAdmin',
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
            'activeJobs',
            'activeTickets',
            'selectedMonth',
            'statusFilter',
            'categoryFilter',
            'employeeFilter'
        ));
    }

    /**
     * Submit an Expense / Travel Claim (Technician & Staff Self-Service).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'expense_category'    => ['required', 'in:fuel_travel,hardware_tools,food_lodging,toll_parking,emergency_materials,other'],
            'expense_date'        => ['required', 'date', 'after_or_equal:today - 60 days'],
            'amount'              => ['nullable', 'numeric', 'min:0.01', 'max:500000'],
            'travel_distance_km'  => ['nullable', 'numeric', 'min:0.1', 'max:5000'],
            'travel_from'         => ['nullable', 'string', 'max:150'],
            'travel_to'           => ['nullable', 'string', 'max:150'],
            'rate_per_km'         => ['nullable', 'numeric', 'min:1', 'max:100'],
            'installation_job_id' => ['nullable', 'exists:installation_jobs,id'],
            'service_ticket_id'   => ['nullable', 'exists:service_tickets,id'],
            'description'         => ['required', 'string', 'min:4', 'max:1000'],
            'receipt'             => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $amount = !empty($validated['amount']) ? (float) $validated['amount'] : 0.0;

        // Smart Fuel KM Auto-Calculation
        if ($validated['expense_category'] === 'fuel_travel') {
            $km = (float) ($validated['travel_distance_km'] ?? 0);
            $rate = (float) ($validated['rate_per_km'] ?? 6.0); // Default ₹6.00/km

            if ($amount <= 0 && $km > 0) {
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
            'user_id'             => Auth::id(),
            'installation_job_id' => $validated['installation_job_id'] ?? null,
            'service_ticket_id'   => $validated['service_ticket_id'] ?? null,
            'expense_category'    => $validated['expense_category'],
            'expense_date'        => $validated['expense_date'],
            'amount'              => $amount,
            'travel_distance_km'  => $validated['travel_distance_km'] ?? null,
            'travel_from'         => $validated['travel_from'] ?? null,
            'travel_to'           => $validated['travel_to'] ?? null,
            'rate_per_km'         => $validated['rate_per_km'] ?? null,
            'description'         => $validated['description'],
            'receipt_path'        => $receiptPath,
            'status'              => 'pending',
        ]);

        // Audit notification log to admin
        try {
            NotificationLog::create([
                'channel'        => 'system',
                'event_type'     => 'expense_claim_submitted',
                'recipient_type' => 'admin',
                'recipient_name' => 'Finance & Accounts',
                'subject'        => "New Expense Claim #{$claim->claim_no} from " . Auth::user()->name,
                'message_body'   => Auth::user()->name . " filed a claim for ₹" . number_format($amount, 2) . " ({$claim->category_label}).",
                'action_url'     => route('finance.expenses.index', ['tab' => 'pending']),
                'status'         => 'sent',
                'reference_type' => get_class($claim),
                'reference_id'   => $claim->id,
                'sent_at'        => now(),
                'created_by'     => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            // Non-blocking log
        }

        return redirect()
            ->route('finance.expenses.index', ['tab' => 'my_claims'])
            ->with('status', "Expense claim #{$claim->claim_no} for ₹" . number_format($amount, 2) . " submitted successfully and is under review.");
    }

    /**
     * Admin 1-Click Approve Expense Claim.
     */
    public function approve(ExpenseClaim $claim): RedirectResponse
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized. Only Administrators can approve expense claims.');
        }

        if ($claim->status !== 'pending') {
            return back()->with('error', 'This claim has already been processed.');
        }

        $claim->update([
            'status'      => 'approved',
            'actioned_by' => Auth::id(),
            'actioned_at' => now(),
        ]);

        // Audit notification to employee
        try {
            NotificationLog::create([
                'channel'         => 'system',
                'event_type'      => 'expense_claim_approved',
                'recipient_type'  => 'employee',
                'recipient_name'  => $claim->user?->name ?? 'Employee',
                'recipient_email' => $claim->user?->email,
                'subject'         => "Expense Claim Approved #{$claim->claim_no}",
                'message_body'    => "Your claim of ₹" . number_format((float) $claim->amount, 2) . " has been approved by " . Auth::user()->name . " and queued for payment disbursal.",
                'action_url'      => route('finance.expenses.index', ['tab' => 'my_claims']),
                'status'          => 'sent',
                'reference_type'  => get_class($claim),
                'reference_id'    => $claim->id,
                'sent_at'         => now(),
                'created_by'      => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            // Non-blocking log
        }

        return back()->with('status', "✅ Claim #{$claim->claim_no} for {$claim->user?->name} (₹" . number_format((float) $claim->amount, 2) . ") has been APPROVED.");
    }

    /**
     * Admin Reject Expense Claim with Reason.
     */
    public function reject(Request $request, ExpenseClaim $claim): RedirectResponse
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized. Only Administrators can reject expense claims.');
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

        // Audit notification to employee
        try {
            NotificationLog::create([
                'channel'         => 'system',
                'event_type'      => 'expense_claim_rejected',
                'recipient_type'  => 'employee',
                'recipient_name'  => $claim->user?->name ?? 'Employee',
                'recipient_email' => $claim->user?->email,
                'subject'         => "Expense Claim Rejected #{$claim->claim_no}",
                'message_body'    => "Your claim #{$claim->claim_no} was rejected. Reason: " . $validated['rejection_reason'],
                'action_url'      => route('finance.expenses.index', ['tab' => 'my_claims']),
                'status'          => 'sent',
                'reference_type'  => get_class($claim),
                'reference_id'    => $claim->id,
                'sent_at'         => now(),
                'created_by'      => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            // Non-blocking log
        }

        return back()->with('status', "Claim #{$claim->claim_no} has been rejected.");
    }

    /**
     * Admin Record Disbursed / Settled Payment for Claim.
     */
    public function markPaid(Request $request, ExpenseClaim $claim): RedirectResponse
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized. Only Administrators can mark claims as paid.');
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

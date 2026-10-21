<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $roleFilter = $request->query('role', 'all');

        $query = User::query();

        if (in_array($roleFilter, ['admin', 'staff', 'technician', 'customer'])) {
            $query->where('role', $roleFilter);
        }

        $users = $query->orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users', 'roleFilter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $leads = \App\Models\Lead::orderBy('customer_name')->get(['id', 'customer_name', 'email', 'phone']);
        return view('admin.users.create', compact('leads'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::in(['admin', 'staff', 'technician'])],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        if ($request->filled('return_to')) {
            return redirect($request->return_to)
                ->with('status', 'Technician account registered successfully.')
                ->with('new_technician_id', $user->id);
        }

        return redirect()->route('admin.users.index')
            ->with('status', 'Account registered successfully.');
    }

    /**
     * Display the specified user profile view.
     */
    public function show(User $user): View
    {
        $user->load(['lead']);

        $stats = [
            'tickets_count'    => 0,
            'jobs_count'       => 0,
            'quotes_count'     => 0,
            'invoices_count'   => 0,
            'open_tickets'     => 0,
            'resolved_tickets' => 0,
        ];
        $recentTickets  = collect();
        $recentJobs     = collect();
        $recentQuotes   = collect();
        $recentInvoices = collect();

        if ($user->role === 'technician') {
            $stats['tickets_count']    = \App\Models\ServiceTicket::where('assigned_technician_id', $user->id)->count();
            $stats['jobs_count']       = \App\Models\InstallationJob::where('assigned_technician_id', $user->id)->count();
            $stats['open_tickets']     = \App\Models\ServiceTicket::where('assigned_technician_id', $user->id)->whereIn('status', ['open','pending','in_progress'])->count();
            $stats['resolved_tickets'] = \App\Models\ServiceTicket::where('assigned_technician_id', $user->id)->where('status', 'resolved')->count();
            
            $recentTickets = \App\Models\ServiceTicket::where('assigned_technician_id', $user->id)->latest()->limit(5)->get();
            $recentJobs    = \App\Models\InstallationJob::where('assigned_technician_id', $user->id)->latest()->limit(5)->get();
        } elseif ($user->role === 'customer') {
            $lead = $user->lead ?? ($user->lead_id ? \App\Models\Lead::find($user->lead_id) : null);
            if ($lead) {
                $stats['tickets_count']  = \App\Models\ServiceTicket::where('lead_id', $lead->id)->count();
                $stats['quotes_count']   = \App\Models\Quotation::where('lead_id', $lead->id)->count();
                $stats['invoices_count'] = \App\Models\Invoice::where('lead_id', $lead->id)->count();
                
                $recentTickets  = \App\Models\ServiceTicket::where('lead_id', $lead->id)->latest()->limit(5)->get();
                $recentQuotes   = \App\Models\Quotation::where('lead_id', $lead->id)->latest()->limit(5)->get();
                $recentInvoices = \App\Models\Invoice::where('lead_id', $lead->id)->latest()->limit(5)->get();
            }
        } elseif ($user->role === 'admin' || $user->role === 'staff') {
            $stats['tickets_count']  = \App\Models\ServiceTicket::count();
            $stats['jobs_count']     = \App\Models\InstallationJob::count();
            $stats['quotes_count']   = \App\Models\Quotation::count();
            $stats['invoices_count'] = \App\Models\Invoice::count();
        }

        return view('admin.users.show', compact('user', 'stats', 'recentTickets', 'recentJobs', 'recentQuotes', 'recentInvoices'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $leads = \App\Models\Lead::orderBy('customer_name')->get(['id', 'customer_name', 'email', 'phone']);
        return view('admin.users.edit', compact('user', 'leads'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::in(['admin', 'staff', 'technician'])],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        $redirectTo = $request->input('return_to', route('admin.users.index'));
        return redirect($redirectTo)
            ->with('status', 'Employee account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $redirectTo = $request->input('return_to', route('admin.users.index'));

        if ($user->id === auth()->id()) {
            return redirect($redirectTo)
                ->withErrors(['delete' => 'You cannot delete your own admin account.']);
        }

        $user->delete();

        return redirect($redirectTo)
            ->with('status', 'Employee account deleted successfully.');
    }
}

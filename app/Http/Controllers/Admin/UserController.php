<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        if (in_array($roleFilter, ['admin', 'staff', 'technician', 'customer', 'worker'])) {
            if ($roleFilter === 'worker') {
                // Ensure all approved workers have associated user records (not sign-up requests)
                $unlinkedWorkers = \App\Models\Worker::approved()
                    ->where(fn ($q) => $q->whereNull('user_id')->orWhereDoesntHave('user'))
                    ->get();
                foreach ($unlinkedWorkers as $w) {
                    $cleanPhone = preg_replace('/[^0-9]/', '', (string) $w->phone);
                    $email = $cleanPhone ? "worker_{$cleanPhone}@cctv-crm.local" : "worker_{$w->id}@cctv-crm.local";
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name'     => $w->name,
                            // Workers sign in with their phone, so no shared password
                            'password' => Hash::make(Str::random(40)),
                            'role'     => 'worker',
                        ]
                    );
                    $w->update(['user_id' => $user->id]);
                }
            }
            $query->where('role', $roleFilter);
        }

        $users = $query->with('worker')->orderBy('name')->paginate(15)->withQueryString();

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
    public function store(Request $request)
    {
        // Provide graceful defaults for quick modal creation
        $rawPassword = $request->input('password') ?: 'password';
        $request->merge([
            'password'              => $rawPassword,
            'password_confirmation' => $request->input('password_confirmation') ?: $rawPassword,
            'role'                  => $request->input('role') ?: 'technician',
        ]);

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'string', Rule::in(['admin', 'staff', 'technician', 'worker'])],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        if ($user->role === 'worker' && !$user->worker) {
            \App\Models\Worker::create([
                'user_id'    => $user->id,
                'name'       => $user->name,
                'phone'      => null,
                'daily_rate' => 750.00,
                'is_active'  => true,
            ]);
        }

        if ($request->expectsJson() || $request->ajax() || $request->isJson()) {
            return response()->json([
                'success' => true,
                'user'    => $user,
                'message' => ucfirst($user->role) . ' account registered successfully.',
            ]);
        }

        if ($request->filled('return_to')) {
            return redirect($request->return_to)
                ->with('status', ucfirst($user->role) . ' account registered successfully.')
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
        $user->load(['lead', 'worker']);

        $stats = [
            'tickets_count'    => 0,
            'jobs_count'       => 0,
            'quotes_count'     => 0,
            'invoices_count'   => 0,
            'open_tickets'     => 0,
            'resolved_tickets' => 0,
            'shifts_count'     => 0,
            'daily_rate'       => 0,
            'total_earned'     => 0,
            'total_paid'       => 0,
            'balance'          => 0,
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
        } elseif ($user->role === 'worker') {
            $worker = $user->worker ?? \App\Models\Worker::where('user_id', $user->id)->first();
            if ($worker) {
                $worker->load(['attendances.project', 'wagePayments.recorder']);
                $stats['shifts_count'] = $worker->attendances()->count();
                $stats['daily_rate']   = (float) $worker->daily_rate;
                $stats['total_earned'] = (float) $worker->getTotalEarned();
                $stats['total_paid']   = (float) $worker->getTotalPaid();
                $stats['balance']      = (float) $worker->getRunningBalance();
            }
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
            'role' => ['required', 'string', Rule::in(['admin', 'staff', 'technician', 'worker'])],
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

        if ($user->role === 'worker' && !$user->worker) {
            \App\Models\Worker::create([
                'user_id'    => $user->id,
                'name'       => $user->name,
                'phone'      => null,
                'daily_rate' => 750.00,
                'is_active'  => true,
            ]);
        }

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

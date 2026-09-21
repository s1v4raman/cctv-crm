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
            'role' => ['required', 'string', Rule::in(['admin', 'staff', 'technician', 'customer'])],
            'lead_id' => ['nullable', 'exists:leads,id'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'lead_id' => $request->role === 'customer' ? $request->lead_id : null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('status', 'Account registered successfully.');
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
            'role' => ['required', 'string', Rule::in(['admin', 'staff', 'technician', 'customer'])],
            'lead_id' => ['nullable', 'exists:leads,id'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'lead_id' => $request->role === 'customer' ? $request->lead_id : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // If customer account, synchronize the CRM Lead email and name
        if ($user->role === 'customer') {
            $lead = $user->lead ?? ($user->lead_id ? \App\Models\Lead::find($user->lead_id) : null);
            if ($lead) {
                $lead->update([
                    'customer_name' => $user->name,
                    'email'         => $user->email,
                ]);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('status', 'Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->withErrors(['delete' => 'You cannot delete your own admin account.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('status', 'Account deleted successfully.');
    }
}

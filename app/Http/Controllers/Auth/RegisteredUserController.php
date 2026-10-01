<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $phone = $request->input('phone') ?: 'Pending update';

        $lead = \App\Models\Lead::where('email', $request->email)->first();
        if (!$lead && $request->filled('phone')) {
            $lead = \App\Models\Lead::where('phone', $request->phone)->first();
        }

        if (!$lead) {
            $lead = \App\Models\Lead::create([
                'customer_name' => $request->name,
                'email'         => $request->email,
                'phone'         => $phone,
                'source'        => 'customer_portal',
                'status'        => 'contacted',
                'notes'         => 'Registered customer account via Customer Client Portal.',
            ]);
        } else {
            if ($lead->phone === 'Pending update' && $request->filled('phone')) {
                $lead->update(['phone' => $request->phone]);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'lead_id' => $lead->id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('portal.dashboard', absolute: false))
            ->with('status', 'Welcome to your Precision IT Systems Customer Portal! You can now track your quotations, service tickets, AMC warranties, and book new site surveys.');
    }
}

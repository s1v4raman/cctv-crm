<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // Store portal type in session — used on logout to redirect correctly.
        // Works for both local port access AND Cloudflare Tunnel (where port is always 443).
        if ($user->isCustomer()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Customer self-service portal login has been retired. Please contact support for assistance.',
            ]);
        }

        // Admin / Technician / Employee — all go to staff portal
        $request->session()->put('portal_type', 'staff');

        if ($user->role === 'technician') {
            return redirect()->intended(route('technician.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $port = (int) $request->getPort();
        if ($port === 8001) {
            $scheme = $request->getScheme();
            $host = $request->getHost();
            return redirect("{$scheme}://{$host}:8001/staff/login");
        }

        return redirect()->route('staff.login');
    }
}

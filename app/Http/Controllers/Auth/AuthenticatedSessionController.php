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
            $request->session()->put('portal_type', 'customer');
            return redirect()->intended(route('portal.dashboard', absolute: false));
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
     *
     * Redirect rules:
     *   - Staff (Admin / Technician / Employee) → /login  (Staff ERP login page)
     *   - Customer → /  (Customer home page)
     *
     * Uses session portal_type stored at login — works via Cloudflare Tunnel too.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Capture user & portal type BEFORE the session is invalidated
        $user       = $request->user();
        $portalType = $request->session()->get('portal_type', 'customer');
        $port       = (int) $request->getPort();

        // Fallback: if user is loaded, derive portal from role directly
        if ($user) {
            $portalType = $user->isCustomer() ? 'customer' : 'staff';
        }

        // Also trust port 8001 as a staff indicator (local dev)
        if ($port === 8001) {
            $portalType = 'staff';
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Staff → Staff ERP login page | Customer → Customer home page
        if ($portalType === 'staff' || $port === 8001) {
            if ($port === 8001) {
                $scheme = $request->getScheme();
                $host = $request->getHost();
                return redirect("{$scheme}://{$host}:8001/login");
            }
            return redirect(route('login'));
        }

        return redirect('/');
    }
}

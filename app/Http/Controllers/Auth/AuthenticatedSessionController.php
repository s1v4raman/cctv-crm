<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnforceLoginPortal;
use App\Http\Middleware\SetLocale;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view: the office's (admin, employee) or the field's (technician, daily worker).
     */
    public function create(Request $request): View
    {
        return view('auth.login', ['portal' => EnforceLoginPortal::forRequest($request)]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $portal = EnforceLoginPortal::forRequest($request);

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

        // An account from the other portal signs in on its own link
        if (!EnforceLoginPortal::allows($portal, $user)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route(EnforceLoginPortal::loginRoute($portal))->withErrors([
                'email' => EnforceLoginPortal::wrongAccountMessage($portal),
            ]);
        }

        // Admin / Technician / Employee — all go to staff portal
        $request->session()->put('portal_type', 'staff');

        if ($user->role === 'technician') {
            // English / Tamil picked on the sign-in page's Technician tab carries over to the technician pages
            $locale = SetLocale::rememberOnSignIn($request, $user);

            return redirect()->intended(route('technician.dashboard', absolute: false))
                ->withCookie(cookie()->forever(SetLocale::COOKIE, $locale));
        }

        if ($user->role === 'worker') {
            return redirect()->intended(route('worker.portal', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $wasWorker = (bool) $request->user()?->isWorker();
        $wasField = $wasWorker || (bool) $request->user()?->isTechnician();

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Technicians and workers land back on their own sign-in link, a worker on the worker tab
        // (in the language this phone picked)
        $loginRoute = $wasField ? EnforceLoginPortal::loginRoute('technician') : 'login';

        return $wasWorker
            ? redirect()->route($loginRoute)->with('login_portal', 'worker')
            : redirect()->route($loginRoute);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * A local server started for one portal (LOGIN_PORTAL=admin or technician, see run-admin-server.bat and
 * run-technician-server.bat) only lets its own accounts in: any other account is signed straight back out,
 * whichever way it signed in (password, Google, worker phone, remember-me). The technician server also
 * takes the daily-wage workers' sign-in; the admin server closes it.
 */
class EnforceLoginPortal
{
    /**
     * The roles each locked server lets in, which are also the sign-in tabs it shows.
     */
    public const ROLES = [
        'admin' => ['admin'],
        'technician' => ['technician', 'worker'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $portal = config('app.login_portal');

        if (!$portal) {
            return $next($request);
        }

        if ($request->routeIs('login.worker') && !in_array('worker', self::ROLES[$portal], true)) {
            abort(404);
        }

        // An account already signed in here (or remembered) from before the server was locked
        if ($this->isWrongAccount($portal)) {
            return $this->signOut($request, $portal);
        }

        $response = $next($request);

        // The request that just signed someone in
        return $this->isWrongAccount($portal) ? $this->signOut($request, $portal) : $response;
    }

    private function isWrongAccount(string $portal): bool
    {
        $user = Auth::guard('web')->user();

        return $user !== null && !in_array($user->role, self::ROLES[$portal], true);
    }

    private function signOut(Request $request, string $portal): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => $portal === 'admin'
                ? __('This server is for admin sign-in only. Technicians and daily workers use the technician server.')
                : __('This server is for technician and daily worker sign-in only. Admins use the admin server.'),
        ]);
    }
}

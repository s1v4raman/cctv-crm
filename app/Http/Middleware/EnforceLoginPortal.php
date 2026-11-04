<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Two sign-in portals: the office (admins and employees) and the field (technicians and daily-wage workers).
 *
 * On one server each portal has its own link: /login for the office and /field/login for the field, and each
 * sign-in page turns the other portal's accounts away. A local server started for one portal (LOGIN_PORTAL=admin
 * or technician, see run-admin-server.bat and run-technician-server.bat) shows only that portal at /login and
 * signs any other account straight back out, whichever way it signed in (password, Google, worker phone,
 * remember-me). The technician server also takes the daily-wage workers' sign-in; the admin server closes it.
 */
class EnforceLoginPortal
{
    /**
     * The roles each portal lets in, which are also the sign-in tabs it shows.
     */
    public const ROLES = [
        'admin' => ['admin', 'staff'],
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

    /**
     * The portal a sign-in page or sign-in request belongs to: the server's own when it is locked,
     * otherwise the field portal on the /field pages and the office portal everywhere else.
     * The field pages do not exist on a server locked to the office.
     */
    public static function forRequest(Request $request): string
    {
        $locked = config('app.login_portal');

        abort_if($locked === 'admin' && $request->routeIs('field.*'), 404);

        return $locked ?? ($request->routeIs('field.*') ? 'technician' : 'admin');
    }

    public static function allows(string $portal, User $user): bool
    {
        return in_array($user->role, self::ROLES[$portal], true);
    }

    /**
     * The sign-in page of a portal. A locked server has only one, at /login.
     */
    public static function loginRoute(string $portal): string
    {
        return $portal === 'technician' && !config('app.login_portal') ? 'field.login' : 'login';
    }

    /**
     * Why an account was turned away from a portal, and where it signs in instead.
     */
    public static function wrongAccountMessage(string $portal): string
    {
        if (config('app.login_portal')) {
            return $portal === 'admin'
                ? __('This server is for admin and employee sign-in only. Technicians and daily workers use the technician server.')
                : __('This server is for technician and daily worker sign-in only. Admins and employees use the admin server.');
        }

        return $portal === 'admin'
            ? __('This sign-in page is for admins and employees. Technicians and daily workers sign in at :url', ['url' => route('field.login')])
            : __('This sign-in page is for technicians and daily workers. Admins and employees sign in at :url', ['url' => route('login')]);
    }

    private function isWrongAccount(string $portal): bool
    {
        $user = Auth::guard('web')->user();

        return $user !== null && !self::allows($portal, $user);
    }

    private function signOut(Request $request, string $portal): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['email' => self::wrongAccountMessage($portal)]);
    }
}

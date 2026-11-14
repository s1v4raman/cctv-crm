<?php

namespace App\Http\Middleware;

use App\Models\ProjectWorkerAttendance;
use App\Models\Worker;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Worker ID photos, punch selfies and site survey photos. Internal users can open all of them,
 * a worker only their own, and anyone with a link from URL::temporarySignedRoute until it expires.
 */
class PrivatePhotoAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasValidSignature()) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user) {
            throw new AuthenticationException();
        }

        if ($user->isInternal()) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}

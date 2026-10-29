<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InternalUserOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Daily-wage workers only have their own portal: send them there instead of a 403
        if ($user?->isWorker() && $request->isMethod('GET') && !$request->expectsJson()) {
            return redirect()->route('worker.portal');
        }

        if (!$user || !$user->isInternal()) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}

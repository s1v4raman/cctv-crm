<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OfficeUserOnly
{
    /**
     * Admin and staff: adding and editing records (see User::isOffice()).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isOffice()) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}

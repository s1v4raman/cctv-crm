<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and attach recommended security headers to response.
     *
     * @param  Request  $request
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // Voice input: the worker sign-in's name on the field sign-in page, and the text boxes on worker and
        // technician pages. Office pages, the office sign-in included, keep the microphone off.
        $signInPortal = config('app.login_portal') ?? ($request->routeIs('field.login') ? 'technician' : 'admin');
        $voiceInput = ($request->routeIs('login', 'staff.login', 'admin.login', 'field.login') && $signInPortal === 'technician')
            || ($request->hasSession() && $request->user()?->canChooseLanguage());
        $microphone = $voiceInput ? '(self)' : '()';
        $response->headers->set('Permissions-Policy', "camera=(self), microphone={$microphone}, geolocation=()");

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}

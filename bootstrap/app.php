<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust Cloudflare Tunnel & all reverse proxies (fixes 419 CSRF error via tunnel)
        $middleware->trustProxies(at: '*');

        // Security response headers (anti-clickjacking, XSS, MIME sniffing)
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->validateCsrfTokens(except: [
            'api/*',
            'webhook/*',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminOnly::class,
            'internal' => \App\Http\Middleware\InternalUserOnly::class,
            'technician' => \App\Http\Middleware\TechnicianMiddleware::class,
            'customer' => \App\Http\Middleware\CustomerAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

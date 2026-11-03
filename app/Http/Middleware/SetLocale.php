<?php

namespace App\Http\Middleware;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Worker and technician pages in English or Tamil: the signed-in user's saved choice,
 * else this phone's cookie (the worker sign-in page, or an account that never picked one).
 * Office and customer accounts always get English, whatever the cookie on the browser says.
 */
class SetLocale
{
    public const COOKIE = 'crm_locale';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = self::preferredLocale($request);

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }

    public static function preferredLocale(Request $request): string
    {
        $user = $request->user();

        if ($user && !$user->canChooseLanguage()) {
            return config('app.fallback_locale');
        }

        $locale = $user?->locale ?: $request->cookie(self::COOKIE);

        return self::isSupported($locale) ? $locale : config('app.fallback_locale');
    }

    /**
     * The language for a worker or technician who has just signed in. A choice made on this phone's
     * sign-in page (the cookie) wins and is saved on the account; without one, the account's saved choice is used.
     */
    public static function rememberOnSignIn(Request $request, User $user): string
    {
        $phoneLocale = $request->cookie(self::COOKIE);

        if (self::isSupported($phoneLocale) && $user->locale !== $phoneLocale) {
            $user->forceFill(['locale' => $phoneLocale])->save();
        }

        $locale = self::isSupported($user->locale) ? $user->locale : config('app.fallback_locale');
        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $locale;
    }

    public static function isSupported(mixed $locale): bool
    {
        return is_string($locale) && array_key_exists($locale, config('app.supported_locales', []));
    }
}

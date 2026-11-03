<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    /**
     * The English / Tamil switch on the worker sign-in, worker portal and technician pages.
     * Kept in a cookie on this phone and, when signed in, on the account, so it survives signing out.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(array_keys(config('app.supported_locales')))],
        ]);

        $user = $request->user();
        if ($user?->canChooseLanguage()) {
            $user->forceFill(['locale' => $validated['locale']])->save();
        }

        $response = back()->withCookie(cookie()->forever(SetLocale::COOKIE, $validated['locale']));

        // Switching on the sign-in page's Daily Worker or Technician tab should land back on that tab
        if (in_array($request->input('portal'), ['worker', 'technician'], true)) {
            $response->with('login_portal', $request->input('portal'));
        }

        return $response;
    }
}

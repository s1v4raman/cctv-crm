<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google OAuth consent screen.
     */
    public function redirectToGoogle(Request $request): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            $isStaff = $request->input('portal') === 'staff' || $request->is('staff/*');
            $redirectRoute = $isStaff ? 'staff.login' : 'login';

            return redirect()->route($redirectRoute)->withErrors([
                'email' => 'Google Client ID & Secret are not yet configured in your .env file. Please add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET to enable Google sign in.',
            ]);
        }

        // Store portal intent and auth mode (login vs register) in session
        $portal = $request->input('portal', 'customer');
        $mode   = $request->input('mode', 'login');

        session([
            'oauth_portal' => $portal,
            'oauth_mode'   => $mode,
        ]);

        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Handle the callback returned from Google OAuth.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $portalIntent = session('oauth_portal', 'customer');
        $mode         = session('oauth_mode', 'login');
        $fallbackRoute = ($portalIntent === 'staff') ? 'staff.login' : ($mode === 'register' ? 'register' : 'login');

        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route($fallbackRoute)->withErrors([
                'email' => 'Google sign-in was cancelled or failed: ' . $e->getMessage(),
            ]);
        }

        if (empty($googleUser->getEmail())) {
            return redirect()->route($fallbackRoute)->withErrors([
                'email' => 'No email address was provided by your Google account.',
            ]);
        }

        // 1. Try finding user by google_id
        $user = User::where('google_id', $googleUser->getId())->first();

        // 2. If not found by google_id, check by email
        if (!$user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Link Google account to existing user
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar() ?? $user->avatar,
                ]);
            }
        }

        // 3. User does not exist — self-registration is disabled for ERP security
        if (!$user) {
            return redirect()->route('login')->withErrors([
                'email' => 'No internal staff account found with Google email (' . $googleUser->getEmail() . '). Please contact your system administrator to provision your ERP account.',
            ]);
        }

        // Customer accounts are not allowed to access the internal ERP
        if ($user->isCustomer()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Customer self-service portal login has been retired. Please contact support for assistance.',
            ]);
        }

        // Update avatar if available
        if ($googleUser->getAvatar() && $user->avatar !== $googleUser->getAvatar()) {
            $user->update(['avatar' => $googleUser->getAvatar()]);
        }

        // Log the user in
        Auth::login($user, remember: true);
        $request->session()->regenerate();
        $request->session()->put('portal_type', 'staff');

        if ($user->role === 'technician') {
            return redirect()->intended(route('technician.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }
}

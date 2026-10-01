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

        // 3. User does not exist
        if (!$user) {
            // In LOGIN mode: Only allow existing registered accounts to log in
            if ($mode === 'login') {
                $loginRoute = ($portalIntent === 'staff') ? 'staff.login' : 'login';
                return redirect()->route($loginRoute)->withErrors([
                    'email' => 'No registered account found with Google email (' . $googleUser->getEmail() . '). Please sign up first to create your customer account.',
                ]);
            }

            // In REGISTER mode: Create new customer account
            $user = User::create([
                'name'              => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Customer',
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'role'              => 'customer',
                'password'          => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
            ]);

            // Auto-provision Customer Lead profile
            $user->getCustomerLead();
        } else {
            // Update avatar if available
            if ($googleUser->getAvatar() && $user->avatar !== $googleUser->getAvatar()) {
                $user->update(['avatar' => $googleUser->getAvatar()]);
            }
        }

        // Log the user in
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        // Routing based on user role
        if ($user->isCustomer()) {
            $request->session()->put('portal_type', 'customer');
            return redirect()->intended(route('portal.dashboard'));
        }

        $request->session()->put('portal_type', 'staff');

        if ($user->role === 'technician') {
            return redirect()->intended(route('technician.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }
}

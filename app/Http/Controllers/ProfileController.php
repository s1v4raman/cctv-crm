<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Synchronize customer profile with CRM Lead record
        if ($user->isCustomer() || $user->lead_id) {
            $lead = $user->getCustomerLead();
            if ($lead) {
                $lead->update([
                    'customer_name' => $user->name,
                    'email'         => $user->email,
                ]);
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update the user's personal theme preferences (per-user theme customization).
     */
    public function updateTheme(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'accent'     => ['nullable', 'string', 'max:20'],
            'mode'       => ['nullable', 'string', 'max:20'],
            'themeStyle' => ['nullable', 'string', 'max:20'],
        ]);

        $user = $request->user();
        if (isset($validated['accent'])) {
            $user->theme_accent = $validated['accent'];
        }
        if (isset($validated['mode'])) {
            $user->theme_mode = $validated['mode'];
        }
        if (isset($validated['themeStyle'])) {
            $user->theme_style = $validated['themeStyle'];
        }
        $user->save();

        return response()->json([
            'success'      => true,
            'theme_accent' => $user->theme_accent,
            'theme_mode'   => $user->theme_mode,
            'theme_style'  => $user->theme_style,
        ]);
    }
}

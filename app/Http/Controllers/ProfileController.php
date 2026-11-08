<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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
        $validated = $request->validated();

        $user->fill([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Handle Avatar Removal
        if ($request->boolean('remove_avatar')) {
            if ($user->avatar && !str_starts_with($user->avatar, 'data:') && !filter_var($user->avatar, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        }

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // Delete existing physical file if one was stored
            if ($user->avatar && !str_starts_with($user->avatar, 'data:') && !filter_var($user->avatar, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Optimize & crop avatar to a crisp 256x256 square to keep storage ultra-lightweight (~15-25KB)
            $optimizedDataUri = null;
            try {
                $imageContent = file_get_contents($file->getRealPath());
                if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
                    $src = @imagecreatefromstring($imageContent);
                    if ($src !== false) {
                        $width = imagesx($src);
                        $height = imagesy($src);
                        $maxDim = 256;

                        // Center square crop coordinates
                        $minEdge = min($width, $height);
                        $srcX = (int) (($width - $minEdge) / 2);
                        $srcY = (int) (($height - $minEdge) / 2);

                        $dst = imagecreatetruecolor($maxDim, $maxDim);
                        imagealphablending($dst, false);
                        imagesavealpha($dst, true);

                        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $maxDim, $maxDim, $minEdge, $minEdge);

                        ob_start();
                        imagejpeg($dst, null, 85);
                        $compressedData = ob_get_clean();

                        imagedestroy($src);
                        imagedestroy($dst);

                        if (!empty($compressedData)) {
                            $optimizedDataUri = 'data:image/jpeg;base64,' . base64_encode($compressedData);
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Avatar GD optimization error: ' . $e->getMessage());
            }

            // Store persistent Base64 Data URI in DB so it never vanishes when ephemeral cloud containers sleep or restart
            if ($optimizedDataUri) {
                $user->avatar = $optimizedDataUri;
            } else {
                $mime = $file->getMimeType() ?: 'image/jpeg';
                $user->avatar = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
            }

            // Also maintain local disk file for filesystem compatibility
            try {
                $filename = 'avatar_' . $user->id . '_' . time() . '.jpg';
                $path = 'avatars/' . $filename;
                if ($optimizedDataUri) {
                    $binary = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $optimizedDataUri));
                    Storage::disk('public')->put($path, $binary);
                } else {
                    $file->storeAs('avatars', $filename, 'public');
                }
            } catch (\Throwable $e) {
                // Ignore storage disk failure on read-only/ephemeral filesystems
            }
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
        if ($user) {
            if (isset($validated['accent'])) {
                $user->theme_accent = $validated['accent'];
            }
            if (isset($validated['mode'])) {
                $m = $validated['mode'];
                if ($m === 'light') $m = 'day';
                if ($m === 'dark') $m = 'night';
                $user->theme_mode = $m;
            }
            if (isset($validated['themeStyle'])) {
                $user->theme_style = $validated['themeStyle'];
            }
            $user->save();
        }

        $effectiveMode = $user?->theme_mode ?? ($validated['mode'] ?? 'night');
        if ($effectiveMode === 'light') $effectiveMode = 'day';
        if ($effectiveMode === 'dark') $effectiveMode = 'night';

        // Keep session in sync with user's theme mode
        session(['crm_mode' => $effectiveMode]);

        return response()->json([
            'success'      => true,
            'theme_accent' => $user?->theme_accent,
            'theme_mode'   => $user?->theme_mode ?? $effectiveMode,
            'theme_style'  => $user?->theme_style,
        ])->withCookie(cookie('crm_mode', $effectiveMode, 525600, '/', null, false, false, false, 'Lax'));
    }

    /**
     * Fallback route to stream user avatars from storage link or reconstruct from DB.
     */
    public function serveStorageAvatar(string $filename)
    {
        $path = 'avatars/' . $filename;
        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path), [
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        // Search user by avatar filename
        $user = \App\Models\User::where('avatar', 'LIKE', '%' . $filename)->first();
        if ($user && !empty($user->avatar) && str_starts_with($user->avatar, 'data:image/')) {
            $data = $user->avatar;
            $mime = 'image/jpeg';
            if (preg_match('/^data:([^;]+);base64,/', $data, $m)) {
                $mime = $m[1];
            }
            $data = preg_replace('/^data:[^;]+;base64,/', '', $data);
            $binary = base64_decode(str_replace(' ', '+', $data));

            if ($binary) {
                try {
                    Storage::disk('public')->put($path, $binary);
                } catch (\Throwable $e) {}

                return response($binary, 200, [
                    'Content-Type'  => $mime,
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                ]);
            }
        }

        abort(404);
    }
}

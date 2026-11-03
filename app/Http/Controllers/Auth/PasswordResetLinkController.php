<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($request->input('email')))]);
        }

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // The log and array mailers only write the email to a file. On the server that means
        // nobody receives it, so say so instead of "We have emailed your password reset link".
        if (in_array(config('mail.default'), ['log', 'array'], true) && ! app()->environment(['local', 'testing'])) {
            Log::warning('Password reset link requested, but MAIL_MAILER is "' . config('mail.default') . '", so no email was sent.');

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('Password reset emails are not set up on this server yet. Ask your administrator to set a new password for you.')]);
        }

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (TransportExceptionInterface $e) {
            report($e);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('The reset email could not be sent. Try again in a few minutes, or ask your administrator to set a new password for you.')]);
        }

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}

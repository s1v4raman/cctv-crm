<?php

namespace App\Http\Middleware;

use App\Models\ProjectWorkerAttendance;
use App\Models\Worker;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Worker ID photos, punch selfies and site survey photos. Internal users can open all of them,
 * a worker only their own, and anyone with a link from URL::temporarySignedRoute until it expires.
 */
class PrivatePhotoAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasValidSignature()) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user) {
            throw new AuthenticationException();
        }

        if ($user->isInternal()) {
            return $next($request);
        }

        if ($user->isWorker() && $user->worker && $this->photoWorkerId($request) === $user->worker->id) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }

    /**
     * The worker a requested ID photo or punch selfie belongs to (null for other photos).
     */
    private function photoWorkerId(Request $request): ?int
    {
        $attendance = $request->route('attendance');
        if ($attendance instanceof ProjectWorkerAttendance) {
            return (int) $attendance->worker_id;
        }

        $worker = $request->route('worker');
        if ($worker instanceof Worker) {
            return (int) $worker->id;
        }

        return null;
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\HeaderNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark every topbar notification up to now as read for the signed-in user.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->forceFill(['notifications_read_at' => now()])->save();
        HeaderNotificationService::clearCacheForUser($user->id);

        return response()->json([
            'unread_count' => HeaderNotificationService::getNotificationsForUser($user)['unread_count'],
        ]);
    }
}

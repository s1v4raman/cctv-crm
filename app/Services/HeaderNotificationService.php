<?php

namespace App\Services;

use App\Models\InstallationJob;
use App\Models\NotificationLog;
use App\Models\SiteSurvey;
use App\Models\User;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HeaderNotificationService
{
    /**
     * Retrieve aggregated, role-aware topbar notifications for the authenticated user.
     *
     * @param User $user
     * @return array{unread_count: int, items: array<int, array<string, mixed>>}
     */
    public static function getNotificationsForUser(?User $user): array
    {
        if (!$user) {
            return ['unread_count' => 0, 'items' => []];
        }

        return Cache::remember("topbar_notifs_user_{$user->id}", 60, function () use ($user) {
            $items = collect();

        // 1. Fetch relevant NotificationLogs (e.g. dispatched WhatsApp, SMS, Email logs)
        if ($user->isAdmin() || $user->isStaff()) {
            $logs = NotificationLog::latest()->take(4)->get();
        } else {
            $logs = NotificationLog::where(function ($q) use ($user) {
                if ($user->email) {
                    $q->where('recipient_email', $user->email);
                }
            })->latest()->take(4)->get();
        }

        foreach ($logs as $log) {
            $statusLabel = ucfirst($log->status ?? 'sent');
            $channelLabel = strtoupper($log->channel ?? 'ALERT');
            $items->push([
                'id'          => 'log-' . $log->id,
                'title'       => "[{$channelLabel}] " . ($log->subject ?: 'Notification Dispatched'),
                'description' => $log->recipient_name ? "To {$log->recipient_name} • {$statusLabel}" : ($log->message_body ? \Illuminate\Support\Str::limit($log->message_body, 55) : 'Dispatched successfully'),
                'time'        => $log->created_at ? $log->created_at->diffForHumans(null, true) . ' ago' : 'Recently',
                'timestamp'   => $log->created_at ? $log->created_at->timestamp : time(),
                'icon'        => 'log',
                'activity_at' => $log->created_at?->timestamp,
                'url'         => route('alerts.index'),
                'is_unread'   => $log->created_at && $log->created_at->isToday(),
            ]);
        }

        // Worker sign-up requests waiting for an admin to approve them
        if ($user->isAdmin()) {
            foreach (Worker::pendingApproval()->latest()->take(3)->get() as $request) {
                $items->push([
                    'id'          => 'worker-request-' . $request->id,
                    'title'       => "Worker sign-up: {$request->name}",
                    'description' => "{$request->phone} is waiting for your approval",
                    'time'        => $request->created_at->diffForHumans(null, true) . ' ago',
                    'timestamp'   => $request->created_at->timestamp,
                    'icon'        => 'info',
                    'url'         => route('workers.index') . '#signup-requests',
                    'activity_at' => $request->created_at->timestamp,
                    'is_unread'   => true,
                ]);
            }
        }

        // 2. Role-specific operational actionable items
        if ($user->isAdmin() || $user->isStaff()) {
            // Pending Field Installation Jobs
            $pendingJobs = InstallationJob::whereIn('status', ['pending', 'scheduled'])
                ->latest()
                ->take(3)
                ->get();

            foreach ($pendingJobs as $job) {
                $items->push([
                    'id'          => 'job-' . $job->id,
                    'title'       => "Field Job Scheduled: {$job->job_no}",
                    'description' => "Scheduled: " . ($job->scheduled_date ? Carbon::parse($job->scheduled_date)->format('M d, Y') : 'Pending date'),
                    'time'        => $job->created_at ? $job->created_at->diffForHumans(null, true) . ' ago' : 'Recently',
                    'timestamp'   => $job->created_at ? $job->created_at->timestamp : time(),
                    'icon'        => 'job',
                    'url'         => route('jobs.show', $job->id),
                    'activity_at' => ($job->updated_at ?? $job->created_at)?->timestamp,
                    'is_unread'   => true,
                ]);
            }

            // Pending Site Surveys
            $pendingSurveys = SiteSurvey::where('status', 'pending')
                ->latest()
                ->take(3)
                ->get();

            foreach ($pendingSurveys as $survey) {
                $items->push([
                    'id'          => 'survey-' . $survey->id,
                    'title'       => "Pending Site Survey",
                    'description' => "Date: " . ($survey->survey_date ? Carbon::parse($survey->survey_date)->format('M d, Y') : 'Pending'),
                    'time'        => 'Survey Alert',
                    'timestamp'   => time(),
                    'icon'        => 'survey',
                    'url'         => route('site-surveys.show', $survey->id),
                    'activity_at' => ($survey->updated_at ?? $survey->created_at)?->timestamp,
                    'is_unread'   => true,
                ]);
            }
        } elseif ($user->isTechnician()) {
            // Technician assigned jobs
            $jobs = InstallationJob::where('assigned_technician_id', $user->id)
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->latest()
                ->take(3)
                ->get();

            foreach ($jobs as $job) {
                $items->push([
                    'id'          => 'job-' . $job->id,
                    'title'       => "Assigned Field Work Order",
                    'description' => "Scheduled: " . ($job->scheduled_date ? Carbon::parse($job->scheduled_date)->format('M d, Y') : 'Pending date'),
                    'time'        => 'Work Order',
                    'timestamp'   => time(),
                    'icon'        => 'job',
                    'url'         => route('technician.dashboard'),
                    'activity_at' => ($job->updated_at ?? $job->created_at)?->timestamp,
                    'is_unread'   => true,
                ]);
            }
        }

        // 3. System heartbeat fallback if empty
        if ($items->isEmpty()) {
            $items->push([
                'id'          => 'system-online',
                'title'       => 'Precision IT Suite Active',
                'description' => 'CCTV CRM operational telemetry and messaging gateways connected.',
                'time'        => 'Now',
                'timestamp'   => time(),
                'icon'        => 'info',
                'url'         => route('dashboard'),
                'is_unread'   => false,
            ]);
        }

        // Anything that has not changed since the user last pressed "Mark all as read" counts as read
        $readAt = $user->notifications_read_at?->timestamp;
        if ($readAt !== null) {
            $now = now()->timestamp;
            $items = $items->map(function (array $item) use ($readAt, $now) {
                // future-dated records (bad imports / demo data) cannot be newer than the read time
                $activity = $item['activity_at'] ?? 0;
                $item['is_unread'] = $item['is_unread'] && $activity > $readAt && $activity <= $now;
                return $item;
            });
        }

        // Sort by timestamp desc and limit to 6 items
        $sortedItems = $items->sortByDesc('timestamp')->take(6)->values()->all();
        $unreadCount = collect($sortedItems)->where('is_unread', true)->count();

        return [
            'unread_count' => $unreadCount,
            'items'        => $sortedItems,
        ];
        });
    }

    /**
     * Clear the cached notifications for a specific user.
     */
    public static function clearCacheForUser(int|string $userId): void
    {
        Cache::forget("topbar_notifs_user_{$userId}");
    }
}

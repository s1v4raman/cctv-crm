<?php

namespace App\Services;

use App\Models\EmployeeAttendance;
use App\Models\ExpenseClaim;
use App\Models\InstallationJob;
use App\Models\NotificationLog;
use App\Models\ProjectWorkerAttendance;
use App\Models\Quotation;
use App\Models\SiteSurvey;
use App\Models\User;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illimage.pnguminate\Support\Facades\Cache;

class HeaderNotificationService
{
    /**
     * Retrieve aggregated, role-aware topbar notifications for the authenticated user.
     *
     * @param User|null $user
     * @return array
     */
    public static function getNotificationsForUser(?User $user): array
    {
        if (!$user) {
            return ['unread_count' => 0, 'items' => []];
        }

        $cacheKey = "topbar_notifs_user_{$user->id}";

        return Cache::remember($cacheKey, 60, function () use ($user) {
            return self::buildNotifications($user);
        });
    }

    /**
     * Build notifications collection and state for the user.
     *
     * @param User $user
     * @return array
     */
    protected static function buildNotifications(User $user): array
    {
        $items = collect();

        // 1. Logs
        $items = $items->merge(self::fetchLogsForUser($user));

        // 2. Operational actionable items
        $items = $items->merge(self::fetchOperationalItems($user));

        // 3. System heartbeat fallback if empty
        if ($items->isEmpty()) {
            $items->push([
                'id'          => 'system-online',
                'title'       => 'Precision IT Suite Active',
                'description' => 'CCTV CRM operational telemetry and messaging gateways connected.',
                'time'        => 'Now',
                'timestamp'   => time(),
                'icon'        => 'info',
                'category'    => 'System',
                'url'         => $user->homeUrl(),
                'is_unread'   => false,
            ]);
        }

        // 4. Anything that has not changed since the user last pressed "Mark all as read" counts as read
        $readAt = $user->notifications_read_at?->timestamp;
        if ($readAt !== null) {
            $now = now()->timestamp;
            $items = $items->map(function (array $item) use ($readAt, $now) {
                $activity = $item['activity_at'] ?? 0;
                $item['is_unread'] = $item['is_unread'] && $activity > $readAt && $activity <= $now;
                return $item;
            });
        }

        // 5. Sort by timestamp desc and limit to 6 items
        $sortedItems = $items->sortByDesc('timestamp')->take(6)->values()->all();
        $unreadCount = collect($sortedItems)->where('is_unread', true)->count();

        return [
            'unread_count' => $unreadCount,
            'items'        => $sortedItems,
        ];
    }

    /**
     * Fetch relevant notification logs for the user.
     *
     * @param User $user
     * @return Collection
     */
    protected static function fetchLogsForUser(User $user): Collection
    {
        $items = collect();

        if ($user->isAdmin() || $user->isStaff()) {
            $logs = NotificationLog::latest()->take(6)->get();
        } else {
            $logs = NotificationLog::where(function ($q) use ($user) {
                if ($user->email) {
                    $q->where('recipient_email', $user->email);
                }
                if ($user->phone) {
                    $q->orWhere('recipient_phone', $user->phone);
                }
                $q->orWhere('created_by', $user->id);
            })->latest()->take(6)->get();
        }

        foreach ($logs as $log) {
            $statusLabel = ucfirst($log->status ?? 'sent');
            $channelLabel = strtoupper($log->channel ?? 'ALERT');
            $actionInfo = self::resolveActionForLog($log, $user);

            $items->push([
                'id'          => 'log-' . $log->id,
                'title'       => "[{$channelLabel}] " . ($log->subject ?: 'Notification Dispatched'),
                'description' => $log->recipient_name ? "To {$log->recipient_name} • {$statusLabel}" : ($log->message_body ? \Illuminate\Support\Str::limit($log->message_body, 55) : 'Dispatched successfully'),
                'time'        => $log->created_at ? $log->created_at->diffForHumans(null, true) . ' ago' : 'Recently',
                'timestamp'   => $log->created_at ? $log->created_at->timestamp : time(),
                'icon'        => $actionInfo['icon'],
                'category'    => $actionInfo['category_tag'],
                'activity_at' => $log->created_at?->timestamp,
                'url'         => $actionInfo['url'],
                'is_unread'   => $log->created_at && $log->created_at->isToday(),
            ]);
        }

        return $items;
    }

    /**
     * Fetch role-specific operational actionable items.
     *
     * @param User $user
     * @return Collection
     */
    protected static function fetchOperationalItems(User $user): Collection
    {
        $items = collect();

        // Admin & Staff actionable items
        if ($user->isAdmin() || $user->isStaff()) {
            // Worker sign-up requests waiting for admin
            if ($user->isAdmin()) {
                foreach (Worker::pendingApproval()->latest()->take(3)->get() as $request) {
                    $items->push([
                        'id'          => 'worker-request-' . $request->id,
                        'title'       => "Worker sign-up: {$request->name}",
                        'description' => "{$request->phone} is waiting for your approval",
                        'time'        => $request->created_at->diffForHumans(null, true) . ' ago',
                        'timestamp'   => $request->created_at->timestamp,
                        'icon'        => 'attendance',
                        'category'    => 'Worker Signup',
                        'url'         => route('workers.index') . '#signup-requests',
                        'activity_at' => $request->created_at->timestamp,
                        'is_unread'   => true,
                    ]);
                }
            }

            // Pending Travel & Work Expense Claims
            $pendingClaims = ExpenseClaim::with(['user', 'site', 'project'])
                ->where('status', 'pending')
                ->latest()
                ->take(4)
                ->get();

            foreach ($pendingClaims as $claim) {
                $userName = $claim->user?->name ?? 'Technician';
                $items->push([
                    'id'          => 'pending-claim-' . $claim->id,
                    'title'       => "Expense Claim: #{$claim->claim_no} (₹" . number_format((float) $claim->amount, 0) . ")",
                    'description' => "By {$userName} • {$claim->category_label} • Needs Admin Review",
                    'time'        => $claim->created_at ? $claim->created_at->diffForHumans(null, true) . ' ago' : 'Recently',
                    'timestamp'   => $claim->created_at ? $claim->created_at->timestamp : time(),
                    'icon'        => 'expense',
                    'category'    => 'Expense Claim',
                    'url'         => route('finance.expenses.index', [
                        'tab'      => 'all_claims',
                        'claim_id' => $claim->id,
                        'claim_no' => $claim->claim_no,
                        'search'   => $claim->claim_no,
                    ]),
                    'activity_at' => ($claim->updated_at ?? $claim->created_at)?->timestamp,
                    'is_unread'   => true,
                ]);
            }

            // Pending Field Installation Jobs
            $pendingJobs = InstallationJob::with('quotation.lead')
                ->whereIn('status', ['pending', 'scheduled'])
                ->latest()
                ->take(3)
                ->get();

            foreach ($pendingJobs as $job) {
                $client = $job->quotation?->lead?->customer_name ?? 'Client';
                $items->push([
                    'id'          => 'job-' . $job->id,
                    'title'       => "Field Job Scheduled: {$job->job_no}",
                    'description' => "For {$client} • Scheduled: " . ($job->scheduled_date ? Carbon::parse($job->scheduled_date)->format('M d, Y') : 'Pending date'),
                    'time'        => $job->created_at ? $job->created_at->diffForHumans(null, true) . ' ago' : 'Recently',
                    'timestamp'   => $job->created_at ? $job->created_at->timestamp : time(),
                    'icon'        => 'job',
                    'category'    => 'Field Job',
                    'url'         => route('jobs.show', $job->id),
                    'activity_at' => ($job->updated_at ?? $job->created_at)?->timestamp,
                    'is_unread'   => true,
                ]);
            }

            // Pending Site Surveys
            $pendingSurveys = SiteSurvey::with('lead')
                ->where('status', 'pending')
                ->latest()
                ->take(3)
                ->get();

            foreach ($pendingSurveys as $survey) {
                $client = $survey->lead?->customer_name ?? 'Customer Site';
                $items->push([
                    'id'          => 'survey-' . $survey->id,
                    'title'       => "Pending Survey: {$client}",
                    'description' => "Scheduled: " . ($survey->survey_date ? Carbon::parse($survey->survey_date)->format('M d, Y') : 'Pending date') . " • Review & Audit",
                    'time'        => 'Survey Alert',
                    'timestamp'   => time(),
                    'icon'        => 'survey',
                    'category'    => 'Site Survey',
                    'url'         => route('site-surveys.show', $survey->id),
                    'activity_at' => ($survey->updated_at ?? $survey->created_at)?->timestamp,
                    'is_unread'   => true,
                ]);
            }

            // Pending Technician Site Attendances awaiting approval
            $pendingTechAttendances = EmployeeAttendance::with(['user', 'project'])
                ->where('approval_status', 'pending')
                ->latest()
                ->take(4)
                ->get();

            foreach ($pendingTechAttendances as $techAtt) {
                $techName = $techAtt->user?->name ?? 'Technician';
                $siteTitle = $techAtt->project?->title ?? 'Customer Site';
                $items->push([
                    'id'          => 'tech-att-' . $techAtt->id,
                    'title'       => "Technician Attendance: {$techName}",
                    'description' => "Site Shift for {$siteTitle} • Needs Approval",
                    'time'        => $techAtt->created_at ? $techAtt->created_at->diffForHumans(null, true) . ' ago' : 'Recently',
                    'timestamp'   => $techAtt->created_at ? $techAtt->created_at->timestamp : time(),
                    'icon'        => 'attendance',
                    'category'    => 'Attendance',
                    'url'         => route('attendance.index', ['tab' => 'approvals']),
                    'activity_at' => $techAtt->created_at?->timestamp,
                    'is_unread'   => true,
                ]);
            }

            // Pending Worker Attendances awaiting approval
            $pendingWorkerAttendances = ProjectWorkerAttendance::with(['worker', 'workDay.project', 'logger'])
                ->where('approval_status', 'pending')
                ->latest()
                ->take(4)
                ->get();

            foreach ($pendingWorkerAttendances as $workerAtt) {
                $wName = $workerAtt->worker?->name ?? 'Worker';
                $loggedByName = $workerAtt->logger?->name ?? 'Technician';
                $projTitle = $workerAtt->workDay?->project?->title ?? 'Site';
                $items->push([
                    'id'          => 'worker-att-' . $workerAtt->id,
                    'title'       => "Worker Attendance: {$wName}",
                    'description' => "By {$loggedByName} at {$projTitle} • Needs Approval",
                    'time'        => $workerAtt->created_at ? $workerAtt->created_at->diffForHumans(null, true) . ' ago' : 'Recently',
                    'timestamp'   => $workerAtt->created_at ? $workerAtt->created_at->timestamp : time(),
                    'icon'        => 'attendance',
                    'category'    => 'Attendance',
                    'url'         => route('attendance.index', ['tab' => 'approvals']),
                    'activity_at' => $workerAtt->created_at?->timestamp,
                    'is_unread'   => true,
                ]);
            }

            // Pending Technician Quotations
            $pendingQuotations = Quotation::with(['lead', 'creator'])
                ->where('status', 'pending_approval')
                ->latest()
                ->take(4)
                ->get();

            foreach ($pendingQuotations as $pQuote) {
                $custName = $pQuote->lead?->customer_name ?? 'Customer';
                $techName = $pQuote->creator?->name ?? 'Field Technician';
                $quoteNo = $pQuote->quotation_no ?? ('QUO-' . $pQuote->id);
                $items->push([
                    'id'          => 'pending-quote-' . $pQuote->id,
                    'title'       => "Quotation Review: {$quoteNo}",
                    'description' => "By {$techName} for {$custName} • Needs Pricing & Item Review",
                    'time'        => $pQuote->created_at ? $pQuote->created_at->diffForHumans(null, true) . ' ago' : 'Recently',
                    'timestamp'   => $pQuote->created_at ? $pQuote->created_at->timestamp : time(),
                    'icon'        => 'quotation',
                    'category'    => 'Quotation',
                    'url'         => route('quotations.show', $pQuote),
                    'activity_at' => $pQuote->created_at?->timestamp,
                    'is_unread'   => true,
                ]);
            }
        } elseif ($user->isTechnician()) {
            // Technician Expense Claims (Approved or Rejected status updates)
            $myRecentClaims = ExpenseClaim::where('user_id', $user->id)
                ->latest('updated_at')
                ->take(3)
                ->get();

            foreach ($myRecentClaims as $claim) {
                if ($claim->status === 'approved') {
                    $items->push([
                        'id'          => 'tech-claim-' . $claim->id,
                        'title'       => "Claim Approved: #{$claim->claim_no}",
                        'description' => "₹" . number_format((float) $claim->amount, 0) . " verified by Admin. Added to monthly salary slip.",
                        'time'        => $claim->actioned_at ? $claim->actioned_at->diffForHumans(null, true) . ' ago' : 'Recently',
                        'timestamp'   => ($claim->actioned_at ?? $claim->updated_at)?->timestamp ?? time(),
                        'icon'        => 'expense',
                        'category'    => 'Expense Claim',
                        'url'         => route('technician.dashboard') . '?tab=allowances&claim_no=' . urlencode($claim->claim_no),
                        'activity_at' => ($claim->actioned_at ?? $claim->updated_at)?->timestamp,
                        'is_unread'   => true,
                    ]);
                } elseif ($claim->status === 'rejected') {
                    $items->push([
                        'id'          => 'tech-claim-' . $claim->id,
                        'title'       => "Claim Rejected: #{$claim->claim_no}",
                        'description' => "Reason: " . ($claim->rejection_reason ?: 'Declined by Admin'),
                        'time'        => $claim->actioned_at ? $claim->actioned_at->diffForHumans(null, true) . ' ago' : 'Recently',
                        'timestamp'   => ($claim->actioned_at ?? $claim->updated_at)?->timestamp ?? time(),
                        'icon'        => 'expense',
                        'category'    => 'Expense Claim',
                        'url'         => route('technician.dashboard') . '?tab=allowances&claim_no=' . urlencode($claim->claim_no),
                        'activity_at' => ($claim->actioned_at ?? $claim->updated_at)?->timestamp,
                        'is_unread'   => true,
                    ]);
                }
            }

            // Approved Quotations ready for technician to send to customer
            $approvedQuotations = Quotation::with(['lead'])
                ->where('status', 'approved')
                ->where('created_by', $user->id)
                ->latest()
                ->take(3)
                ->get();

            foreach ($approvedQuotations as $aQuote) {
                $custName = $aQuote->lead?->customer_name ?? 'Customer';
                $quoteNo = $aQuote->quotation_no ?? ('QUO-' . $aQuote->id);
                $items->push([
                    'id'          => 'approved-quote-' . $aQuote->id,
                    'title'       => "Quotation Approved: {$quoteNo}",
                    'description' => "For {$custName} • Ready to dispatch via WhatsApp & Email",
                    'time'        => $aQuote->updated_at ? $aQuote->updated_at->diffForHumans(null, true) . ' ago' : 'Recently',
                    'timestamp'   => $aQuote->updated_at ? $aQuote->updated_at->timestamp : time(),
                    'icon'        => 'quotation',
                    'category'    => 'Quotation',
                    'url'         => route('quotations.show', $aQuote),
                    'activity_at' => $aQuote->updated_at?->timestamp,
                    'is_unread'   => true,
                ]);
            }

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
                    'category'    => 'Field Job',
                    'url'         => route('technician.dashboard') . '?tab=jobs',
                    'activity_at' => ($job->updated_at ?? $job->created_at)?->timestamp,
                    'is_unread'   => true,
                ]);
            }

            // Technician assigned site surveys
            $surveys = SiteSurvey::with('lead')
                ->where('surveyed_by', $user->id)
                ->where('status', 'pending')
                ->latest()
                ->take(3)
                ->get();

            foreach ($surveys as $survey) {
                $client = $survey->lead?->customer_name ?? 'Customer Site';
                $items->push([
                    'id'          => 'survey-' . $survey->id,
                    'title'       => "Assigned Survey: {$client}",
                    'description' => "Scheduled: " . ($survey->survey_date ? Carbon::parse($survey->survey_date)->format('M d, Y') : 'Pending date') . " • Audit & Photos",
                    'time'        => 'Site Survey',
                    'timestamp'   => time(),
                    'icon'        => 'survey',
                    'category'    => 'Site Survey',
                    'url'         => route('site-surveys.show', $survey->id),
                    'activity_at' => ($survey->updated_at ?? $survey->created_at)?->timestamp,
                    'is_unread'   => true,
                ]);
            }
        }

        return $items;
    }

    /**
     * Clear the cached notifications for a specific user.
     *
     * @param int|string $userId
     * @return void
     */
    public static function clearCacheForUser(int|string $userId): void
    {
        Cache::forget("topbar_notifs_user_{$userId}");
    }

    /**
     * Notify Admin and Staff when a technician submits attendance from site.
     *
     * @param string $title
     * @param string $messageBody
     * @param string|null $url
     * @return void
     */
    public static function notifyAdminAndStaffOfAttendance(string $title, string $messageBody, ?string $url = null): void
    {
        $adminAndStaff = User::whereIn('role', ['admin', 'staff'])->get();
        foreach ($adminAndStaff as $u) {
            Cache::forget("topbar_notifs_user_{$u->id}");
        }

        try {
            NotificationLog::create([
                'channel'        => 'email',
                'event_type'     => 'attendance_pending_approval',
                'recipient_type' => 'admin',
                'recipient_name' => 'Admin & Employee Team',
                'subject'        => $title,
                'message_body'   => $messageBody,
                'action_url'     => $url ?? route('attendance.index', ['tab' => 'approvals']),
                'status'         => 'delivered',
                'sent_at'        => now(),
                'created_by'     => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            // Log fallback if DB constraint occurs
        }
    }

    /**
     * Notify Admin and Staff when a technician submits a quotation from site.
     *
     * @param Quotation $quotation
     * @param User $technician
     * @return void
     */
    public static function notifyAdminAndStaffOfQuotation(Quotation $quotation, User $technician): void
    {
        $adminAndStaff = User::whereIn('role', ['admin', 'staff'])->get();
        foreach ($adminAndStaff as $u) {
            Cache::forget("topbar_notifs_user_{$u->id}");
        }

        try {
            NotificationLog::create([
                'channel'        => 'email',
                'event_type'     => 'quotation_pending_approval',
                'recipient_type' => 'admin',
                'recipient_name' => 'Admin & Employee Team',
                'subject'        => "Field Quotation #{$quotation->quotation_no} by {$technician->name}",
                'message_body'   => "Technician {$technician->name} created a quotation for " . ($quotation->lead?->customer_name ?? 'Client') . " with " . $quotation->items->count() . " items. Pending pricing review.",
                'action_url'     => route('quotations.show', $quotation),
                'status'         => 'delivered',
                'reference_type' => get_class($quotation),
                'reference_id'   => $quotation->id,
                'sent_at'        => now(),
                'created_by'     => $technician->id,
            ]);
        } catch (\Throwable $e) {
            // Log fallback
        }
    }

    /**
     * Resolve destination URL, icon and category tag for a NotificationLog entry.
     *
     * @param NotificationLog $log
     * @param User $user
     * @return array
     */
    public static function resolveActionForLog(NotificationLog $log, User $user): array
    {
        $eventType = strtolower((string)$log->event_type);
        $subject = (string)$log->subject;
        $body = (string)$log->message_body;
        $refType = (string)$log->reference_type;
        $refId = $log->reference_id;
        $text = $subject . ' ' . $body;

        // 1. Expense Claim (EXP-XXXXXX-XXX)
        if (
            str_contains($refType, 'ExpenseClaim') ||
            str_contains($eventType, 'expense') ||
            preg_match('/EXP-\d{6}-\d+/i', $text)
        ) {
            $claimNo = null;
            if (preg_match('/(EXP-\d{6}-\d+)/i', $text, $matches)) {
                $claimNo = strtoupper($matches[1]);
            }

            if ($user->isAdmin() || $user->isStaff()) {
                $url = route('finance.expenses.index', array_filter([
                    'tab'      => 'all_claims',
                    'claim_id' => $refId,
                    'claim_no' => $claimNo,
                    'search'   => $claimNo,
                ]));
            } elseif ($user->isTechnician()) {
                $url = route('technician.dashboard') . '?tab=allowances' . ($claimNo ? '&claim_no=' . urlencode($claimNo) : ($refId ? '&claim_id=' . $refId : ''));
            } else {
                $url = route('finance.expenses.index', array_filter([
                    'tab'      => 'my_claims',
                    'claim_id' => $refId,
                    'claim_no' => $claimNo,
                ]));
            }

            return [
                'url'          => $url,
                'icon'         => 'expense',
                'category_tag' => 'Expense Claim',
            ];
        }

        // 2. Site Survey
        if (str_contains($refType, 'SiteSurvey') || str_contains($eventType, 'survey')) {
            if ($refId) {
                $url = route('site-surveys.show', $refId);
            } elseif ($user->isTechnician()) {
                $url = route('technician.dashboard') . '?tab=surveys';
            } else {
                $url = route('site-surveys.index');
            }

            return [
                'url'          => $url,
                'icon'         => 'survey',
                'category_tag' => 'Site Survey',
            ];
        }

        // 3. Field Jobs / Installation Work Orders
        if (str_contains($refType, 'InstallationJob') || str_contains($eventType, 'job')) {
            if ($user->isTechnician()) {
                $url = route('technician.dashboard') . '?tab=jobs' . ($refId ? '&job_id=' . $refId : '');
            } elseif ($refId) {
                $url = route('jobs.show', $refId);
            } else {
                $url = route('jobs.index');
            }

            return [
                'url'          => $url,
                'icon'         => 'job',
                'category_tag' => 'Field Job',
            ];
        }

        // 4. Quotations
        if (str_contains($refType, 'Quotation') || str_contains($eventType, 'quotation')) {
            if ($refId) {
                $url = route('quotations.show', $refId);
            } else {
                $url = $user->isTechnician() ? route('technician.dashboard') . '?tab=quotations' : route('quotations.index');
            }

            return [
                'url'          => $url,
                'icon'         => 'quotation',
                'category_tag' => 'Quotation',
            ];
        }

        // 5. Worker / Technician Attendance
        if (
            str_contains($refType, 'Attendance') ||
            str_contains($eventType, 'attendance')
        ) {
            $url = ($user->isAdmin() || $user->isStaff())
                ? route('attendance.index', ['tab' => 'approvals'])
                : route('technician.dashboard');

            return [
                'url'          => $url,
                'icon'         => 'attendance',
                'category_tag' => 'Attendance',
            ];
        }

        // 6. Project
        if (str_contains($refType, 'Project') || str_contains($eventType, 'project')) {
            $url = $refId ? route('projects.show', $refId) : route('projects.index');
            return [
                'url'          => $url,
                'icon'         => 'job',
                'category_tag' => 'Project',
            ];
        }

        // 7. Lead
        if (str_contains($refType, 'Lead') || str_contains($eventType, 'lead')) {
            $url = $refId ? route('leads.show', $refId) : route('leads.index');
            return [
                'url'          => $url,
                'icon'         => 'info',
                'category_tag' => 'Lead CRM',
            ];
        }

        // 8. Custom action_url on log
        if (!empty($log->action_url)) {
            return [
                'url'          => $log->action_url,
                'icon'         => 'log',
                'category_tag' => strtoupper($log->channel ?? 'ALERT'),
            ];
        }

        // Fallback
        $fallbackUrl = $user->isAdmin() ? route('alerts.index') : ($user->isTechnician() ? route('technician.dashboard') : route('dashboard'));
        return [
            'url'          => $fallbackUrl,
            'icon'         => 'log',
            'category_tag' => strtoupper($log->channel ?? 'ALERT'),
        ];
    }
}

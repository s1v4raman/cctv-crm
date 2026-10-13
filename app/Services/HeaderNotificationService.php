<?php

namespace App\Services;

use App\Models\InstallationJob;
use App\Models\NotificationLog;
use App\Models\Product;
use App\Models\ServiceTicket;
use App\Models\SiteSurvey;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
                'url'         => route('alerts.index'),
                'is_unread'   => $log->created_at && $log->created_at->isToday(),
            ]);
        }

        // 2. Role-specific operational actionable items
        if ($user->isAdmin() || $user->isStaff()) {
            // Urgent / Critical Service Tickets
            $tickets = ServiceTicket::whereIn('status', ['open', 'in_progress'])
                ->whereIn('priority', ['critical', 'high'])
                ->latest()
                ->take(3)
                ->get();

            foreach ($tickets as $ticket) {
                $items->push([
                    'id'          => 'ticket-' . $ticket->id,
                    'title'       => "High Priority Service Alert",
                    'description' => ($ticket->subject ?: 'Service ticket') . " (" . ucfirst($ticket->priority) . ")",
                    'time'        => $ticket->created_at ? $ticket->created_at->diffForHumans(null, true) . ' ago' : 'Recently',
                    'timestamp'   => $ticket->created_at ? $ticket->created_at->timestamp : time(),
                    'icon'        => 'ticket',
                    'url'         => route('service-tickets.show', $ticket->id),
                    'is_unread'   => true,
                ]);
            }

            // Low Stock Inventory Warnings
            $lowStock = Product::where('is_active', true)
                ->whereColumn('stock_quantity', '<=', 'min_stock_alert')
                ->latest()
                ->take(3)
                ->get();

            foreach ($lowStock as $prod) {
                $items->push([
                    'id'          => 'product-' . $prod->id,
                    'title'       => "Low Stock: {$prod->name}",
                    'description' => "Current: {$prod->stock_quantity} {$prod->unit} (Min: {$prod->min_stock_alert})",
                    'time'        => 'Alert',
                    'timestamp'   => time(),
                    'icon'        => 'inventory',
                    'url'         => route('inventory.index', ['status' => 'low_stock']),
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
                    'description' => "Scheduled: " . ($job->scheduled_date ? $job->scheduled_date->format('M d, Y') : 'Pending date'),
                    'time'        => 'Work Order',
                    'timestamp'   => time(),
                    'icon'        => 'job',
                    'url'         => route('technician.dashboard'),
                    'is_unread'   => true,
                ]);
            }
        } elseif ($user->isCustomer() && $user->lead_id) {
            // Customer open service tickets
            $custTickets = ServiceTicket::where('lead_id', $user->lead_id)
                ->whereIn('status', ['open', 'in_progress'])
                ->latest()
                ->take(3)
                ->get();

            foreach ($custTickets as $ticket) {
                $items->push([
                    'id'          => 'cust-ticket-' . $ticket->id,
                    'title'       => "Customer Support Request",
                    'description' => "Status: " . ucfirst(str_replace('_', ' ', $ticket->status)),
                    'time'        => $ticket->created_at ? $ticket->created_at->diffForHumans(null, true) . ' ago' : 'Recently',
                    'timestamp'   => $ticket->created_at ? $ticket->created_at->timestamp : time(),
                    'icon'        => 'ticket',
                    'url'         => route('portal.tickets.show', $ticket->id),
                    'is_unread'   => false,
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

        // Sort by timestamp desc and limit to 6 items
        $sortedItems = $items->sortByDesc('timestamp')->take(6)->values()->all();
        $unreadCount = collect($sortedItems)->where('is_unread', true)->count();

        return [
            'unread_count' => $unreadCount,
            'items'        => $sortedItems,
        ];
    }
}

<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StockAlertService
{
    protected AlertNotificationService $alertService;

    public function __construct(AlertNotificationService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * Check if a product's current stock has fallen below its minimum threshold
     * and trigger WhatsApp / SMS / Email alerts with a 24-hour deduplication safeguard.
     */
    public function checkAndTriggerAlert(Product $product, bool $force = false): array
    {
        // Only trigger if product is active and stock is at or below min_stock_alert
        if (!$product->is_active || $product->stock_quantity > $product->min_stock_alert) {
            return [];
        }

        // Deduplication: check if an alert for this product was already generated in the past 24 hours
        if (!$force) {
            $recentlyAlerted = NotificationLog::where('event_type', 'stock_low_threshold_alert')
                ->where('reference_type', Product::class)
                ->where('reference_id', $product->id)
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->exists();

            if ($recentlyAlerted) {
                return [];
            }
        }

        // Target recipients: Admin & Staff users
        $recipients = User::whereIn('role', ['admin', 'staff'])
            ->whereNotNull('email')
            ->get();

        $variables = [
            'product_name'   => $product->name,
            'sku'            => $product->sku,
            'current_stock'  => $product->stock_quantity,
            'min_stock'      => $product->min_stock_alert,
            'category'       => $product->category ?? 'General Hardware',
            'unit'           => $product->unit ?? 'pcs',
            'reorder_link'   => route('inventory.index', ['status' => 'low_stock']),
            'customer_name'  => 'Store Manager',
        ];

        $dispatchedLogs = [];

        if ($recipients->isNotEmpty()) {
            foreach ($recipients as $recipient) {
                $vars = array_merge($variables, [
                    'customer_name' => $recipient->name,
                ]);

                $logs = $this->alertService->sendAlert(
                    'stock_low_threshold_alert',
                    $recipient,
                    $vars,
                    $product
                );

                $dispatchedLogs = array_merge($dispatchedLogs, $logs);
            }
        } else {
            // Fallback system notification log
            $fallbackRecipient = (object) [
                'name'  => 'System Admin',
                'email' => config('mail.from.address', 'admin@cctvcrm.com'),
                'phone' => null,
            ];

            $logs = $this->alertService->sendAlert(
                'stock_low_threshold_alert',
                $fallbackRecipient,
                $variables,
                $product
            );

            $dispatchedLogs = array_merge($dispatchedLogs, $logs);
        }

        Log::info("Stock low-threshold alert dispatched for product {$product->sku} ({$product->name}). Remaining: {$product->stock_quantity}");

        return $dispatchedLogs;
    }

    /**
     * Sweep through all active products and trigger alerts for any low or out of stock items.
     */
    public function sweepLowStock(bool $force = false): int
    {
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock_alert')
            ->get();

        $alertCount = 0;

        foreach ($lowStockProducts as $product) {
            $logs = $this->checkAndTriggerAlert($product, $force);
            if (!empty($logs)) {
                $alertCount++;
            }
        }

        return $alertCount;
    }
}

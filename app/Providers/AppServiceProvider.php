<?php

namespace App\Providers;

use App\Support\SharedDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        SharedDatabase::guardDestructiveCommands();

        Gate::policy(\App\Models\PurchaseOrder::class, \App\Policies\PurchaseOrderPolicy::class);
        Gate::policy(\App\Models\Supplier::class, \App\Policies\SupplierPolicy::class);
        Gate::policy(\App\Models\GoodsReceipt::class, \App\Policies\GoodsReceiptPolicy::class);
        Gate::policy(\App\Models\VendorBill::class, \App\Policies\VendorBillPolicy::class);
        Gate::policy(\App\Models\PurchaseReturn::class, \App\Policies\PurchaseReturnPolicy::class);

        // Enforce HTTPS in production or when running behind an SSL-terminating reverse proxy (Render, Cloudflare, etc.)
        if (
            app()->environment('production') ||
            str_starts_with((string) config('app.url'), 'https://') ||
            request()->header('X-Forwarded-Proto') === 'https' ||
            request()->server('HTTP_X_FORWARDED_PROTO') === 'https' ||
            request()->isSecure()
        ) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}

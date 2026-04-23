<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Blade::directive('plan', function ($feature) {
            return "<?php if(tenancy()->tenant && \\App\\Helpers\\SubscriptionHelper::canAccess(tenancy()->tenant->subscription, {$feature})): ?>";
        });

        Blade::directive('endplan', function () {
            return "<?php endif; ?>";
        });

        View::composer('layouts.navigation', function ($view) {
            $notifications = collect();

            if (Auth::check()) {
                try {
                    $notifications = Auth::user()
                        ->notifications()
                        ->latest()
                        ->take(20)       // prevent unbounded queries on active tenants
                        ->get();
                } catch (\Throwable) {
                    // Fail silently — never crash a page over missing notifications
                }
            }

            $view->with('notifications', $notifications);
        });
    }
}
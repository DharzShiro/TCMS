<?php

namespace App\Providers;

use App\Events\NewReleasePublished;
use App\Events\TenantUpdateCompleted;
use App\Events\TenantUpdateFailed;
use App\Listeners\NotifyAdminOfNewRelease;
use App\Listeners\NotifyAdminOfTenantUpdate;
use App\Models\SystemRelease;
use App\Models\TenantVersionStatus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ── Update module events ──────────────────────────────────────────
        Event::listen(NewReleasePublished::class,  NotifyAdminOfNewRelease::class);
        Event::listen(TenantUpdateCompleted::class, [NotifyAdminOfTenantUpdate::class, 'handleCompleted']);
        Event::listen(TenantUpdateFailed::class,    [NotifyAdminOfTenantUpdate::class, 'handleFailed']);

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

        // ── Dynamic version display ───────────────────────────────────────
        // Resolved once per request and cached in the service container so
        // both layouts.navigation and layouts.sidebar only hit the DB once.
        // Central domain  → latest active release version (admin sees newest fetched version)
        // Tenant domain   → tenant's own deployed version (trainer/trainee see their version)
        $resolveVersion = static function (): string {
            if (app()->has('_app_display_version')) {
                return app('_app_display_version');
            }

            try {
                if (tenancy()->initialized()) {
                    $status  = TenantVersionStatus::where('tenant_id', tenancy()->tenant->id)->first();
                    $version = $status?->current_version ?? config('github.current_version', '1.0.0');
                } else {
                    $release = SystemRelease::latestActive();
                    $version = $release?->version ?? config('github.current_version', '1.0.0');
                }
            } catch (\Throwable) {
                $version = config('github.current_version', '1.0.0');
            }

            app()->instance('_app_display_version', $version);
            return $version;
        };

        View::composer(['layouts.navigation', 'layouts.sidebar'], function ($view) use ($resolveVersion) {
            $view->with('_appVersion', $resolveVersion());
        });
    }
}
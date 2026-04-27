<?php

namespace App\Providers;

use App\Events\NewReleasePublished;
use App\Events\TenantUpdateCompleted;
use App\Events\TenantUpdateFailed;
use App\Listeners\NotifyAdminOfNewRelease;
use App\Listeners\NotifyAdminOfTenantUpdate;
use App\Models\SystemRelease;
use App\Models\TenantVersionStatus;
use Illuminate\Support\Facades\DB;
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
        // Central domain  → latest active release version
        // Tenant domain   → this tenant's own deployed version
        // Raw DB::table() used deliberately — bypasses Eloquent connection
        // routing so both central and tenant contexts hit the right database.
        View::composer(['layouts.navigation', 'layouts.sidebar'], function ($view) {
            $version = config('github.current_version', '1.0.0');

            try {
                $tenant = tenancy()->tenant ?? null;

                if ($tenant !== null) {
                    $row = DB::connection('mysql')
                        ->table('tenant_version_statuses')
                        ->where('tenant_id', $tenant->id)
                        ->value('current_version');

                    if ($row) {
                        $version = $row;
                    }
                } else {
                    $row = DB::connection('mysql')
                        ->table('system_releases')
                        ->where('is_active', true)
                        ->orderByDesc('published_at')
                        ->value('version');

                    if ($row) {
                        $version = $row;
                    }
                }
            } catch (\Throwable) {
                // Keep config fallback — never crash a page over a missing version
            }

            $view->with('_appVersion', $version);
        });
    }
}
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SuperAdminLoginController;
use App\Http\Controllers\Auth\SuperAdminRegisterController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\SuperAdmin\SuperAdminAnalyticsController;
use App\Http\Controllers\SuperAdmin\SuperAdminReportController;
use App\Http\Controllers\SuperAdmin\SuperAdminActivityLogController;
use App\Http\Controllers\SuperAdmin\SuperAdminPlanController;
use App\Http\Controllers\SuperAdmin\SuperAdminMonitoringController;
use App\Http\Controllers\SuperAdmin\SuperAdminRenewalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SuperAdmin\SuperAdminReleaseController;
use App\Http\Controllers\SuperAdmin\SuperAdminSupportController;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)
        ->middleware('web')
        ->group(function () {

        // ── Landing page ───────────────────────────────────────────────────
        Route::get('/', function () {
            return view('superadmin.welcome');
        });

        Route::get('/auth/google/callback', [
                \App\Http\Controllers\Auth\SocialAuthController::class,
                'handleGoogleCallback'
            ])->name('auth.google.callback.central');

        // ── Guest routes ───────────────────────────────────────────────────
        Route::middleware('guest:web')->group(function () {
            Route::get('/login',     [SuperAdminLoginController::class, 'showLoginForm'])->name('superadmin.login');
            Route::post('/login',    [SuperAdminLoginController::class, 'login']);
            Route::get('/register', [SuperAdminRegisterController::class, 'showRegistrationForm'])->name('register');
            Route::post('/register', [SuperAdminRegisterController::class, 'register']);
            
        });

        // ── Authenticated routes ───────────────────────────────────────────
        Route::middleware('auth:web')->group(function () {
            Route::post('/logout', [SuperAdminLoginController::class, 'logout'])->name('superadmin.logout');
            Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
            Route::get('/profile',          [ProfileController::class, 'edit'])->name('superadmin.profile.edit');
            Route::patch('/profile',        [ProfileController::class, 'update'])->name('superadmin.profile.update');
            Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('superadmin.profile.password.update');
            Route::delete('/profile',       [ProfileController::class, 'destroy'])->name('superadmin.profile.destroy');
        });

        // ── SuperAdmin protected routes ────────────────────────────────────
        Route::middleware(['auth:web', 'superadmin'])
            ->prefix('superadmin')
            ->name('superadmin.')
            ->group(function () {

                // Dashboard
                Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');

                // ── Tenant routes ──────────────────────────────────────────
                // Static routes BEFORE wildcard {tenant}
                Route::get('/tenants',        [SuperAdminController::class, 'index'])->name('tenants.index');
                Route::get('/tenants/create', [SuperAdminController::class, 'create'])->name('tenants.create');
                Route::post('/tenants',       [SuperAdminController::class, 'store'])->name('tenants.store');

                // In your routes/web.php, inside your superadmin route group, add these two lines:
                Route::patch('tenants/{tenant}/enable',  [SuperAdminController::class, 'enable'])->name('tenants.enable');
                Route::patch('tenants/{tenant}/disable', [SuperAdminController::class, 'disable'])->name('tenants.disable');

                // Wildcard {tenant} routes AFTER static routes
                Route::get('/tenants/{tenant}',           [SuperAdminController::class, 'show'])->name('tenants.show');
                Route::delete('/tenants/{tenant}',        [SuperAdminController::class, 'destroy'])->name('tenants.destroy');
                Route::post('/tenants/{tenant}/approve',  [SuperAdminController::class, 'approve'])->name('tenants.approve');
                Route::post('/tenants/{tenant}/reject',   [SuperAdminController::class, 'reject'])->name('tenants.reject');
                Route::patch('/tenants/{tenant}/upgrade', [SuperAdminController::class, 'upgrade'])->name('tenants.upgrade');

                // ── Analytics ─────────────────────────────────────────────
                Route::get('/analytics', [SuperAdminAnalyticsController::class, 'index'])->name('analytics.index');

                // ── Reports ───────────────────────────────────────────────
                Route::prefix('reports')->name('reports.')->group(function () {
                    Route::get('/',              [SuperAdminReportController::class, 'index'])               ->name('index');
                    Route::get('/tenants',       [SuperAdminReportController::class, 'exportTenants'])       ->name('tenants');
                    Route::get('/subscriptions', [SuperAdminReportController::class, 'exportSubscriptions']) ->name('subscriptions');
                    Route::get('/activity',      [SuperAdminReportController::class, 'exportActivity'])      ->name('activity');
                    Route::get('/registrations', [SuperAdminReportController::class, 'exportRegistrations']) ->name('registrations');
                });

                Route::prefix('plans')->name('plans.')->group(function () {
                    Route::get('/',                       [SuperAdminPlanController::class, 'index'])          ->name('index');
                    Route::post('/apply',                 [SuperAdminPlanController::class, 'applyToTenant'])  ->name('apply');
                
                    // Discounts
                    Route::post('/discounts',             [SuperAdminPlanController::class, 'storeDiscount'])   ->name('discounts.store');
                    Route::patch('/discounts/{discount}', [SuperAdminPlanController::class, 'updateDiscount'])  ->name('discounts.update');
                    Route::delete('/discounts/{discount}',[SuperAdminPlanController::class, 'destroyDiscount']) ->name('discounts.destroy');
                    Route::post('/discounts/validate',    [SuperAdminPlanController::class, 'validateCode'])    ->name('discounts.validate');
                });

                Route::resource('plans/manage', SuperAdminPlanController::class)
                    ->names('plans.manage')
                    ->parameters(['manage' => 'plan'])
                    ->except(['show', 'index']);

                Route::get('/renewals', [SuperAdminRenewalController::class, 'index'])
                    ->name('renewals.index');
                Route::post('/renewals/{renewal}/approve', [SuperAdminRenewalController::class, 'approve'])
                    ->name('renewals.approve');
                Route::post('/renewals/{renewal}/reject', [SuperAdminRenewalController::class, 'reject'])
                    ->name('renewals.reject');

                Route::get('/activity-logs', [SuperAdminActivityLogController::class, 'index'])
                    ->name('activity-logs.index');
                
                Route::prefix('monitoring')->name('monitoring.')->group(function () {
                    Route::get('/',                                     [SuperAdminMonitoringController::class, 'index'])          ->name('index');
                    Route::post('/recalculate-all',                     [SuperAdminMonitoringController::class, 'recalculateAll'])->name('recalculate.all');
                    Route::post('/recalculate/{tenant}',                [SuperAdminMonitoringController::class, 'recalculate'])   ->name('recalculate');
                });

                // ── Release / Update Management ───────────────────────────
                Route::prefix('releases')->name('releases.')->group(function () {
                    Route::get('/',                                      [SuperAdminReleaseController::class, 'index'])           ->name('index');
                    Route::post('/fetch',                                [SuperAdminReleaseController::class, 'fetch'])           ->name('fetch');
                    Route::post('/sync-tenants',                        [SuperAdminReleaseController::class, 'syncTenants'])     ->name('sync-tenants');
                    Route::get('/{release}',                            [SuperAdminReleaseController::class, 'show'])            ->name('show');
                    Route::post('/{release}/deploy',                    [SuperAdminReleaseController::class, 'deploy'])          ->name('deploy');
                    Route::post('/{release}/undeploy',                  [SuperAdminReleaseController::class, 'undeploy'])        ->name('undeploy');
                    Route::post('/{release}/push-all',                  [SuperAdminReleaseController::class, 'pushUpdateToAll'])  ->name('push-all');
                    Route::post('/{release}/push-tenant',               [SuperAdminReleaseController::class, 'pushUpdateToTenant'])->name('push-tenant');
                });

                // ── Support Inbox ─────────────────────────────────────────
                Route::prefix('support')->name('support.')->group(function () {
                    Route::get('/',                                      [SuperAdminSupportController::class, 'index'])           ->name('index');
                    // Specific non-wildcard routes BEFORE the /{ticket} wildcard
                    Route::get('/attachment/{attachment}/download',     [SuperAdminSupportController::class, 'downloadAttachment'])->name('attachment');
                    Route::get('/attachment/{attachment}/preview',      [SuperAdminSupportController::class, 'previewAttachment']) ->name('preview');
                    Route::get('/{ticket}',                             [SuperAdminSupportController::class, 'show'])            ->name('show');
                    Route::post('/{ticket}/reply',                      [SuperAdminSupportController::class, 'reply'])           ->name('reply');
                    Route::patch('/{ticket}/status',                    [SuperAdminSupportController::class, 'updateStatus'])    ->name('status');
                    Route::patch('/{ticket}/priority',                  [SuperAdminSupportController::class, 'updatePriority'])  ->name('priority');
                });
        });
    });
}
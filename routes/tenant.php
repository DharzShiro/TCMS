<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

// Controllers — Auth
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\TenantLoginController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\TraineeRegisterController;

// Controllers — Admin
use App\Http\Controllers\Tenant\Admin\AdminController;
use App\Http\Controllers\Tenant\Admin\AdminTrainersManagementController;
use App\Http\Controllers\Tenant\Admin\AdminTraineesManagementController;
use App\Http\Controllers\Tenant\Admin\AdminCourseController;
use App\Http\Controllers\Tenant\Admin\AdminEnrollmentController;
use App\Http\Controllers\Tenant\Admin\AdminTrainingScheduleController;
use App\Http\Controllers\Tenant\Admin\AdminAttendanceController;
use App\Http\Controllers\Tenant\Admin\AdminCertificateController;
use App\Http\Controllers\Tenant\Admin\AdminUserController;
use App\Http\Controllers\Tenant\Admin\AdminSubscriptionController;
use App\Http\Controllers\Tenant\Admin\AdminReportController;
use App\Http\Controllers\Tenant\Admin\AdminBrandingController;
use App\Http\Controllers\Tenant\Admin\AdminAssessmentController;
use App\Http\Controllers\Tenant\Admin\AdminRenewalController;


// Controllers — Trainer
use App\Http\Controllers\Tenant\Trainer\TrainerController;
use App\Http\Controllers\Tenant\Trainer\TrainerScheduleController;
use App\Http\Controllers\Tenant\Trainer\TrainerAttendanceController;
use App\Http\Controllers\Tenant\Trainer\TrainerAssessmentController;
use App\Http\Controllers\Tenant\Trainer\TrainerTraineeController;

// Controllers — Trainee
use App\Http\Controllers\Tenant\Trainee\TraineeController;
use App\Http\Controllers\Tenant\Trainee\TraineeCourseController;
use App\Http\Controllers\Tenant\Trainee\TraineeEnrollmentController;
use App\Http\Controllers\Tenant\Trainee\TraineeScheduleController;
use App\Http\Controllers\Tenant\Trainee\TraineeAssessmentController;
use App\Http\Controllers\Tenant\Trainee\TraineeCertificateController;
use App\Http\Controllers\Tenant\Admin\CustomReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Tenant\WelcomeController;
use App\Http\Controllers\Tenant\Admin\AdminUpdateController;
use App\Http\Controllers\Tenant\Admin\AdminSupportController;


Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'tenant.active',
    'track.bandwidth',
])->group(function () {

    Route::get('/', WelcomeController::class);

    // ── Guest routes ───────────────────────────────────────────────────────
    // Tenant guest routes — use guest:tenant so the tenant guard is checked,
    // not the default web guard (which would always appear unauthenticated).
    Route::middleware('guest:tenant')->group(function () {
        Route::get('/login',     [TenantLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login',    [TenantLoginController::class, 'login']);
        Route::get('/register',  [TraineeRegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [TraineeRegisterController::class, 'register']);
        Route::get('/auth/google',        [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
        Route::get('/auth/google/finish', [SocialAuthController::class, 'finishGoogleLogin'])->name('auth.google.finish');
    });

    // ── Authenticated routes ───────────────────────────────────────────────
    Route::middleware('auth:tenant')->group(function () {
        Route::post('/logout', [TenantLoginController::class, 'logout'])->name('logout');

        Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.markRead');

        Route::get('/profile',          [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile',        [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
        Route::delete('/profile',       [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // ── Admin ──────────────────────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->middleware(['auth:tenant', 'role:admin'])->group(function () {

        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        Route::get('/subscription',                [AdminSubscriptionController::class, 'index'])->name('subscription.index');
        Route::post('/subscription/upgrade',       [AdminSubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
        Route::post('/subscription/validate-code', [AdminSubscriptionController::class, 'validateCode'])->name('subscription.validate-code');
        Route::post('/subscription/resolve-price', [AdminSubscriptionController::class, 'resolvePrice'])->name('subscription.resolve-price');
         // Expiry wall (no subscription check — always accessible)
        // ── System Update ──────────────────────────────────────────────
        Route::prefix('update')->name('update.')->group(function () {
            Route::get('/',           [AdminUpdateController::class, 'index'])       ->name('index');
            Route::post('/apply',     [AdminUpdateController::class, 'applyUpdate']) ->name('apply');
        });

        // ── Support Center ─────────────────────────────────────────────
        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/',                                   [AdminSupportController::class, 'index'])              ->name('index');
            Route::get('/create',                             [AdminSupportController::class, 'create'])             ->name('create');
            Route::post('/',                                  [AdminSupportController::class, 'store'])              ->name('store');
            Route::get('/{id}',                              [AdminSupportController::class, 'show'])               ->name('show');
            Route::post('/{id}/reply',                       [AdminSupportController::class, 'reply'])              ->name('reply');
            Route::get('/{ticketId}/attachment/{attachment}',         [AdminSupportController::class, 'downloadAttachment']) ->name('attachment');
            Route::get('/{ticketId}/attachment/{attachment}/preview', [AdminSupportController::class, 'previewAttachment'])  ->name('preview');
        });

        Route::get('/subscription/expired', [AdminRenewalController::class, 'expired'])
            ->name('subscription.expired');
        Route::post('/renewal/request', [AdminRenewalController::class, 'request'])
            ->name('renewal.request');
        Route::post('/renewal/cancel', [AdminRenewalController::class, 'cancel'])
            ->name('renewal.cancel');

        // ── Analytics & Reports ────────────────────────────────────────────
        Route::middleware('subscription:reports')->group(function () {
            Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export/trainees',     [AdminReportController::class, 'exportTrainees'])->name('reports.export.trainees');
            Route::get('/reports/export/trainers',     [AdminReportController::class, 'exportTrainers'])->name('reports.export.trainers');
            Route::get('/reports/export/enrollments',  [AdminReportController::class, 'exportEnrollments'])->name('reports.export.enrollments');
            Route::get('/reports/export/attendances',  [AdminReportController::class, 'exportAttendances'])->name('reports.export.attendances');
            Route::get('/reports/export/assessments',  [AdminReportController::class, 'exportAssessments'])->name('reports.export.assessments');
            Route::get('/reports/export/certificates', [AdminReportController::class, 'exportCertificates'])->name('reports.export.certificates');
            
        });

        Route::middleware(['subscription:custom-reports'])->group(function () {
            Route::get('/reports/custom', [CustomReportController::class, 'index'])
                ->name('reports.custom.index');
            Route::post('/reports/custom/preview', [CustomReportController::class, 'preview'])
                ->name('reports.custom.preview');
            Route::get('/reports/custom/export', [CustomReportController::class, 'export'])
                ->name('reports.custom.export');
        });

        Route::middleware('subscription:branding')->group(function () {
            Route::get('/branding',  [AdminBrandingController::class, 'index'])->name('branding.index');
            Route::post('/branding', [AdminBrandingController::class, 'update'])->name('branding.update');
            Route::delete('/branding/logo', [AdminBrandingController::class, 'resetLogo'])->name('branding.logo.reset');
        });
        
        // ── Basic plan ─────────────────────────────────────────────────────
        Route::middleware('subscription:trainees')->group(function () {
            Route::resource('trainees', AdminTraineesManagementController::class);
        });

        Route::middleware('subscription:courses')->group(function () {
            Route::resource('courses', AdminCourseController::class);
        });

        Route::middleware('subscription:enrollments')->group(function () {
            Route::resource('enrollments', AdminEnrollmentController::class);
        });

        Route::middleware('subscription:attendances')->group(function () {
            Route::resource('attendances', AdminAttendanceController::class);
        });

        // ── Standard plan ──────────────────────────────────────────────────
        Route::middleware('subscription:trainers')->group(function () {
            Route::resource('trainers', AdminTrainersManagementController::class);
        });

        Route::middleware('subscription:training-schedules')->group(function () {
            Route::resource('training-schedules', AdminTrainingScheduleController::class);
        });

        Route::middleware('subscription:users')->group(function () {
            Route::resource('users', AdminUserController::class);
        });
        Route::middleware('subscription:assessments')->resource('assessments', AdminAssessmentController::class);
        // ── Premium plan ───────────────────────────────────────────────────
        Route::middleware('subscription:certificates')->group(function () {
            Route::get('certificates/{certificate}/preview',  [AdminCertificateController::class, 'preview'])->name('certificates.preview');
            Route::get('certificates/{certificate}/download', [AdminCertificateController::class, 'download'])->name('certificates.download');
            Route::resource('certificates', AdminCertificateController::class);
        });
    });

    // ── Trainer ────────────────────────────────────────────────────────────
    Route::prefix('trainer')->name('trainer.')->middleware(['auth:tenant', 'role:trainer'])->group(function () {
        Route::get('/dashboard', [TrainerController::class, 'dashboard'])->name('dashboard');

        Route::middleware('subscription:training-schedules')->group(function () {
            Route::get('/schedules',                    [TrainerScheduleController::class, 'index'])->name('schedules.index');
            Route::get('/schedules/{trainingSchedule}', [TrainerScheduleController::class, 'show'])->name('schedules.show');
        });

        Route::middleware('subscription:attendances')->group(function () {
            Route::get('/attendances-bulk',  [TrainerAttendanceController::class, 'bulk'])->name('attendances.bulk');
            Route::post('/attendances-bulk', [TrainerAttendanceController::class, 'bulkStore'])->name('attendances.bulk.store');
            Route::resource('attendances', TrainerAttendanceController::class);
        });

        Route::middleware('subscription:assessments')->group(function () {
            Route::resource('assessments', TrainerAssessmentController::class);
        });

        Route::middleware('subscription:trainers')->group(function () {
            Route::get('/trainees',           [TrainerTraineeController::class, 'index'])->name('trainees.index');
            Route::get('/trainees/{trainee}', [TrainerTraineeController::class, 'show'])->name('trainees.show');
        });
    });

    // ── Trainee ────────────────────────────────────────────────────────────
    Route::prefix('trainee')->name('trainee.')->middleware(['auth:tenant', 'role:trainee'])->group(function () {
        Route::get('/dashboard', [TraineeController::class, 'dashboard'])->name('dashboard');

        Route::middleware('subscription:courses')->group(function () {
            Route::get('/courses',                  [TraineeCourseController::class, 'index'])->name('courses.index');
            Route::get('/courses/{course}',         [TraineeCourseController::class, 'show'])->name('courses.show');
            Route::post('/courses/{course}/enroll', [TraineeCourseController::class, 'enroll'])->name('courses.enroll');
        });

        Route::middleware('subscription:enrollments')->group(function () {
            Route::get('/enrollments',              [TraineeEnrollmentController::class, 'index'])->name('enrollments.index');
            Route::get('/enrollments/{enrollment}', [TraineeEnrollmentController::class, 'show'])->name('enrollments.show');
        });

        Route::middleware('subscription:training-schedules')->group(function () {
            Route::get('/schedules',                    [TraineeScheduleController::class, 'index'])->name('schedules.index');
            Route::get('/schedules/{trainingSchedule}', [TraineeScheduleController::class, 'show'])->name('schedules.show');
        });

        Route::middleware('subscription:assessments')->group(function () {
            Route::get('/assessments',              [TraineeAssessmentController::class, 'index'])->name('assessments.index');
            Route::get('/assessments/{assessment}', [TraineeAssessmentController::class, 'show'])->name('assessments.show');
        });

        Route::middleware('subscription:certificates')->group(function () {
            Route::get('/certificates/{certificate}/download', [TraineeCertificateController::class, 'download'])->name('certificates.download');
            Route::get('/certificates/{certificate}/preview',  [TraineeCertificateController::class, 'preview'])->name('certificates.preview');
            Route::get('/certificates',               [TraineeCertificateController::class, 'index'])->name('certificates.index');
            Route::get('/certificates/{certificate}', [TraineeCertificateController::class, 'show'])->name('certificates.show');
        });
    });

});
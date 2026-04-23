<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tenants:recalculate-storage')->hourly();

Schedule::command('subscriptions:notify-expiring')->dailyAt('08:00');

// Fetch new GitHub releases and sync tenant version statuses every 6 hours
Schedule::command('releases:fetch --sync')->everySixHours();

// Nightly full tenant version sync (catches any drift)
Schedule::command('releases:sync-tenants')->dailyAt('03:00');

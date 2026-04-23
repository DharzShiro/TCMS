<?php

namespace App\Listeners;

use App\Events\NewReleasePublished;
use App\Jobs\NotifyTenantsOfReleaseJob;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotifyAdminOfNewRelease
{
    public function handle(NewReleasePublished $event): void
    {
        $release = $event->release;

        // Notify all superadmin users (central notifications table)
        User::all()->each(function (User $user) use ($release) {
            try {
                Notification::create([
                    'user_id' => $user->id,
                    'title'   => "New Release: v{$release->version}",
                    'message' => "GitHub release \"{$release->name}\" is now available. Mark it as deployed to push the update to tenants.",
                    'link'    => route('superadmin.releases.show', $release),
                ]);
            } catch (\Throwable $e) {
                Log::warning('[Release] Admin notification failed: ' . $e->getMessage());
            }
        });

        // Dispatch job to notify each tenant's admin user
        NotifyTenantsOfReleaseJob::dispatch($release)->onQueue('updates');
    }
}

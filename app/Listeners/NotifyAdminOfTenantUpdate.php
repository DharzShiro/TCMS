<?php

namespace App\Listeners;

use App\Events\TenantUpdateCompleted;
use App\Events\TenantUpdateFailed;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotifyAdminOfTenantUpdate
{
    public function handleCompleted(TenantUpdateCompleted $event): void
    {
        $this->notifySuperAdmins(
            "✅ Tenant Updated: {$event->tenant->name}",
            "{$event->tenant->name} was successfully updated to v{$event->release->version}.",
            route('superadmin.releases.show', $event->release),
        );
    }

    public function handleFailed(TenantUpdateFailed $event): void
    {
        $this->notifySuperAdmins(
            "❌ Update Failed: {$event->tenant->name}",
            "{$event->tenant->name} failed to update to v{$event->release->version}. Reason: {$event->reason}",
            route('superadmin.releases.show', $event->release),
        );
    }

    private function notifySuperAdmins(string $title, string $message, string $link): void
    {
        User::all()->each(function (User $user) use ($title, $message, $link) {
            try {
                Notification::create([
                    'user_id' => $user->id,
                    'title'   => $title,
                    'message' => $message,
                    'link'    => $link,
                ]);
            } catch (\Throwable $e) {
                Log::warning('[Update] Superadmin notification failed: ' . $e->getMessage());
            }
        });
    }
}

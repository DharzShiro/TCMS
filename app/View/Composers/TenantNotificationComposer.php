<?php

namespace App\View\Composers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TenantNotificationComposer
{
    /**
     * Bind notifications data to the navigation view.
     *
     * Fetches the latest 20 notifications for the authenticated user
     * (within the tenant DB context) so the bell dropdown is always fresh.
     */
    public function compose(View $view): void
    {
        if (! Auth::check()) {
            $view->with('notifications', collect());
            return;
        }

        try {
            $notifications = Notification::where('user_id', Auth::id())
                ->latest()
                ->take(20)
                ->get();
        } catch (\Throwable) {
            // Fail silently — never crash the page over missing notifications
            $notifications = collect();
        }

        $view->with('notifications', $notifications);
    }
}
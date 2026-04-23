<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiringMail;
use App\Models\Notification;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyExpiringSubscriptions extends Command
{
    /**
     * Notification thresholds in days.
     * An email is sent only at these exact thresholds.
     * In-app notifications are created at every threshold.
     */
    private const EMAIL_THRESHOLDS = [30, 10, 7, 3, 1];
    private const BELL_THRESHOLDS  = [30, 14, 10, 7, 3, 1];

    protected $signature   = 'subscriptions:notify-expiring';
    protected $description = 'Notify tenants whose subscriptions are about to expire.';

    public function handle(): int
    {
        // Fetch approved tenants with a non-null, future expires_at
        $tenants = Tenant::where('status', 'approved')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->get();

        $this->info("Checking {$tenants->count()} active tenant(s)...");

        foreach ($tenants as $tenant) {
            $daysLeft = (int) now()->startOfDay()->diffInDays($tenant->expires_at->copy()->startOfDay(), false);

            if ($daysLeft < 0) continue; // already expired — skip

            $this->sendEmailIfThreshold($tenant, $daysLeft);
            $this->createBellNotificationIfThreshold($tenant, $daysLeft);
        }

        $this->info('Done.');
        return self::SUCCESS;
    }

    // ── Email ─────────────────────────────────────────────────────────────

    private function sendEmailIfThreshold(Tenant $tenant, int $daysLeft): void
    {
        if (! in_array($daysLeft, self::EMAIL_THRESHOLDS, true)) return;

        try {
            Mail::to($tenant->admin_email)
                ->send(new SubscriptionExpiringMail($tenant, $daysLeft));

            $this->line("  [EMAIL] {$tenant->name} → {$tenant->admin_email} ({$daysLeft}d left)");
        } catch (\Throwable $e) {
            $this->error("  [EMAIL FAILED] {$tenant->name}: {$e->getMessage()}");
        }
    }

    // ── In-app bell notification ──────────────────────────────────────────

    private function createBellNotificationIfThreshold(Tenant $tenant, int $daysLeft): void
    {
        if (! in_array($daysLeft, self::BELL_THRESHOLDS, true)) return;

        // Avoid duplicate notifications for the same tenant + same threshold today.
        try {
            $tenant->run(function () use ($tenant, $daysLeft) {
                $admin = User::where('role', 'admin')->first();
                if (! $admin) return;

                $alreadySent = Notification::where('user_id', $admin->id)
                    ->where('title', 'like', '%Subscription Expiring%')
                    ->whereDate('created_at', today())
                    ->exists();

                if ($alreadySent) return;

                $urgency = match(true) {
                    $daysLeft <= 1  => '🔴 URGENT — ',
                    $daysLeft <= 3  => '🟠 ',
                    $daysLeft <= 7  => '🟡 ',
                    default         => 'ℹ️  ',
                };

                Notification::create([
                    'user_id' => $admin->id,
                    'title'   => "{$urgency}Subscription Expiring in {$daysLeft} Day(s)",
                    'message' => "Your " . ucfirst($tenant->subscription) . " Plan expires on "
                               . $tenant->expires_at->format('F d, Y') . ". "
                               . ($daysLeft <= 3
                                   ? 'Please renew immediately to avoid service interruption.'
                                   : 'Please renew soon to avoid service interruption.'),
                    'is_read' => false,
                    'link'    => '/admin/subscription',
                ]);

                $this->line("  [BELL] {$tenant->name} admin notified ({$daysLeft}d left)");
            });
        } catch (\Throwable $e) {
            $this->error("  [BELL FAILED] {$tenant->name}: {$e->getMessage()}");
        }
    }
}
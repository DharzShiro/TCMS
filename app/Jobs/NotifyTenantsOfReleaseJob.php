<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\SystemRelease;
use App\Models\Tenant;
use App\Models\TenantVersionStatus;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyTenantsOfReleaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300;

    public function __construct(private readonly SystemRelease $release) {}

    public function handle(): void
    {
        $tenants = Tenant::where('status', 'approved')->get();

        foreach ($tenants as $tenant) {
            $this->notifyTenant($tenant);
        }

        Log::info("[Releases] Notified {$tenants->count()} tenants of v{$this->release->version}.");
    }

    private function notifyTenant(Tenant $tenant): void
    {
        try {
            $tenant->run(function () {
                $admin = User::where('role', 'admin')->first();

                if ($admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'title'   => "System Update Available — v{$this->release->version}",
                        'message' => "A new system update is available: {$this->release->name}. Click to review and apply.",
                        'link'    => '/admin/update',
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::warning("[Releases] Notification failed for tenant {$tenant->id}: {$e->getMessage()}");
        }
    }
}

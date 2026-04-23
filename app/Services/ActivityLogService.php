<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogService
{
    public static function log(
        Request $request,
        string  $action,
        bool    $success = true,
        ?string $failureReason = null,
        ?array  $userOverride = null  // for failed logins where user isn't authenticated
    ): void {
        try {
            $tenant = null;
            try {
                $tenant = tenancy()->tenant ?? null;
            } catch (\Throwable) {
                // Not in tenant context (e.g. superadmin)
            }

            $user = auth()->user();

            ActivityLog::create([
                'tenant_id'      => $tenant?->id,
                'tenant_name'    => $tenant?->name,
                'user_id'        => $user?->id ?? $userOverride['id'] ?? null,
                'user_name'      => $user?->name ?? $userOverride['name'] ?? null,
                'user_email'     => $user?->email ?? $userOverride['email'] ?? null,
                'role'           => $user?->role ?? $userOverride['role'] ?? null,
                'action'         => $action,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'success'        => $success,
                'failure_reason' => $failureReason,
            ]);
        } catch (\Throwable $e) {
            // Never let logging crash the app
            logger()->error('ActivityLog failed: ' . $e->getMessage());
        }
    }
}
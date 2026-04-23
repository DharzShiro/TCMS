<?php

namespace App\Http\Middleware;

use App\Models\TenantUsageStat;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackTenantBandwidth
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $tenant = tenancy()->tenant ?? null;

            if (! $tenant) {
                return $response;
            }

            // Estimate: request size + response size
            $requestSize  = strlen($request->getContent()) + strlen(serialize($request->headers->all()));
            $responseSize = strlen($response->getContent());
            $totalBytes   = $requestSize + $responseSize;

            $today = now()->toDateString();

            $stat = TenantUsageStat::firstOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'db_size_bytes'          => 0,
                    'file_size_bytes'        => 0,
                    'bandwidth_bytes_today'  => 0,
                    'bandwidth_bytes_total'  => 0,
                ]
            );

            // Reset daily counter if it's a new day
            if ($stat->bandwidth_date?->toDateString() !== $today) {
                $stat->bandwidth_bytes_today = 0;
                $stat->bandwidth_date        = $today;
            }

            $stat->increment('bandwidth_bytes_today', $totalBytes);
            $stat->increment('bandwidth_bytes_total', $totalBytes);

        } catch (\Throwable) {
            // Never crash the app for monitoring
        }

        return $response;
    }
}
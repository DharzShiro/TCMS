<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforceSubscriptionExpiry
{
    // Routes always accessible even when expired
    private const ALWAYS_ALLOW = [
        'admin.subscription.*',
        'admin.renewal.*',
        'logout',
    ];

    public function handle(Request $request, Closure $next)
    {
        $tenant = tenancy()->tenant;
        if (! $tenant) return $next($request);

        // Not expired — continue normally
        if ($tenant->isSubscribed()) return $next($request);

        // Expired — allow only the expiry wall and renewal routes
        $routeName = $request->route()?->getName() ?? '';
        foreach (self::ALWAYS_ALLOW as $pattern) {
            if (fnmatch($pattern, $routeName)) return $next($request);
        }

        return redirect()->route('admin.subscription.expired');
    }
}
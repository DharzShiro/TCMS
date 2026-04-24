<?php
// app/Http/Middleware/EnsureTenantIsActive.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Facades\Tenancy;

class EnsureTenantIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = tenancy()->tenant;

        if ($tenant && ! $tenant->is_active) {
            auth()->guard('tenant')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return response()->view('tenant-disabled', ['tenant' => $tenant], 403);
        }

        return $next($request);
    }
}
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

            return redirect()->route('login')->withErrors([
                'email' => 'Your organization\'s access has been suspended. Please contact support.',
            ]);
        }

        return $next($request);
    }
}
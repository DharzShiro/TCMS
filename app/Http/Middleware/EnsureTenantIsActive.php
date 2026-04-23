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
            // Clear tenancy so the session isn't poisoned
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Your organization\'s access has been suspended. Please contact support.',
            ]);
        }

        return $next($request);
    }
}
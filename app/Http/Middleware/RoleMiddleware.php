<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Use the tenant guard — auth:tenant middleware runs first and calls
        // Auth::shouldUse('tenant'), so auth()->user() is reliable here too,
        // but being explicit avoids any default-guard ambiguity.
        $user = auth()->guard('tenant')->user();

        if (! $user || ! in_array($user->role, $roles)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
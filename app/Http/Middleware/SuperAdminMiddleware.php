<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->guard('web')->check()) {
            return redirect()->route('superadmin.login');
        }

        if (auth()->guard('web')->user()->role !== 'superadmin') {
            abort(403, 'Forbidden: Super Admin access required');
        }

        return $next($request);
    }
}
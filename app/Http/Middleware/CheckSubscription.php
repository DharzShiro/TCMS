<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    protected array $plans = ['basic', 'standard', 'premium'];

    protected array $featurePlans = [
        'trainees'           => 'basic',
        'courses'            => 'basic',
        'enrollments'        => 'basic',
        'attendances'        => 'basic',
        'trainers'           => 'standard',
        'assessments'        => 'standard',
        'training-schedules' => 'standard',
        'users'              => 'standard',
        'reports'            => 'standard',
        'certificates'       => 'premium',
        'custom-reports'     => 'premium',
        'branding'           => 'premium',
    ];

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            abort(403, 'No active tenant.');
        }

        if (!$tenant->isSubscribed()) {
            return redirect()->route('login')
                ->withErrors(['subscription' => 'Your subscription has expired.']);
        }

        $required = $this->featurePlans[$feature] ?? 'basic';
        $current  = $tenant->subscription;

        if (!$this->hasAccess($current, $required)) {
            abort(403, "Your plan ({$current}) does not include this feature. Upgrade to {$required} or higher.");
        }

        return $next($request);
    }

    protected function hasAccess(string $current, string $required): bool
    {
        $ci = array_search($current, $this->plans);
        $ri = array_search($required, $this->plans);
        return $ci !== false && $ri !== false && $ci >= $ri;
    }
}
<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;

class WelcomeController extends Controller
{
    public function __invoke()
    {
        $tenant = tenancy()->tenant;

        $brandLogo    = $tenant?->brand_logo
                            ? asset("storage/{$tenant->brand_logo}")
                            : asset('assets/app_logo.PNG');
        $brandName    = $tenant?->brand_name    ?? config('app.name', 'TCMS');
        $brandTagline = $tenant?->brand_tagline ?? 'TESDA Training Management';
        $colorPrimary = $tenant?->brand_color_primary ?? '#003087';
        $colorAccent  = $tenant?->brand_color_accent  ?? '#CE1126';

        return view('tenants.welcome', compact(
            'brandLogo', 'brandName', 'brandTagline',
            'colorPrimary', 'colorAccent'
        ));
    }
}
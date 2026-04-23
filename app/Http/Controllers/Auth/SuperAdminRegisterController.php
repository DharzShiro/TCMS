<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SuperAdminRegisterController extends Controller
{
    public function showRegistrationForm()
    {
        $today = today();

        // Load only active, currently-available plans ordered by sort_order.
        // This is a Collection (not keyed) so the view can loop over them
        // in the correct display order.
        $plans = collect();
        try {
            $plans = SubscriptionPlan::where('is_active', true)
                ->where(fn($q) => $q->whereNull('available_from')
                                    ->orWhereDate('available_from', '<=', $today))
                ->where(fn($q) => $q->whereNull('available_until')
                                    ->orWhereDate('available_until', '>=', $today))
                ->orderBy('sort_order')
                ->get();
        } catch (\Throwable) {
            // Table not yet migrated — view will show the empty-state message
        }

        return view('auth.register', compact('plans'));
    }

    public function register(Request $request)
    {
        // ── Build valid slug list dynamically from the DB ─────────────────────
        // This means any plan the superadmin creates/activates is automatically
        // accepted here without touching this controller.
        $today      = today();
        $validSlugs = [];

        try {
            $validSlugs = SubscriptionPlan::where('is_active', true)
                ->where(fn($q) => $q->whereNull('available_from')
                                    ->orWhereDate('available_from', '<=', $today))
                ->where(fn($q) => $q->whereNull('available_until')
                                    ->orWhereDate('available_until', '>=', $today))
                ->pluck('slug')
                ->toArray();
        } catch (\Throwable) {
            // Fall back to the three canonical slugs if the table doesn't exist yet
            $validSlugs = ['basic', 'standard', 'premium'];
        }

        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'admin_email'  => ['required', 'email', 'unique:tenants,admin_email'],
            'subdomain'    => ['required', 'string', 'alpha_dash', 'max:100', 'unique:tenants,subdomain'],
            'subscription' => ['required', 'string', 'in:' . implode(',', $validSlugs)],
        ], [
            'subscription.in' => 'Please select a valid subscription plan.',
        ]);

        try {
            Tenant::create([
                'id'           => Str::uuid()->toString(),
                'name'         => $request->name,
                'admin_email'  => $request->admin_email,
                'subdomain' => trim(strtolower($request->subdomain)),
                'subscription' => $request->subscription,
                'status'       => 'pending',
                'is_active'    => true,
                'expires_at'   => null,
            ]);

            // Notify all superadmins (not just the first one)
            $planName = SubscriptionPlan::where('slug', $request->subscription)
                ->value('name') ?? ucfirst($request->subscription);

            User::where('role', 'superadmin')->each(function (User $superadmin) use ($request, $planName) {
                Notification::create([
                    'user_id' => $superadmin->id,
                    'title'   => 'New Tenant Application',
                    'message' => "'{$request->name}' has applied for tenancy ({$planName} plan) with email {$request->admin_email}.",
                    'link'    => route('superadmin.tenants.index'),
                ]);
            });

            return redirect()->route('register')
                ->with('status', 'submitted');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error during registration: ' . $e->getMessage());
        }
    }
}
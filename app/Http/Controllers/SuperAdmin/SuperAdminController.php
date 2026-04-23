<?php
// app/Http/Controllers/SuperAdmin/SuperAdminController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\TenantApprovalMail;
use App\Mail\TenantRejectionMail;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\RenewalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Services\HostsFileService;

class SuperAdminController extends Controller
{
    // -------------------------------------------------------------------------
    // Shared query helper
    // -------------------------------------------------------------------------

    private function tenantStats(): array
    {
        return [
            'tenants'          => Tenant::latest()->get(),
            'approvedTenants'  => Tenant::where('status', 'approved')->get(),
            'pendingTenants'   => Tenant::where('status', 'pending')->get(),
            'rejectedTenants'  => Tenant::where('status', 'rejected')->get(),
        ];
    }

    /**
     * Resolve a SubscriptionPlan from a slug, falling back gracefully
     * if the plans table doesn't exist yet (e.g. before first migrate).
     */
    private function resolvePlan(string $slug): ?SubscriptionPlan
    {
        try {
            return SubscriptionPlan::where('slug', $slug)->first();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Calculate expiry date — reads from SubscriptionPlan if available,
     * otherwise falls back to the original hardcoded durations.
     */
    private function expiresAt(string $slug): \Carbon\Carbon
    {
        $plan = $this->resolvePlan($slug);

        if ($plan) {
            return now()->addDays($plan->duration_days);
        }

        // Fallback (keeps working even if migration hasn't run yet)
        return match($slug) {
            'standard' => now()->addMonths(6),
            'premium'  => now()->addYear(),
            default    => now()->addDays(30),
        };
    }

    /**
     * Generate a unique, database-safe tenant ID from the tenant's name.
     *
     * Examples:
     *   "Makati Training Center" → "makati_training_center"
     *   "ABC Corp (Cebu)"        → "abc_corp_cebu"
     *
     * If the slug is already taken, a numeric suffix is appended:
     *   "makati_training_center_2", "makati_training_center_3", …
     */
    private function generateTenantId(string $name): string
    {
        // Convert to lowercase, replace spaces/hyphens with underscores,
        // strip anything that isn't alphanumeric or underscore.
        $base = preg_replace('/[^a-z0-9]+/', '_', strtolower($name));
        $base = trim($base, '_');

        // Ensure it starts with a letter (MySQL requirement for DB names used
        // directly, and avoids issues with some tenancy drivers).
        if (!preg_match('/^[a-z]/', $base)) {
            $base = 'tenant_' . $base;
        }

        // Truncate to a safe length (MySQL DB name limit is 64 chars).
        $base = substr($base, 0, 50);

        $candidate = $base;
        $counter   = 2;

        while (Tenant::where('id', $candidate)->exists()) {
            $candidate = $base . '_' . $counter;
            $counter++;
        }

        return $candidate;
    }

    // -------------------------------------------------------------------------
    // Dashboard & Index
    // -------------------------------------------------------------------------

    public function dashboard()
    {
        return view('superadmin.dashboard', $this->tenantStats());
    }

    public function index()
    {
        return view('superadmin.tenants.index', $this->tenantStats());
    }

    // -------------------------------------------------------------------------
    // Create & Store
    // -------------------------------------------------------------------------

    public function create()
    {
        return view('superadmin.tenants.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'admin_email'  => ['required', 'email', 'unique:tenants,admin_email'],
            'subdomain'    => ['required', 'string', 'alpha_dash', 'unique:tenants,subdomain'],
            'subscription' => ['required', 'in:basic,standard,premium'],
        ]);

        try {
            // Generate a human-readable, name-based ID so the tenant database
            // is named after the organisation (e.g. "makati_training_center")
            // instead of a random UUID.
            $tenantId = $this->generateTenantId($request->name);

            Tenant::create([
                'id'           => $tenantId,
                'name'         => $request->name,
                'admin_email'  => $request->admin_email,
                'subdomain'    => trim(strtolower($request->subdomain)),
                'subscription' => $request->subscription,
                'status'       => 'pending',
                'is_active'    => true,
                'expires_at'   => null,
            ]);

            return redirect()->route('superadmin.tenants.index')
                ->with('success', 'Tenant registration submitted and is pending approval.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error creating tenant: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function show(Tenant $tenant)
    {
        // Pass plans so the upgrade form can show plan prices from the DB
        $plans = collect();
        try {
            $plans = SubscriptionPlan::orderBy('sort_order')->get();
        } catch (\Throwable) {
            // Plans table not yet migrated — silently ignore
        }

        return view('superadmin.tenants.show', compact('tenant', 'plans'));
    }

    // -------------------------------------------------------------------------
    // Approve — now reads duration from SubscriptionPlan
    // -------------------------------------------------------------------------

    public function approve(Tenant $tenant)
    {
        if ($tenant->status === 'approved') {
            return back()->with('error', 'Tenant is already approved.');
        }
    
        try {
            $password = 'TCM' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $domain   = $tenant->subdomain . '.tcm.com';
    
            $tenant->domains()->create(['domain' => $domain]);
    
            $tenant->status     = 'approved';
            $tenant->is_active  = true;
            $tenant->expires_at = $this->expiresAt($tenant->subscription);
            $tenant->save();
    
            $tenant->run(function () use ($tenant, $password) {
                Artisan::call('migrate', [
                    '--path'  => 'database/migrations/tenant',
                    '--force' => true,
                ]);
    
                DB::connection('tenant')->table('users')->insert([
                    'name'       => $tenant->name,
                    'email'      => $tenant->admin_email,
                    'password'   => Hash::make($password),
                    'role'       => 'admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    
            Mail::to($tenant->admin_email)->send(new TenantApprovalMail($tenant, $password));
    
            // ✅ NEW: Write the subdomain to the hosts file automatically.
            // Matches exactly what the diagram shows: approve() → HostsFileService::addTenantEntry()
            // Wrapped in try/catch so a hosts-file failure never blocks the approval.
            try {
                (new HostsFileService())->addTenantEntry($tenant->subdomain, $tenant->name);
            } catch (\Throwable $e) {
                // Log the warning but don't fail — admin can run `php artisan tenants:sync-hosts`
                \Illuminate\Support\Facades\Log::warning(
                    "Tenant approved but hosts file could not be updated: " . $e->getMessage()
                );
            }
    
            return redirect()->route('superadmin.tenants.index')
                ->with('success', "Tenant approved. Credentials sent to {$tenant->admin_email}.");
    
        } catch (\Exception $e) {
            $tenant->domains()->where('domain', $tenant->subdomain . '.tcm.com')->delete();
            $tenant->status     = 'pending';
            $tenant->expires_at = null;
            $tenant->save();
    
            return back()->with('error', 'Error approving tenant: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Reject
    // -------------------------------------------------------------------------

    public function reject(Tenant $tenant)
    {
        if ($tenant->status === 'rejected') {
            return back()->with('error', 'Tenant is already rejected.');
        }

        try {
            $tenant->status = 'rejected';
            $tenant->save();

            Mail::to($tenant->admin_email)->send(new TenantRejectionMail($tenant));

            return redirect()->route('superadmin.tenants.index')
                ->with('success', 'Tenant registration rejected.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error rejecting tenant: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Enable / Disable
    // -------------------------------------------------------------------------

    public function enable(Tenant $tenant)
    {
        if ($tenant->is_active) {
            return back()->with('error', 'Tenant is already enabled.');
        }

        $tenant->is_active = true;
        $tenant->save();

        return back()->with('success', "Tenant \"{$tenant->name}\" has been enabled.");
    }

    public function disable(Tenant $tenant)
    {
        if (! $tenant->is_active) {
            return back()->with('error', 'Tenant is already disabled.');
        }

        $tenant->is_active = false;
        $tenant->save();

        return back()->with('success', "Tenant \"{$tenant->name}\" has been disabled. They can no longer access the system.");
    }

    // -------------------------------------------------------------------------
    // Upgrade — now reads duration from SubscriptionPlan
    // -------------------------------------------------------------------------

    public function upgrade(Request $request, Tenant $tenant)
    {
        $request->validate([
            'subscription' => ['required', 'in:basic,standard,premium'],
        ]);

        $plans        = ['basic', 'standard', 'premium'];
        $currentIndex = array_search($tenant->subscription, $plans);
        $newIndex     = array_search($request->subscription, $plans);

        if ($newIndex <= $currentIndex) {
            return back()->with('error', 'You can only upgrade to a higher plan.');
        }

        // Cancel any pending renewal requests that are now stale because the
        // tenant has moved to a higher plan.
        $planOrder     = ['basic' => 0, 'standard' => 1, 'premium' => 2];
        $requestedRank = $planOrder[$request->subscription] ?? 0;

        RenewalRequest::on('mysql')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->where(function ($q) use ($planOrder, $requestedRank) {
                $stalePlanSlugs = array_keys(
                    array_filter($planOrder, fn($rank) => $rank <= $requestedRank)
                );
                $q->whereIn('plan_slug', $stalePlanSlugs);
            })
            ->update([
                'status' => 'cancelled_by_upgrade',
                'notes'  => 'Automatically cancelled — tenant upgraded to a higher plan.',
            ]);

        try {
            $tenant->subscription = $request->subscription;
            $tenant->expires_at   = $this->expiresAt($request->subscription);
            $tenant->save();

            return redirect()->back()
                ->with('success', "Tenant upgraded to " . ucfirst($request->subscription) . " plan successfully.");

        } catch (\Exception $e) {
            return back()->with('error', 'Error upgrading tenant: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function destroy(Tenant $tenant)
    {
        try {
            $subdomain = $tenant->subdomain; // capture before deletion
    
            $tenant->delete();
    
            // ✅ NEW: Clean up the hosts file entry when a tenant is deleted.
            try {
                (new HostsFileService())->removeTenantEntry($subdomain);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning(
                    "Tenant deleted but hosts file entry could not be removed: " . $e->getMessage()
                );
            }
    
            return redirect()->route('superadmin.tenants.index')
                ->with('success', 'Tenant deleted successfully.');
    
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting tenant: ' . $e->getMessage());
        }
    }

}
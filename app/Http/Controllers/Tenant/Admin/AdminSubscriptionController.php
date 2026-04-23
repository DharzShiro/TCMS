<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\Notification;
use App\Models\RenewalRequest;
use App\Models\SubscriptionPlan;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSubscriptionController extends Controller
{
    // ── Upgrade page ──────────────────────────────────────────────────────

    public function index()
    {
        $tenant      = tenancy()->tenant;
        $currentPlan = $tenant->subscription ?? 'basic';
        $today = today();
        $plans = SubscriptionPlan::active()
            ->where(fn($q) => $q->whereNull('available_from')
                                ->orWhereDate('available_from', '<=', $today))
            ->where(fn($q) => $q->whereNull('available_until')
                                ->orWhereDate('available_until', '>=', $today))
            ->orderBy('sort_order')
            ->get();
        $planSlugs = $plans->pluck('slug')->toArray();

        // Best active automatic discount for each plan
        $autoDiscounts = [];
        foreach ($plans as $plan) {
            $autoDiscounts[$plan->slug] = Discount::on('mysql')
                ->where('is_active', true)
                ->where('is_automatic', true)
                ->where(fn($q) => $q->whereNull('plan_slugs')
                                    ->orWhereJsonContains('plan_slugs', $plan->slug))
                ->where(fn($q) => $q->whereNull('valid_from')
                                    ->orWhereDate('valid_from', '<=', today()))
                ->where(fn($q) => $q->whereNull('valid_until')
                                    ->orWhereDate('valid_until', '>=', today()))
                ->orderByDesc('value')
                ->first();
        }

        // Any pending renewal request
        $pendingRenewal = RenewalRequest::on('mysql')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return view('tenants.admin.subscription.upgrade', compact(
            'plans',
            'currentPlan',
            'planSlugs',
            'autoDiscounts',
            'pendingRenewal',
        ));
    }

    // ── AJAX: validate a promo code ───────────────────────────────────────

    public function validateCode(Request $request)
    {
        $request->validate([
            'code'      => ['required', 'string'],
            'plan_slug' => ['required', 'string'],
        ]);

        $tenantId = optional(tenancy()->tenant)->id;
        $discount = Discount::findValidCode($request->code, $request->plan_slug, $tenantId);

        if (! $discount) {
            return response()->json(['valid' => false, 'message' => 'Invalid or inapplicable promo code.']);
        }

        $plan  = SubscriptionPlan::where('slug', $request->plan_slug)->firstOrFail();
        $base  = (float) $plan->price;

        return response()->json([
            'valid'           => true,
            'formatted_value' => $discount->formatted_value,
            'original_price'  => $base,
            'discount_amount' => $discount->discountAmount($base),
            'final_price'     => $discount->applyTo($base),
        ]);
    }

    // ── AJAX: upgrade plan ────────────────────────────────────────────────

    public function upgrade(Request $request)
    {
        $data = $request->validate([
            'subscription'  => ['required', 'string'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'discount_code' => ['nullable', 'string'],
        ]);

        $tenant    = tenancy()->tenant;
        $planModel = SubscriptionPlan::where('slug', $data['subscription'])->firstOrFail();
        $basePrice = (float) $planModel->price;

        // Build a dynamic plan order keyed by sort_order
        $allPlans      = SubscriptionPlan::orderBy('sort_order')->pluck('sort_order', 'slug')->toArray();
        $currentRank   = $allPlans[$tenant->subscription] ?? 0;
        $requestedRank = $allPlans[$data['subscription']]  ?? 0;

        // Enforce upgrade-only (no downgrades)
        if ($requestedRank <= $currentRank) {
            return response()->json([
                'success' => false,
                'message' => 'You can only upgrade to a higher plan.',
            ], 422);
        }

        // ── Resolve duration ──────────────────────────────────────────────
        $durationDays = (int) ($data['duration_days'] ?? $planModel->duration_days);
        if ($durationDays < 1) {
            $durationDays = $planModel->duration_days;
        }

        // Pro-rate the price based on the chosen duration vs the standard duration.
        $proratedBase = $planModel->duration_days > 0
            ? round(($basePrice / $planModel->duration_days) * $durationDays, 2)
            : $basePrice;

        $price    = $proratedBase;
        $discount = null;

        // ── Resolve discount ──────────────────────────────────────────────
        if (! empty($data['discount_code'])) {
            $discount = Discount::findValidCode($data['discount_code'], $data['subscription'], $tenant->id);
            if ($discount) {
                $price = $discount->applyTo($price);
            }
        }

        if (! $discount) {
            $auto = Discount::on('mysql')
                ->where('is_active', true)
                ->where('is_automatic', true)
                ->where(fn($q) => $q->whereNull('plan_slugs')
                                    ->orWhereJsonContains('plan_slugs', $data['subscription']))
                ->where(fn($q) => $q->whereNull('valid_from')
                                    ->orWhereDate('valid_from', '<=', today()))
                ->where(fn($q) => $q->whereNull('valid_until')
                                    ->orWhereDate('valid_until', '>=', today()))
                ->orderByDesc('value')
                ->first();

            if ($auto) {
                $discount = $auto;
                $price    = $auto->applyTo($proratedBase);
            }
        }

        $expiresAt = now()->addDays($durationDays);
        $appliedBy = auth()->id();

        // ── Cancel stale pending renewal requests ─────────────────────────
        $staleSlug = array_keys(
            array_filter($allPlans, fn($rank) => $rank <= $requestedRank)
        );

        RenewalRequest::on('mysql')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->whereIn('plan_slug', $staleSlug)
            ->update([
                'status' => 'cancelled_by_upgrade',
                'notes'  => 'Automatically cancelled — tenant upgraded to a higher plan.',
            ]);

        // ── Write discount usage ──────────────────────────────────────────
        $discountUsageId = null;
        if ($discount) {
            $usage = DiscountUsage::on('mysql')->create([
                'discount_id'     => $discount->id,
                'tenant_id'       => $tenant->id,
                'action'          => 'tenant_upgrade',
                'plan_slug'       => $data['subscription'],
                'original_price'  => $proratedBase,
                'discount_amount' => $discount->discountAmount($proratedBase),
                'final_price'     => $price,
                'applied_by'      => $appliedBy,
            ]);
            $discountUsageId = $usage->id;
        }

        // ── Write subscription history ────────────────────────────────────
        TenantSubscription::on('mysql')->create([
            'tenant_id'         => $tenant->id,
            'plan_slug'         => $data['subscription'],
            'discount_usage_id' => $discountUsageId,
            'amount_paid'       => $price,
            'action'            => 'tenant_upgrade',
            'starts_at'         => now(),
            'expires_at'        => $expiresAt,
            'applied_by'        => $appliedBy,
        ]);

        // ── Update tenant ─────────────────────────────────────────────────
        DB::connection('mysql')->table('tenants')
            ->where('id', $tenant->id)
            ->update([
                'subscription' => $data['subscription'],
                'expires_at'   => $expiresAt,
                'updated_at'   => now(),
            ]);

        $tenant->subscription = $data['subscription'];
        $tenant->expires_at   = $expiresAt;

        // ── Bell notification → tenant admin (tenant DB) ──────────────────
        // auth()->user() is the tenant admin logged into the tenant DB,
        // so a plain Notification::create() correctly targets the tenant DB here.
        try {
            $planName        = $planModel->name;
            $expiryFormatted = $expiresAt->format('F d, Y');
            $discountNote    = $discount
                ? ' A ' . $discount->formatted_value . ' discount was applied.'
                : '';

            Notification::create([
                'user_id' => auth()->id(),
                'title'   => '🎉 Plan Upgraded to ' . $planName . '!',
                'message' => 'Your subscription has been upgraded to the '
                           . $planName . ' Plan.'
                           . $discountNote
                           . ' Access expires on ' . $expiryFormatted . '.'
                           . ' New features are now active.',
                'is_read' => false,
                'link'    => '/admin/subscription',
            ]);
        } catch (\Throwable) {
            // Never fail the upgrade because of a notification error
        }

        // ── Bell notification → all superadmins (central DB) ─────────────
        // IMPORTANT: Must use ::on('mysql') so the notification lands in the
        // central database where the superadmin bell reads from — NOT the
        // tenant DB that is currently active in this request context.
        try {
            $planName        = $planModel->name;
            $expiryFormatted = $expiresAt->format('F d, Y');
            $discountNote    = $discount
                ? ' Discount applied: ' . $discount->formatted_value . '.'
                : '';
            $priceLabel      = $price > 0
                ? ' Final price: ₱' . number_format($price, 2) . '.'
                : '';

            User::on('mysql')->where('role', 'superadmin')
                ->each(function (User $superadmin) use ($tenant, $planName, $expiryFormatted, $discountNote, $priceLabel) {
                    Notification::on('mysql')->create([
                        'user_id' => $superadmin->id,
                        'title'   => '⬆️ Plan Upgraded — ' . $tenant->name,
                        'message' => "'{$tenant->name}' has self-upgraded to the {$planName} Plan."
                                   . $discountNote
                                   . $priceLabel
                                   . " New expiry: {$expiryFormatted}.",
                        'is_read' => false,
                        'link'    => route('superadmin.tenants.index'),
                    ]);
                });
        } catch (\Throwable) {
            // Never fail the upgrade because of a notification error
        }

        return response()->json(['success' => true]);
    }
}
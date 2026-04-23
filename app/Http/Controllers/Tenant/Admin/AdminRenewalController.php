<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Notification;
use App\Models\RenewalRequest;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class AdminRenewalController extends Controller
{
    /**
     * Show the subscription-expired wall.
     */
    public function expired()
    {
        $tenant         = tenancy()->tenant;
        $plans          = SubscriptionPlan::active()->orderBy('sort_order')->get();
        $pendingRequest = RenewalRequest::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return view('tenants.admin.subscription.expired', compact(
            'tenant', 'plans', 'pendingRequest'
        ));
    }

    /**
     * Submit a renewal request.
     *
     * Accepts:
     *   plan_slug      – which plan to renew to
     *   duration_days  – how many days to add (tenant-chosen; overrides plan default)
     *   discount_code  – optional promo code
     */
    public function request(Request $request)
    {
        $data = $request->validate([
            'plan_slug'     => ['required', 'string'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'discount_code' => ['nullable', 'string'],
        ]);

        $tenant    = tenancy()->tenant;
        $planModel = SubscriptionPlan::where('slug', $data['plan_slug'])->firstOrFail();

        // Block duplicate pending requests
        $alreadyPending = RenewalRequest::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending renewal request.',
            ]);
        }

        // ── Resolve duration ──────────────────────────────────────────────
        $durationDays = (int) ($data['duration_days'] ?? $planModel->duration_days ?? 30);
        if ($durationDays < 1) {
            $durationDays = $planModel->duration_days ?? 30;
        }

        // ── Price calculation ─────────────────────────────────────────────
        $basePrice   = $this->priceForDuration($planModel, $durationDays);
        $discount    = null;
        $finalPrice  = $basePrice;
        $discountAmt = 0;

        if (! empty($data['discount_code'])) {
            $discount = Discount::findValidCode(
                $data['discount_code'],
                $data['plan_slug'],
                $tenant->id
            );
            if ($discount) {
                $discountAmt = $discount->discountAmount($basePrice);
                $finalPrice  = $discount->applyTo($basePrice);
            }
        }

        // Also apply automatic discount if no code used
        if (! $discount) {
            $autoDiscount = Discount::on('mysql')
                ->where('is_active', true)
                ->where('is_automatic', true)
                ->where(function ($q) use ($data) {
                    $q->whereNull('plan_slugs')
                      ->orWhereJsonContains('plan_slugs', $data['plan_slug']);
                })
                ->where(function ($q) {
                    $q->whereNull('valid_from')->orWhereDate('valid_from', '<=', today());
                })
                ->where(function ($q) {
                    $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', today());
                })
                ->orderByDesc('value')
                ->first();

            if ($autoDiscount) {
                $discountAmt = $autoDiscount->discountAmount($basePrice);
                $finalPrice  = $autoDiscount->applyTo($basePrice);
            }
        }

        RenewalRequest::create([
            'tenant_id'       => $tenant->id,
            'plan_slug'       => $data['plan_slug'],
            'duration_days'   => $durationDays,
            'discount_code'   => $data['discount_code'] ?? null,
            'original_price'  => $basePrice,
            'discount_amount' => $discountAmt,
            'final_price'     => $finalPrice,
            'status'          => 'pending',
        ]);

        // ── Bell notification → tenant admin (tenant DB) ──────────────────
        try {
            $planName    = $planModel->name;
            $daysLabel   = $this->daysLabel($durationDays);
            $priceLabel  = $finalPrice > 0
                ? ' (₱' . number_format($finalPrice, 2) . ')'
                : '';
            $discountLine = $discountAmt > 0
                ? ' A discount of ₱' . number_format($discountAmt, 2) . ' was applied.'
                : '';

            Notification::create([
                'user_id' => auth()->id(),
                'title'   => '⏳ Renewal Request Submitted',
                'message' => 'Your renewal request for the '
                           . $planName . ' Plan ('
                           . $daysLabel . ')' . $priceLabel
                           . ' has been submitted.'
                           . $discountLine
                           . ' Please wait for super admin approval.',
                'is_read' => false,
                'link'    => '/admin/subscription',
            ]);
        } catch (\Throwable) {
            // Never fail the submission because of a notification error
        }

        // ── Bell notification → all superadmins (central DB) ─────────────
        // IMPORTANT: Must use ::on('mysql') so the notification lands in the
        // central database where the superadmin bell reads from — NOT the
        // tenant DB that is currently active in this request context.
        try {
            $planName    = $planModel->name;
            $daysLabel   = $this->daysLabel($durationDays);
            $priceLabel  = $finalPrice > 0
                ? ' (₱' . number_format($finalPrice, 2) . ')'
                : '';
            $discountLine = $discountAmt > 0
                ? ' Discount applied: ₱' . number_format($discountAmt, 2) . '.'
                : '';

            User::on('mysql')->where('role', 'superadmin')
                ->each(function (User $superadmin) use ($tenant, $planName, $daysLabel, $priceLabel, $discountLine) {
                    Notification::on('mysql')->create([
                        'user_id' => $superadmin->id,
                        'title'   => '🔔 Renewal Request — ' . $tenant->name,
                        'message' => "'{$tenant->name}' has submitted a renewal request for the "
                                   . "{$planName} Plan ({$daysLabel}){$priceLabel}.{$discountLine}"
                                   . ' Please review it in the renewals dashboard.',
                        'is_read' => false,
                        'link'    => route('superadmin.renewals.index'),
                    ]);
                });
        } catch (\Throwable) {
            // Never fail the submission because of a notification error
        }

        return response()->json([
            'success' => true,
            'message' => 'Renewal request submitted. Please wait for super admin approval.',
        ]);
    }

    /**
     * Cancel the tenant's own pending renewal request.
     */
    public function cancel(Request $request)
    {
        $tenant = tenancy()->tenant;

        RenewalRequest::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->delete();

        // ── Bell notification → tenant admin (tenant DB) ──────────────────
        try {
            Notification::create([
                'user_id' => auth()->id(),
                'title'   => '❌ Renewal Request Cancelled',
                'message' => 'Your pending renewal request has been cancelled. '
                           . 'You can submit a new one at any time from the subscription page.',
                'is_read' => false,
                'link'    => '/admin/subscription',
            ]);
        } catch (\Throwable) {
            // Never fail because of a notification error
        }

        // ── Bell notification → all superadmins (central DB) ─────────────
        try {
            User::on('mysql')->where('role', 'superadmin')
                ->each(function (User $superadmin) use ($tenant) {
                    Notification::on('mysql')->create([
                        'user_id' => $superadmin->id,
                        'title'   => '🚫 Renewal Request Cancelled — ' . $tenant->name,
                        'message' => "'{$tenant->name}' has cancelled their pending renewal request.",
                        'is_read' => false,
                        'link'    => route('superadmin.renewals.index'),
                    ]);
                });
        } catch (\Throwable) {
            // Never fail because of a notification error
        }

        return redirect()->back()->with('success', 'Renewal request cancelled.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Pro-rate the plan price proportionally to the requested duration.
     */
    private function priceForDuration(SubscriptionPlan $plan, int $days): float
    {
        if ($plan->duration_days <= 0 || $plan->price <= 0) {
            return (float) $plan->price;
        }

        return round(($plan->price / $plan->duration_days) * $days, 2);
    }

    /**
     * Human-readable label for a day count.
     */
    private function daysLabel(int $days): string
    {
        return match(true) {
            $days === 30  => '1 month',
            $days === 60  => '2 months',
            $days === 90  => '3 months',
            $days === 180 => '6 months',
            $days === 365 => '1 year',
            $days === 730 => '2 years',
            $days % 30 === 0 => ($days / 30) . ' months',
            default       => $days . ' days',
        };
    }
}
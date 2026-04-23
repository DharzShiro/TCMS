<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Tenant;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\DiscountUsage;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * SuperAdminPlanController
 *
 * IMPORTANT — Two completely separate responsibilities:
 *
 * 1. PLAN ASSIGNMENT (applyToTenant)
 *    The superadmin can assign/change a tenant's plan.
 *    A discount code can optionally be attached to record the discounted price paid.
 *
 * 2. DISCOUNT MANAGEMENT (store/update/destroyDiscount)
 *    Discounts are purely pricing tools — they never change a plan on their own.
 */
class SuperAdminPlanController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $plans     = SubscriptionPlan::orderBy('sort_order')->get();
        $discounts = Discount::latest()->get();
        $tenants   = Tenant::orderBy('name')->get();

        return view('superadmin.plans.index', compact('plans', 'discounts', 'tenants'));
    }

    // ── Plan CRUD ─────────────────────────────────────────────────────────────

    public function create()
    {
        return view('superadmin.plans.manage', ['plan' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                     => ['required', 'string', 'max:100'],
            'icon'                     => ['required', 'string', 'max:20'],
            // Slug is free-form but must be unique across plans
            'slug'                     => ['required', 'string', 'max:50', 'alpha_dash', 'unique:subscription_plans,slug'],
            'description'              => ['nullable', 'string'],
            'price'                    => ['required', 'numeric', 'min:0'],
            'duration_days'            => ['required', 'integer', 'min:1'],
            'max_trainees'             => ['nullable', 'integer', 'min:1'],
            'max_trainers'             => ['nullable', 'integer', 'min:1'],
            'max_users'                => ['nullable', 'integer', 'min:1'],
            'max_courses'              => ['nullable', 'integer', 'min:1'],
            'max_exports_monthly'      => ['nullable', 'integer', 'min:0'],
            'allowed_export_formats'   => ['nullable', 'array'],
            'allowed_export_formats.*' => ['in:csv,excel,pdf'],
            'has_assessments'          => ['boolean'],
            'has_certificates'         => ['boolean'],
            'has_custom_reports'       => ['boolean'],
            'has_branding'             => ['boolean'],
            'has_trainers'             => ['boolean'],
            'is_active'                => ['boolean'],
            'available_from'           => ['nullable', 'date'],
            'available_until'          => ['nullable', 'date', 'after_or_equal:available_from'],
            'sort_order'               => ['integer', 'min:0'],
        ]);

        // Normalize slug: lowercase, hyphens
        $data['slug'] = Str::slug($data['slug']);

        // Convert nulls for "unlimited" fields
        foreach (['max_trainees', 'max_trainers', 'max_users', 'max_courses', 'max_exports_monthly'] as $field) {
            $data[$field] = $request->filled($field) ? (int) $data[$field] : null;
        }

        $data['allowed_export_formats'] = $request->input('allowed_export_formats', []);
        $data['has_assessments']        = $request->boolean('has_assessments');
        $data['has_certificates']       = $request->boolean('has_certificates');
        $data['has_custom_reports']     = $request->boolean('has_custom_reports');
        $data['has_branding']           = $request->boolean('has_branding');
        $data['has_trainers']           = $request->boolean('has_trainers');
        $data['is_active']              = $request->boolean('is_active', true);
        $data['sort_order']             = (int) $request->input('sort_order', 0);

        SubscriptionPlan::create($data);

        return redirect()->route('superadmin.plans.index')
            ->with('success', "Plan \"{$data['name']}\" created successfully.");
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('superadmin.plans.manage', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $data = $request->validate([
            'name'                     => ['required', 'string', 'max:100'],
            'icon'                     => ['required', 'string', 'max:20'],
            // Slug must be unique but ignore this plan's own slug
            'slug'                     => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('subscription_plans', 'slug')->ignore($plan->id)],
            'description'              => ['nullable', 'string'],
            'price'                    => ['required', 'numeric', 'min:0'],
            'duration_days'            => ['required', 'integer', 'min:1'],
            'max_trainees'             => ['nullable', 'integer', 'min:1'],
            'max_trainers'             => ['nullable', 'integer', 'min:1'],
            'max_users'                => ['nullable', 'integer', 'min:1'],
            'max_courses'              => ['nullable', 'integer', 'min:1'],
            'max_exports_monthly'      => ['nullable', 'integer', 'min:0'],
            'allowed_export_formats'   => ['nullable', 'array'],
            'allowed_export_formats.*' => ['in:csv,excel,pdf'],
            'has_assessments'          => ['boolean'],
            'has_certificates'         => ['boolean'],
            'has_custom_reports'       => ['boolean'],
            'has_branding'             => ['boolean'],
            'has_trainers'             => ['boolean'],
            'is_active'                => ['boolean'],
            'available_from'           => ['nullable', 'date'],
            'available_until'          => ['nullable', 'date', 'after_or_equal:available_from'],
            'sort_order'               => ['integer', 'min:0'],
        ]);

        $data['slug'] = Str::slug($data['slug']);

        // Convert nulls for "unlimited" fields
        foreach (['max_trainees', 'max_trainers', 'max_users', 'max_courses', 'max_exports_monthly'] as $field) {
            $data[$field] = $request->filled($field) ? (int) $data[$field] : null;
        }

        $data['allowed_export_formats'] = $request->input('allowed_export_formats', []);
        $data['has_assessments']        = $request->boolean('has_assessments');
        $data['has_certificates']       = $request->boolean('has_certificates');
        $data['has_custom_reports']     = $request->boolean('has_custom_reports');
        $data['has_branding']           = $request->boolean('has_branding');
        $data['has_trainers']           = $request->boolean('has_trainers');
        $data['is_active']              = $request->boolean('is_active', true);
        $data['sort_order']             = (int) $request->input('sort_order', 0);

        $plan->update($data);

        return redirect()->route('superadmin.plans.index')
            ->with('success', "Plan \"{$plan->name}\" updated successfully.");
    }

    public function destroy(SubscriptionPlan $plan)
    {
        $name = $plan->name;
        $plan->delete();

        return redirect()->route('superadmin.plans.index')
            ->with('success', "Plan \"{$name}\" deleted.");
    }

    // ── Plan Assignment ───────────────────────────────────────────────────────

    /**
     * Assign a plan to a tenant (and optionally record a discounted price).
     */
    public function applyToTenant(Request $request)
    {
        // Get all valid plan slugs dynamically
        $validSlugs = SubscriptionPlan::pluck('slug')->toArray();

        $data = $request->validate([
            'tenant_id'     => ['required', 'exists:tenants,id'],
            'plan_slug'     => ['required', Rule::in($validSlugs)],
            'discount_code' => ['nullable', 'string'],
        ]);

        $tenant    = Tenant::findOrFail($data['tenant_id']);
        $planModel = SubscriptionPlan::where('slug', $data['plan_slug'])->firstOrFail();
        $price     = (float) $planModel->price;
        $discount  = null;

        if (! empty($data['discount_code'])) {
            $discount = Discount::findValidCode($data['discount_code'], $data['plan_slug'], $tenant->id);

            if (! $discount) {
                return back()->withErrors(['discount_code' => 'Invalid or inapplicable discount code.']);
            }

            $price = $discount->applyTo($price);
        }

        $expiresAt = $planModel->getExpiresAt();

        DB::transaction(function () use ($tenant, $data, $planModel, $discount, $price, $expiresAt) {
            $basePrice       = (float) $planModel->price;
            $discountUsageId = null;

            if ($discount) {
                $usage = DiscountUsage::create([
                    'discount_id'     => $discount->id,
                    'tenant_id'       => $tenant->id,
                    'action'          => 'superadmin_assign',
                    'plan_slug'       => $data['plan_slug'],
                    'original_price'  => $basePrice,
                    'discount_amount' => $discount->discountAmount($basePrice),
                    'final_price'     => $price,
                    'applied_by'      => auth()->id(),
                ]);
                $discountUsageId = $usage->id;
            }

            TenantSubscription::create([
                'tenant_id'         => $tenant->id,
                'plan_slug'         => $data['plan_slug'],
                'discount_usage_id' => $discountUsageId,
                'amount_paid'       => $price,
                'action'            => 'superadmin_assign',
                'starts_at'         => now(),
                'expires_at'        => $expiresAt,
                'applied_by'        => auth()->id(),
            ]);

            $tenant->subscription = $data['plan_slug'];
            $tenant->expires_at   = $expiresAt;
            $tenant->status       = 'approved';
            $tenant->is_active    = true;
            $tenant->save();
        });

        $msg = "Plan set to {$planModel->name} for {$tenant->name}.";
        if ($discount) {
            $msg .= " Discount {$discount->code} applied — recorded price ₱" . number_format($price, 2) . '.';
        }

        return back()->with('success', $msg);
    }

    // ── Discount Management ───────────────────────────────────────────────────

    public function storeDiscount(Request $request)
    {
        $validSlugs = SubscriptionPlan::pluck('slug')->toArray();

        $data = $request->validate([
            'code'         => ['required_if:is_automatic,0', 'nullable', 'string', 'max:50', 'unique:discounts,code'],
            'label'        => ['required', 'string', 'max:150'],
            'type'         => ['required', 'in:percentage,fixed'],
            'value'        => ['required', 'numeric', 'min:0.01'],
            'plan_slugs'   => ['nullable', 'array'],
            'plan_slugs.*' => [Rule::in($validSlugs)],
            'tenant_ids'   => ['nullable', 'array'],
            'tenant_ids.*' => ['exists:tenants,id'],
            'valid_from'   => ['nullable', 'date'],
            'valid_until'  => ['nullable', 'date', 'after_or_equal:valid_from'],
            'is_active'    => ['boolean'],
            'is_automatic' => ['boolean'],
        ]);

        if ($data['type'] === 'percentage' && $data['value'] > 100) {
            return back()->withInput()->withErrors(['value' => 'Percentage cannot exceed 100.']);
        }

        $isAutomatic = $request->boolean('is_automatic', false);

        if ($isAutomatic) {
            $data['code']       = 'AUTO-' . strtoupper(uniqid());
            $data['tenant_ids'] = null;
        } else {
            $data['code'] = strtoupper($data['code'] ?? '');
            $tenantIds          = $request->input('tenant_ids', []);
            $data['tenant_ids'] = (is_array($tenantIds) && count($tenantIds) > 0) ? $tenantIds : null;
        }

        $planSlugs = $request->input('plan_slugs', []);
        $data['plan_slugs']   = (is_array($planSlugs) && count($planSlugs) > 0) ? $planSlugs : null;
        $data['is_active']    = $request->boolean('is_active', true);
        $data['is_automatic'] = $isAutomatic;

        Discount::create($data);

        $typeLabel = $isAutomatic ? 'Automatic discount' : 'Promo code "' . $data['code'] . '"';
        return back()->with('success', $typeLabel . ' created successfully.');
    }

    public function updateDiscount(Request $request, Discount $discount)
    {
        $validSlugs = SubscriptionPlan::pluck('slug')->toArray();

        $data = $request->validate([
            'code'         => [
                'nullable', 'string', 'max:50',
                Rule::unique('discounts', 'code')->ignore($discount->id),
            ],
            'label'        => ['required', 'string', 'max:150'],
            'type'         => ['required', 'in:percentage,fixed'],
            'value'        => ['required', 'numeric', 'min:0.01'],
            'plan_slugs'   => ['nullable', 'array'],
            'plan_slugs.*' => [Rule::in($validSlugs)],
            'tenant_ids'   => ['nullable', 'array'],
            'tenant_ids.*' => ['exists:tenants,id'],
            'valid_from'   => ['nullable', 'date'],
            'valid_until'  => ['nullable', 'date', 'after_or_equal:valid_from'],
            'is_active'    => ['boolean'],
            'is_automatic' => ['boolean'],
        ]);

        if ($data['type'] === 'percentage' && $data['value'] > 100) {
            return back()->withInput()->withErrors(['value' => 'Percentage cannot exceed 100.']);
        }

        $isAutomatic = $request->boolean('is_automatic', false);
        $data['is_active']    = $request->boolean('is_active', true);
        $data['is_automatic'] = $isAutomatic;

        $planSlugs = $request->input('plan_slugs', []);
        $data['plan_slugs'] = (is_array($planSlugs) && count($planSlugs) > 0) ? $planSlugs : null;

        if ($isAutomatic) {
            unset($data['code']);
            $data['tenant_ids'] = null;
        } else {
            $data['code'] = strtoupper($data['code'] ?? $discount->code);
            $tenantIds          = $request->input('tenant_ids', []);
            $data['tenant_ids'] = (is_array($tenantIds) && count($tenantIds) > 0) ? $tenantIds : null;
        }

        $discount->update($data);

        return back()->with('success', 'Discount updated successfully.');
    }

    public function destroyDiscount(Discount $discount)
    {
        $label = $discount->is_automatic ? $discount->label : $discount->code;
        $discount->delete();

        return back()->with('success', 'Discount "' . $label . '" deleted.');
    }

    // ── AJAX: validate a code-based discount ─────────────────────────────────

    public function validateCode(Request $request)
    {
        $request->validate([
            'code'      => ['required', 'string'],
            'plan_slug' => ['required', 'string'],
        ]);

        $tenantId = $request->input('tenant_id') ?? optional(tenancy()->tenant)->id;

        $discount = Discount::findValidCode($request->code, $request->plan_slug, $tenantId);

        if (! $discount) {
            return response()->json([
                'valid'   => false,
                'message' => 'Invalid or inapplicable promo code.',
            ]);
        }

        $planModel = SubscriptionPlan::where('slug', $request->plan_slug)->firstOrFail();
        $base      = (float) $planModel->price;
        $saved     = $discount->discountAmount($base);
        $final     = $discount->applyTo($base);

        return response()->json([
            'valid'           => true,
            'formatted_value' => $discount->formatted_value,
            'original_price'  => $base,
            'discount_amount' => $saved,
            'final_price'     => $final,
        ]);
    }
}
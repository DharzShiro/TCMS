<?php

namespace App\Helpers;
use App\Models\SubscriptionPlan;

class SubscriptionHelper
{
    /**
     * Feature map: which MINIMUM plan slug a feature requires.
     * For custom plans beyond the 3 canonical ones, access is based on
     * the plan's sort_order compared to the required plan's sort_order.
     */
    protected static array $featurePlans = [
        // Available on all plans (sort_order 0 equivalent)
        'trainees'           => 'basic',
        'courses'            => 'basic',
        'enrollments'        => 'basic',
        'attendances'        => 'basic',

        // Standard+
        'trainers'           => 'standard',
        'assessments'        => 'standard',
        'training-schedules' => 'standard',
        'users'              => 'standard',
        'reports'            => 'standard',

        // Premium only
        'certificates'       => 'premium',
        'custom-reports'     => 'premium',
        'branding'           => 'premium',
    ];

    /**
     * Get all plans ordered by sort_order, cached per request.
     */
    protected static ?array $planOrder = null;

    protected static function getPlanOrder(): array
    {
        if (static::$planOrder !== null) return static::$planOrder;

        static::$planOrder = SubscriptionPlan::orderBy('sort_order')
            ->pluck('sort_order', 'slug')
            ->toArray();

        return static::$planOrder;
    }

    /**
     * Returns true if $currentPlan has access to $feature.
     * Comparison is based on sort_order in the DB, not hardcoded position.
     */
    public static function canAccess(string $currentPlan, string $feature): bool
    {
        $required     = static::$featurePlans[$feature] ?? 'basic';
        $planOrder    = static::getPlanOrder();

        $currentIndex  = $planOrder[$currentPlan]  ?? null;
        $requiredIndex = $planOrder[$required]      ?? 0;

        if ($currentIndex === null) return false;

        return $currentIndex >= $requiredIndex;
    }

    public static function getLimit(string $plan, string $resource): ?int
    {
        $planModel = SubscriptionPlan::where('slug', $plan)->first();

        if (! $planModel) return 0;

        return match($resource) {
            'trainees'        => $planModel->max_trainees,
            'trainers'        => $planModel->max_trainers,
            'users'           => $planModel->max_users,
            'courses'         => $planModel->max_courses,
            'exports_monthly' => $planModel->max_exports_monthly,
            default           => null,
        };
    }

    /**
     * Returns true if adding one more of $resource is allowed.
     */
    public static function canAddMore(string $plan, string $resource, int $currentCount): bool
    {
        $limit = static::getLimit($plan, $resource);
        if ($limit === null) return true;   // unlimited
        if ($limit === 0)    return false;  // not available on this plan
        return $currentCount < $limit;
    }

    /**
     * Returns the allowed export formats for a given plan.
     */
    public static function getAllowedExportFormats(string $plan): array
    {
        $planModel = SubscriptionPlan::where('slug', $plan)->first();
        return $planModel?->allowed_export_formats ?? [];
    }

    /**
     * Returns true if the plan can export at all.
     */
    public static function canExport(string $plan): bool
    {
        $formats = static::getAllowedExportFormats($plan);
        return count($formats) > 0;
    }
}
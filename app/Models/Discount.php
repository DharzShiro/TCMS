<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    // ── CRITICAL: discounts live in the central DB, not tenant DBs ───────────
    protected $connection = 'mysql';

    protected $fillable = [
        'code', 'label', 'type', 'value', 'plan_slugs',
        'tenant_ids',
        'valid_from', 'valid_until', 'is_active', 'is_automatic',
    ];

    protected $casts = [
        'valid_from'   => 'date',
        'valid_until'  => 'date',
        'is_active'    => 'boolean',
        'is_automatic' => 'boolean',
        'value'        => 'decimal:2',
        'plan_slugs'   => 'array',   // JSON array e.g. ["standard","premium"] or null = all plans
        'tenant_ids'   => 'array',   // JSON array of tenant IDs or null = any tenant (promo codes only)
    ];

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValidNow($query)
    {
        $today = now()->toDateString();
        return $query->active()
            ->where(fn ($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', $today))
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', $today));
    }

    /**
     * Filter discounts that apply to a given plan slug.
     * plan_slugs = null  → applies to ALL plans
     * plan_slugs = [...] → applies only to the listed plans
     */
    public function scopeForPlan($query, string $plan)
    {
        return $query->where(function ($q) use ($plan) {
            $q->whereNull('plan_slugs')
              ->orWhereJsonContains('plan_slugs', $plan);
        });
    }

    /** Automatic discounts: shown on plan cards with no code entry needed. */
    public function scopeAutomatic($query)
    {
        return $query->where('is_automatic', true);
    }

    /** Code-based discounts: tenant must type the code manually. */
    public function scopeCodeBased($query)
    {
        return $query->where('is_automatic', false);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Returns true when this discount is currently valid for a given plan
     * and optionally a given tenant ID.
     *
     * NOTE: A discount NEVER changes a plan — it only affects price display/recording.
     */
    public function isValidFor(string $plan, ?string $tenantId = null): bool
    {
        if (! $this->is_active) return false;

        // plan_slugs = null means discount applies to ALL plans
        if (! empty($this->plan_slugs) && ! in_array($plan, $this->plan_slugs)) return false;

        // tenant_ids = null means discount applies to ANY tenant (promo codes only)
        if (! empty($this->tenant_ids) && $tenantId !== null && ! in_array($tenantId, $this->tenant_ids)) {
            return false;
        }

        $today = now()->toDateString();
        if ($this->valid_from  && $this->valid_from->toDateString()  > $today) return false;
        if ($this->valid_until && $this->valid_until->toDateString() < $today) return false;

        return true;
    }

    /** Calculate the discount amount off a base price. */
    public function discountAmount(float $price): float
    {
        if ($this->type === 'percentage') {
            return round($price * ($this->value / 100), 2);
        }
        return min((float) $this->value, $price);
    }

    /** Apply the discount and return the final price. */
    public function applyTo(float $price): float
    {
        return max(0, $price - $this->discountAmount($price));
    }

    /** Formatted value string for display. */
    public function getFormattedValueAttribute(): string
    {
        return $this->type === 'percentage'
            ? number_format($this->value, 0) . '%'
            : '₱' . number_format($this->value, 2);
    }

    /**
     * Human-readable label for which plans this discount applies to.
     * e.g. "All plans", "Standard", "Standard, Premium"
     */
    public function getPlanLabelAttribute(): string
    {
        if (empty($this->plan_slugs)) return 'All plans';
        return implode(', ', array_map('ucfirst', $this->plan_slugs));
    }

    /**
     * Human-readable label for which tenants this promo code is restricted to.
     */
    public function getTenantLabelAttribute(): string
    {
        if ($this->is_automatic || empty($this->tenant_ids)) return 'All tenants';

        $names = \App\Models\Tenant::whereIn('id', $this->tenant_ids)
            ->pluck('name')
            ->toArray();

        return count($names) ? implode(', ', $names) : 'All tenants';
    }

    /** Status for display in the table. */
    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) return 'Inactive';

        $today = now()->toDateString();
        if ($this->valid_from  && $this->valid_from->toDateString()  > $today) return 'Scheduled';
        if ($this->valid_until && $this->valid_until->toDateString() < $today) return 'Expired';

        return 'Active';
    }

    /**
     * Find the best active automatic discount for a given plan slug.
     * Forces central DB connection — safe to call from within a tenant context.
     */
    public static function bestAutomaticFor(string $planSlug): ?self
    {
        return static::on('mysql')
            ->validNow()
            ->automatic()
            ->forPlan($planSlug)
            ->orderByDesc('value')
            ->first();
    }

    /**
     * Find a valid code-based discount by code + plan + optional tenant.
     * Forces central DB connection. Rejects automatic discounts (can't be typed in).
     */
    public static function findValidCode(string $code, string $planSlug, ?string $tenantId = null): ?self
    {
        $discount = static::on('mysql')
            ->where('code', strtoupper($code))
            ->first();

        if (! $discount || $discount->is_automatic) return null;
        if (! $discount->isValidFor($planSlug, $tenantId)) return null;

        return $discount;
    }
}
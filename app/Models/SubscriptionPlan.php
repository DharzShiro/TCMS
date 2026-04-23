<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $connection = 'mysql';
    
    protected $fillable = [
        'slug',
        'name',
        'icon',
        'description',
        'price',
        'currency',
        'duration_days',
        'max_trainees',
        'max_trainers',
        'max_users',
        'max_courses',
        'max_exports_monthly',
        'allowed_export_formats',
        'has_assessments',
        'has_certificates',
        'has_custom_reports',
        'has_branding',
        'has_trainers',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'allowed_export_formats' => 'array',
        'has_assessments'        => 'boolean',
        'has_certificates'       => 'boolean',
        'has_custom_reports'     => 'boolean',
        'has_branding'           => 'boolean',
        'has_trainers'           => 'boolean',
        'is_active'              => 'boolean',
        'price'                  => 'decimal:2',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function getFormattedPriceAttribute(): string
    {
        return '₱' . number_format($this->price, 2);
    }

    public function getDurationLabelAttribute(): string
    {
        return match(true) {
            $this->duration_days === 30  => '30 days',
            $this->duration_days === 180 => '6 months',
            $this->duration_days === 365 => '1 year',
            default                      => $this->duration_days . ' days',
        };
    }

    /**
     * Returns the expiry Carbon date that should be set when this plan is applied.
     */
    public function getExpiresAt(): \Carbon\Carbon
    {
        return now()->addDays($this->duration_days);
    }

    public function discounts()
    {
        return Discount::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('applicable_plans')
                  ->orWhereJsonContains('applicable_plans', $this->slug);
            })
            ->get();
    }
}
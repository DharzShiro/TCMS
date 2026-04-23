<?php
// app/Models/Tenant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasFactory, HasDatabase, HasDomains;

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Only auto-generate an ID if one hasn't already been set.
            // When SuperAdminController passes a name-based ID (e.g.
            // "makati_training_center"), that value is used as-is so the
            // tenant database gets a human-readable name.
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = [
        'id',
        'name',
        'admin_email',
        'subdomain',
        'subscription',
        'status',
        'is_active',            
        'brand_name',
        'brand_logo',
        'brand_color_primary',
        'brand_color_accent',
        'brand_tagline',
        'expires_at',
        'data',
    ];

    protected $casts = [
        'data'       => 'array',
        'expires_at' => 'datetime',
        'is_active'  => 'boolean', 
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'admin_email',
            'subdomain',
            'subscription',
            'status',
            'is_active',            
            'brand_name',
            'brand_logo',
            'brand_color_primary',
            'brand_color_accent',
            'brand_tagline',
            'expires_at',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function hasSubscription(string $type): bool
    {
        return $this->subscription === $type;
    }

    public function isSubscribed(): bool
    {
        return $this->status === 'approved' &&
               (! $this->expires_at || $this->expires_at->isFuture());
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription', 'slug');
    }
    
    public function usageStat()
    {
        return $this->hasOne(\App\Models\TenantUsageStat::class, 'tenant_id', 'id');
    }
}
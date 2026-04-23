<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantSubscription extends Model
{
    protected $connection = 'mysql'; // central DB — must be explicit

    protected $fillable = [
        'tenant_id', 'plan_slug', 'discount_usage_id', 'amount_paid',
        'action', 'starts_at', 'expires_at', 'applied_by',
    ];

    protected $casts = [
        'starts_at'  => 'datetime',
        'expires_at' => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function discountUsage()
    {
        return $this->belongsTo(DiscountUsage::class);
    }

    public function appliedBy()
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}
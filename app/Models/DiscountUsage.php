<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountUsage extends Model
{
    protected $fillable = [
        'discount_id',
        'tenant_id',
        'action',
        'plan_slug',
        'original_price',
        'discount_amount',
        'final_price',
        'applied_by',
    ];

    protected $casts = [
        'original_price'  => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price'     => 'decimal:2',
    ];

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function appliedBy()
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}
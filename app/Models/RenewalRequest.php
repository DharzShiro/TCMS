<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RenewalRequest extends Model
{
    protected $connection = 'mysql';

    /**
     * Valid status values:
     *   pending              – awaiting superadmin review
     *   approved             – superadmin approved, expiry extended
     *   rejected             – superadmin rejected
     *   cancelled_by_upgrade – automatically voided because the tenant
     *                          upgraded to a higher plan before approval
     */
    protected $fillable = [
        'tenant_id', 'plan_slug', 'duration_days', 'discount_code',
        'original_price', 'discount_amount', 'final_price',
        'status', 'reviewed_by', 'reviewed_at', 'notes',
    ];

    protected $casts = [
        'duration_days'   => 'integer',
        'original_price'  => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price'     => 'decimal:2',
        'reviewed_at'     => 'datetime',
    ];

    public function tenant()   { return $this->belongsTo(Tenant::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function isPending()           { return $this->status === 'pending'; }
    public function isApproved()          { return $this->status === 'approved'; }
    public function isRejected()          { return $this->status === 'rejected'; }
    public function isCancelledByUpgrade(){ return $this->status === 'cancelled_by_upgrade'; }

    /**
     * Calculate the new expires_at when this renewal is approved.
     *
     * Always extends FROM the later of (now, current expiry) so the
     * tenant never loses remaining paid time.
     */
    public function calculateNewExpiry(Tenant $tenant): \Carbon\Carbon
    {
        $days = $this->duration_days > 0
            ? $this->duration_days
            : optional(SubscriptionPlan::where('slug', $this->plan_slug)->first())->duration_days
              ?? 30;

        $base = ($tenant->expires_at && $tenant->expires_at->isFuture())
            ? $tenant->expires_at
            : now();

        return $base->copy()->addDays($days);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'tenant_id',
        'tenant_name',
        'user_id',
        'user_name',
        'user_email',
        'role',
        'action',
        'ip_address',
        'user_agent',
        'success',
        'failure_reason',
    ];

    protected $casts = [
        'success' => 'boolean',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'login_success' => 'Logged In',
            'login_failed'  => 'Failed Login',
            'logout'        => 'Logged Out',
            default         => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'login_success' => 'green',
            'login_failed'  => 'red',
            'logout'        => 'blue',
            default         => 'gray',
        };
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantVersionStatus extends CentralModel
{
    protected $fillable = [
        'tenant_id',
        'current_version',
        'latest_version',
        'update_status',
        'failure_reason',
        'last_checked_at',
        'last_updated_at',
        'applied_releases',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
        'last_updated_at' => 'datetime',
        'applied_releases' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function isUpToDate(): bool
    {
        return $this->update_status === 'up_to_date';
    }

    public function isUpdating(): bool
    {
        return in_array($this->update_status, ['queued', 'running']);
    }

    public function needsUpdate(): bool
    {
        return $this->update_status === 'update_available';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->update_status) {
            'up_to_date'       => '<span class="badge-green">Up to Date</span>',
            'update_available' => '<span class="badge-yellow">Update Available</span>',
            'queued'           => '<span class="badge-blue">Queued</span>',
            'running'          => '<span class="badge-blue">Updating…</span>',
            'completed'        => '<span class="badge-green">Completed</span>',
            'failed'           => '<span class="badge-red">Failed</span>',
            'skipped'          => '<span class="badge-gray">Skipped</span>',
            default            => '<span class="badge-gray">Unknown</span>',
        };
    }
}

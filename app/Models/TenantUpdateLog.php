<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUpdateLog extends CentralModel
{
    protected $fillable = [
        'tenant_id',
        'release_id',
        'from_version',
        'to_version',
        'status',
        'triggered_by',
        'output',
        'failure_reason',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function release(): BelongsTo
    {
        return $this->belongsTo(SystemRelease::class, 'release_id');
    }

    public function getDurationAttribute(): ?string
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        $seconds = $this->started_at->diffInSeconds($this->completed_at);

        return $seconds < 60
            ? "{$seconds}s"
            : $this->started_at->diffInMinutes($this->completed_at) . 'm';
    }
}

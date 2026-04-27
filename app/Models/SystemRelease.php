<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemRelease extends CentralModel
{
    protected $fillable = [
        'github_id',
        'tag_name',
        'version',
        'name',
        'body',
        'is_prerelease',
        'is_active',
        'is_deployed',
        'github_url',
        'download_url',
        'manifest',
        'published_at',
    ];

    protected $casts = [
        'is_prerelease' => 'boolean',
        'is_active'     => 'boolean',
        'is_deployed'   => 'boolean',
        'manifest'      => 'array',
        'published_at'  => 'datetime',
    ];

    public function tenantUpdateLogs(): HasMany
    {
        return $this->hasMany(TenantUpdateLog::class, 'release_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDeployed($query)
    {
        return $query->where('is_deployed', true);
    }

    public function scopeSemverDesc($query)
    {
        return $query->orderByRaw('
            CAST(SUBSTRING_INDEX(version, ".", 1) AS UNSIGNED) DESC,
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(version, ".", 2), ".", -1) AS UNSIGNED) DESC,
            CAST(SUBSTRING_INDEX(version, ".", -1) AS UNSIGNED) DESC
        ');
    }

    /** Newest release with is_active=true — use for tenant update targeting. */
    public static function latestActive(): ?self
    {
        return static::active()->semverDesc()->first();
    }

    /** Newest release that is both active AND deployed — use for "production version" display. */
    public static function latestDeployed(): ?self
    {
        return static::active()->deployed()->semverDesc()->first();
    }

    public function isNewerThan(string $version): bool
    {
        return version_compare($this->version, $version, '>');
    }
}

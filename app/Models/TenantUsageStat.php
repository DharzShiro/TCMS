<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantUsageStat extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id',
        'db_size_bytes',
        'file_size_bytes',
        'bandwidth_bytes_today',
        'bandwidth_bytes_total',
        'bandwidth_date',
        'last_calculated_at',
    ];

    protected $casts = [
        'bandwidth_date'      => 'date',
        'last_calculated_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // ── Computed ──────────────────────────────────────────────────────────

    public function getTotalStorageBytesAttribute(): int
    {
        return $this->db_size_bytes + $this->file_size_bytes;
    }

    public function getFormattedDbSizeAttribute(): string
    {
        return self::formatBytes($this->db_size_bytes);
    }

    public function getFormattedFileSizeAttribute(): string
    {
        return self::formatBytes($this->file_size_bytes);
    }

    public function getFormattedTotalStorageAttribute(): string
    {
        return self::formatBytes($this->total_storage_bytes);
    }

    public function getFormattedBandwidthTodayAttribute(): string
    {
        return self::formatBytes($this->bandwidth_bytes_today);
    }

    public function getFormattedBandwidthTotalAttribute(): string
    {
        return self::formatBytes($this->bandwidth_bytes_total);
    }

    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow   = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
    }
}
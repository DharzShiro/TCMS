<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class HostsFileService
{
    private const MARKER_START = '# === TCM TENANTS START ===';
    private const MARKER_END   = '# === TCM TENANTS END ===';

    private string $hostsPath;

    public function __construct()
    {
        $this->hostsPath = PHP_OS_FAMILY === 'Windows'
            ? 'C:\\Windows\\System32\\drivers\\etc\\hosts'
            : '/etc/hosts';
    }

    /**
     * Add a single tenant subdomain entry to the hosts file.
     * Called by SuperAdminController::approve() right after the tenant is approved.
     */
    public function addTenantEntry(string $subdomain, string $tenantName = ''): bool
    {
        try {
            $current = $this->read();
            $block   = $this->parseBlock($current);

            $domain = $subdomain . '.tcm.com';

            // Skip if already present
            if (str_contains($block, $domain)) {
                Log::info("[HostsFileService] Entry already exists for {$domain}, skipping.");
                return true;
            }

            $comment  = $tenantName ? "   # {$tenantName}" : '';
            $newLine  = "127.0.0.1 {$domain}{$comment}";
            $block   .= PHP_EOL . $newLine;

            return $this->writeBlock($current, $block);

        } catch (\Throwable $e) {
            Log::warning("[HostsFileService] addTenantEntry failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove a tenant's entry (called on tenant deletion or rejection).
     */
    public function removeTenantEntry(string $subdomain): bool
    {
        try {
            $current = $this->read();
            $block   = $this->parseBlock($current);
            $domain  = $subdomain . '.tcm.com';

            // Remove every line that mentions this domain
            $lines    = explode(PHP_EOL, $block);
            $filtered = array_filter($lines, fn($line) => ! str_contains($line, $domain));
            $newBlock = implode(PHP_EOL, $filtered);

            return $this->writeBlock($current, $newBlock);

        } catch (\Throwable $e) {
            Log::warning("[HostsFileService] removeTenantEntry failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Re-sync every approved tenant at once (used by the Artisan command).
     */
    public function syncAll(array $tenants): bool
    {
        try {
            $current = $this->read();
            $lines   = [];

            foreach ($tenants as $tenant) {
                $domain  = $tenant['subdomain'] . '.tcm.com';
                $comment = isset($tenant['name']) ? "   # {$tenant['name']}" : '';
                $lines[] = "127.0.0.1 {$domain}{$comment}";
            }

            $block = implode(PHP_EOL, $lines);
            return $this->writeBlock($current, $block);

        } catch (\Throwable $e) {
            Log::warning("[HostsFileService] syncAll failed: " . $e->getMessage());
            return false;
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function read(): string
    {
        if (! file_exists($this->hostsPath)) {
            throw new \RuntimeException("Hosts file not found: {$this->hostsPath}");
        }

        $content = file_get_contents($this->hostsPath);

        if ($content === false) {
            throw new \RuntimeException("Cannot read hosts file — run PHP/your terminal as Administrator.");
        }

        return $content;
    }

    /**
     * Extract the lines between our markers (without the markers themselves).
     * Returns an empty string if the block doesn't exist yet.
     */
    private function parseBlock(string $hostsContent): string
    {
        $pattern = '/' . preg_quote(self::MARKER_START, '/') . '\r?\n(.*?)\r?\n' . preg_quote(self::MARKER_END, '/') . '/s';

        if (preg_match($pattern, $hostsContent, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    /**
     * Replace (or insert) the managed block inside the hosts file.
     */
    private function writeBlock(string $hostsContent, string $block): bool
    {
        $managed = self::MARKER_START . PHP_EOL
                 . '127.0.0.1 tcm.com' . PHP_EOL   // always keep central domain
                 . trim($block) . PHP_EOL
                 . self::MARKER_END;

        // Strip old managed block if present
        $pattern = '/' . preg_quote(self::MARKER_START, '/') . '.*?' . preg_quote(self::MARKER_END, '/') . '\r?\n?/s';
        $cleaned = preg_replace($pattern, '', $hostsContent);
        $updated = rtrim($cleaned) . PHP_EOL . PHP_EOL . $managed . PHP_EOL;

        $result = file_put_contents($this->hostsPath, $updated);

        if ($result === false) {
            throw new \RuntimeException("Cannot write to hosts file — run PHP/your terminal as Administrator.");
        }

        Log::info("[HostsFileService] Hosts file updated successfully.");
        return true;
    }
}
<?php

namespace App\Services\Updates;

use App\Models\SystemRelease;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubReleaseService
{
    private string $baseUrl = 'https://api.github.com';
    private string $owner;
    private string $repo;
    private ?string $token;
    private bool $includePrereleases;

    public function __construct()
    {
        $this->owner              = config('github.owner');
        $this->repo               = config('github.repo');
        $this->token              = config('github.token');
        $this->includePrereleases = config('github.include_prereleases', false);
    }

    public function fetchReleases(): array
    {
        if (empty($this->owner) || empty($this->repo)) {
            Log::warning('[GitHub] GITHUB_OWNER or GITHUB_REPO not configured.');
            return [];
        }

        $response = Http::withHeaders($this->headers())
            ->timeout(15)
            ->get("{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/releases", [
                'per_page' => config('github.max_stored_releases', 20),
            ]);

        if (! $response->successful()) {
            Log::error('[GitHub] Failed to fetch releases', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        }

        return $response->json() ?? [];
    }

    public function syncToDatabase(): int
    {
        $releases = $this->fetchReleases();
        $synced   = 0;

        foreach ($releases as $release) {
            if (! $this->includePrereleases && $release['prerelease']) {
                continue;
            }

            $version = ltrim($release['tag_name'], 'v');

            // Skip if not a valid semver-ish string
            if (! preg_match('/^\d+\.\d+/', $version)) {
                continue;
            }

            SystemRelease::updateOrCreate(
                ['github_id' => (string) $release['id']],
                [
                    'tag_name'      => $release['tag_name'],
                    'version'       => $version,
                    'name'          => $release['name'] ?? $release['tag_name'],
                    'body'          => $release['body'] ?? '',
                    'is_prerelease' => $release['prerelease'] ?? false,
                    'github_url'    => $release['html_url'] ?? null,
                    'download_url'  => $release['zipball_url'] ?? null,
                    'published_at'  => $release['published_at'] ?? null,
                ]
            );

            $synced++;
        }

        // Auto-activate the newest stable release
        $this->autoActivateLatest();

        return $synced;
    }

    public function getLatestVersion(): ?string
    {
        $release = SystemRelease::active()->deployed()
            ->orderByDesc('published_at')
            ->first();

        return $release?->version;
    }

    private function autoActivateLatest(): void
    {
        // Mark the single most recent deployed+non-prerelease release as active
        $latest = SystemRelease::where('is_prerelease', false)
            ->orderByDesc('published_at')
            ->first();

        if ($latest && ! $latest->is_active) {
            // Only auto-activate — never auto-deploy; admin must mark deployed
            SystemRelease::where('id', '!=', $latest->id)->update(['is_active' => false]);
            $latest->update(['is_active' => true]);
        }
    }

    private function headers(): array
    {
        $headers = ['Accept' => 'application/vnd.github+json'];

        if ($this->token) {
            $headers['Authorization'] = "Bearer {$this->token}";
        }

        return $headers;
    }

    public function isConfigured(): bool
    {
        return ! empty($this->owner) && ! empty($this->repo);
    }
}

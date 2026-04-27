<?php

namespace App\Services\Updates;

use App\Models\SystemRelease;
use Illuminate\Http\Client\PendingRequest;
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

        $http = $this->baseRequest();

        // ── Try the Releases API first ──────────────────────────────────────
        $response = $http->get("{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/releases", [
            'per_page' => config('github.max_stored_releases', 20),
        ]);

        if (! $response->successful()) {
            $status = $response->status();
            $body   = $response->body();
            Log::error('[GitHub] Failed to fetch releases', ['status' => $status, 'body' => $body]);

            $hint = match (true) {
                $status === 401 => 'Invalid or expired GITHUB_TOKEN.',
                $status === 403 => str_contains($body, 'rate limit')
                    ? 'GitHub API rate limit reached. Add a GITHUB_TOKEN to increase the limit.'
                    : 'GitHub API returned 403 Forbidden.',
                $status === 404 => 'Repository not found. Check GITHUB_OWNER and GITHUB_REPO in .env.',
                default         => "GitHub API returned HTTP {$status}.",
            };

            throw new \RuntimeException($hint);
        }

        $releases = $response->json();

        if (is_array($releases) && count($releases) > 0) {
            return $releases;
        }

        // ── Fallback: Tags API (repo has tags but no GitHub Releases yet) ──
        Log::info('[GitHub] Releases API returned empty — falling back to Tags API.');

        $tagsResponse = $http->get("{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/tags", [
            'per_page' => config('github.max_stored_releases', 20),
        ]);

        if (! $tagsResponse->successful() || ! is_array($tagsResponse->json())) {
            return [];
        }

        $tags = $tagsResponse->json();

        if (empty($tags)) {
            return [];
        }

        // Transform tags into release-shaped records.
        // Tags are returned newest-first; we assign descending timestamps so
        // version ordering is preserved inside the DB without extra API calls.
        $base = now();

        $appName = config('app.name', 'TCMS');

        return array_values(array_map(function (array $tag, int $index) use ($base, $appName) {
            $tagName = $tag['name'];
            $version = ltrim($tagName, 'v.');
            return [
                'id'           => 'tag:' . $tagName,
                'tag_name'     => $tagName,
                'name'         => "{$appName} {$tagName}",
                'body'         => '',
                'prerelease'   => false,
                'html_url'     => "https://github.com/{$this->owner}/{$this->repo}/releases/tag/{$tagName}",
                'zipball_url'  => $tag['zipball_url'] ?? null,
                // Assign dates by semver position so they never collide with each other.
                'published_at' => $base->copy()->subDays($index)->toISOString(),
            ];
        }, $tags, array_keys($tags)));
    }

    /**
     * @return array{synced:int, fetched:int, skipped_prerelease:int, skipped_invalid_tag:int}
     */
    public function syncToDatabase(): array
    {
        $releases = $this->fetchReleases();

        // If the Releases API returned real data, wipe all tag-fallback records —
        // they are no longer needed and should not appear alongside real releases.
        $usingRealReleases = collect($releases)->contains(
            fn($r) => ! str_starts_with((string) ($r['id'] ?? ''), 'tag:')
        );

        if ($usingRealReleases) {
            SystemRelease::where('github_id', 'LIKE', 'tag:%')->delete();
        }

        $stats = [
            'synced'              => 0,
            'fetched'             => count($releases),
            'skipped_prerelease'  => 0,
            'skipped_invalid_tag' => 0,
        ];

        foreach ($releases as $release) {
            if (! $this->includePrereleases && ($release['prerelease'] ?? false)) {
                $stats['skipped_prerelease']++;
                continue;
            }

            // Strip leading 'v' and any accidental dot (handles v1.2.0 and v.1.2.0)
            $version = ltrim($release['tag_name'], 'v.');

            if (! preg_match('/^\d+\.\d+/', $version)) {
                $stats['skipped_invalid_tag']++;
                continue;
            }

            $githubId     = (string) $release['id'];
            $isTagFallback = str_starts_with($githubId, 'tag:');

            if ($isTagFallback) {
                // Skip tags that already have a proper GitHub Release record.
                if (SystemRelease::where('tag_name', $release['tag_name'])
                        ->where('github_id', 'NOT LIKE', 'tag:%')
                        ->exists()) {
                    $stats['synced']++;
                    continue;
                }
            } else {
                // Real release arriving — remove any stale tag-fallback record for this tag.
                SystemRelease::where('github_id', 'LIKE', 'tag:%')
                    ->where('tag_name', $release['tag_name'])
                    ->delete();
            }

            SystemRelease::updateOrCreate(
                ['github_id' => $githubId],
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

            $stats['synced']++;
        }

        $this->autoActivateLatest();

        return $stats;
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
        $latest = SystemRelease::where('is_prerelease', false)
            ->semverDesc()
            ->first();

        if ($latest && ! $latest->is_active) {
            SystemRelease::where('id', '!=', $latest->id)->update(['is_active' => false]);
            $latest->update(['is_active' => true]);
        }
    }

    private function baseRequest(): PendingRequest
    {
        $request = Http::withHeaders($this->headers())->timeout(15);

        // Skip SSL verification on local dev (Windows has no CA bundle by default)
        if (app()->environment('local')) {
            $request = $request->withoutVerifying();
        }

        return $request;
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

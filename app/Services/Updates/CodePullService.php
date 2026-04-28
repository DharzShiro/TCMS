<?php

namespace App\Services\Updates;

use Illuminate\Support\Facades\Log;

/**
 * Handles the server-side code update: git pull → composer install → cache rebuild.
 * Uses a file lock so concurrent tenant update requests don't trigger multiple pulls.
 */
class CodePullService
{
    private string $basePath;
    private string $lockFile;
    private string $versionFile;

    public function __construct()
    {
        $this->basePath    = base_path();
        // Use base_path() so these always resolve to the root storage folder
        // even when called from inside a tenant context (storage_path() shifts there).
        $this->lockFile    = base_path('storage/app/code_pull.lock');
        $this->versionFile = base_path('storage/app/code_version.txt');
    }

    public function isEnabled(): bool
    {
        return (bool) config('github.auto_pull', true);
    }

    /** Returns the code version currently on disk (from the tracker file, or fallback to .env). */
    public function getCodeVersion(): string
    {
        if (file_exists($this->versionFile)) {
            $ver = trim(file_get_contents($this->versionFile));
            if ($ver !== '') {
                return $ver;
            }
        }

        return config('github.current_version', '1.0.0');
    }

    public function isCodeUpToDate(string $targetVersion): bool
    {
        return version_compare($this->getCodeVersion(), $targetVersion, '>=');
    }

    /**
     * Pull code from GitHub, run composer if dependencies changed, then rebuild caches.
     * Thread-safe: waits up to 120 s if another process holds the lock.
     *
     * @return array{success: bool, output: string}
     */
    public function pull(string $targetVersion): array
    {
        if (! $this->isEnabled()) {
            return ['success' => true, 'output' => '[CodePull] Auto-pull disabled (GITHUB_AUTO_PULL=false). Skipped.'];
        }

        // Wait for any concurrent pull to finish
        $waited = 0;
        while (file_exists($this->lockFile) && $waited < 120) {
            sleep(3);
            $waited += 3;

            // Another process may have already pulled this version — check
            if ($this->isCodeUpToDate($targetVersion)) {
                return ['success' => true, 'output' => "[CodePull] v{$targetVersion} was pulled by a concurrent process. Reusing."];
            }
        }

        if (file_exists($this->lockFile)) {
            return ['success' => false, 'output' => '[CodePull] Timed out waiting for another pull to finish. Please retry.'];
        }

        file_put_contents($this->lockFile, $targetVersion);

        try {
            $sections = [];

            // 1 — Verify git is available
            $git    = $this->gitBin();
            $gitVer = $this->run("\"{$git}\" --version");
            if ($gitVer['exit'] !== 0) {
                throw new \RuntimeException('git is not available. Set GIT_BINARY in .env to the full path of git.exe.');
            }

            // 2 — Initialize git repo if this directory was copied rather than cloned
            $this->ensureGitRepo($git, $sections);

            // 3 — Snapshot composer.lock hash before pull
            $composerLockBefore = $this->composerLockHash();

            // 4 — git pull
            $pull = $this->run("\"{$git}\" pull");
            $sections[] = "=== git pull ===\n" . $pull['out'];

            if ($pull['exit'] !== 0) {
                throw new \RuntimeException("git pull failed (exit {$pull['exit']}):\n" . $pull['out']);
            }

            // 4 — composer install if composer.lock changed
            if ($composerLockBefore !== $this->composerLockHash()) {
                $composer = $this->run($this->composerBin() . ' install --no-dev --optimize-autoloader --no-interaction 2>&1');
                $sections[] = "=== composer install ===\n" . $composer['out'];

                if ($composer['exit'] !== 0) {
                    Log::warning('[CodePull] composer install returned non-zero exit. Continuing.', ['output' => $composer['out']]);
                }
            }

            // 5 — Rebuild caches
            $php = $this->phpBin();
            $this->run("{$php} artisan config:cache");
            $this->run("{$php} artisan route:cache");
            $this->run("{$php} artisan view:clear");
            $this->run("{$php} artisan queue:restart");
            $sections[] = "=== Caches rebuilt ===";

            // 6 — Write new code version to tracker file
            file_put_contents($this->versionFile, $targetVersion);

            Log::info("[CodePull] Successfully pulled code to v{$targetVersion}.");

            return ['success' => true, 'output' => implode("\n\n", $sections)];

        } catch (\Throwable $e) {
            Log::error('[CodePull] ' . $e->getMessage());

            return ['success' => false, 'output' => $e->getMessage()];

        } finally {
            @unlink($this->lockFile);
        }
    }

    // ─────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────

    private function composerLockHash(): ?string
    {
        $path = $this->basePath . DIRECTORY_SEPARATOR . 'composer.lock';

        return file_exists($path) ? md5_file($path) : null;
    }

    /** @return array{out: string, exit: int} */
    private function run(string $command): array
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes, $this->basePath);

        if (! is_resource($process)) {
            return ['out' => "Failed to start: {$command}", 'exit' => -1];
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        $out = trim($stdout ?: '');
        if ($stderr = trim($stderr ?: '')) {
            $out .= ($out ? "\n" : '') . $stderr;
        }

        return ['out' => $out, 'exit' => $exit];
    }

    /**
     * If the working directory has no .git folder (files were copied, not cloned),
     * init a repo, add the GitHub remote, and hard-reset to origin so git pull works
     * from this point forward.
     */
    private function ensureGitRepo(string $git, array &$sections): void
    {
        if (is_dir($this->basePath . DIRECTORY_SEPARATOR . '.git')) {
            return;
        }

        $owner  = config('github.owner');
        $repo   = config('github.repo');
        $token  = config('github.token');
        $branch = $this->branchName();

        if (! $owner || ! $repo) {
            throw new \RuntimeException(
                'This directory is not a git repository and GITHUB_OWNER / GITHUB_REPO are not set in .env. ' .
                'Please clone the repository or set those env keys so the update service can initialize it.'
            );
        }

        $remote = $token
            ? "https://{$token}@github.com/{$owner}/{$repo}.git"
            : "https://github.com/{$owner}/{$repo}.git";

        $init = $this->run("\"{$git}\" init");
        $sections[] = "=== git init (first-time setup) ===\n" . $init['out'];
        if ($init['exit'] !== 0) {
            throw new \RuntimeException("git init failed:\n" . $init['out']);
        }

        $addRemote = $this->run("\"{$git}\" remote add origin \"{$remote}\"");
        $sections[] = "=== git remote add origin ===\n" . $addRemote['out'];

        $fetch = $this->run("\"{$git}\" fetch origin \"{$branch}\"");
        $sections[] = "=== git fetch origin {$branch} ===\n" . $fetch['out'];
        if ($fetch['exit'] !== 0) {
            throw new \RuntimeException("git fetch failed:\n" . $fetch['out']);
        }

        $reset = $this->run("\"{$git}\" reset --hard \"origin/{$branch}\"");
        $sections[] = "=== git reset --hard origin/{$branch} (first-time setup) ===\n" . $reset['out'];
        if ($reset['exit'] !== 0) {
            throw new \RuntimeException("git reset --hard failed:\n" . $reset['out']);
        }

        Log::info('[CodePull] Git repository initialized from remote. Subsequent pulls will be incremental.');
    }

    private function phpBin(): string
    {
        return config('github.php_binary', 'php');
    }

    private function gitBin(): string
    {
        return config('github.git_binary', 'git');
    }

    private function composerBin(): string
    {
        return config('github.composer_command', 'composer');
    }

    private function branchName(): string
    {
        return config('github.branch', 'main');
    }
}

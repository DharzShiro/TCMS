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
        $this->lockFile    = storage_path('app/code_pull.lock');
        $this->versionFile = storage_path('app/code_version.txt');
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
            $gitVer = $this->run('git --version');
            if ($gitVer['exit'] !== 0) {
                throw new \RuntimeException('git is not available in the server PATH. Install git and ensure it is accessible by the web server user.');
            }

            // 2 — Snapshot composer.lock hash before pull
            $composerLockBefore = $this->composerLockHash();

            // 3 — git pull
            $pull = $this->run('git pull');
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

    private function phpBin(): string
    {
        return config('github.php_binary', 'php');
    }

    private function composerBin(): string
    {
        return config('github.composer_command', 'composer');
    }
}

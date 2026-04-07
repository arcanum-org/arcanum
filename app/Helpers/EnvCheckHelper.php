<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Http\Listener\RequestCounter;
use App\Http\RenderMetrics;
use Arcanum\Gather\Configuration;
use Arcanum\Ignition\Kernel;
use Composer\InstalledVersions;
use Psr\SimpleCache\CacheInterface;

/**
 * Diagnostic facts about the running environment, surfaced on the
 * welcome page's heartbeat badge, environment column, and footer crumb.
 *
 * Static checks per request — no caching. The index page only renders in
 * dev mode and is called rarely; running file_exists() and
 * extension_loaded() per page load is cheap and keeps the page accurate
 * without a manual cache bust after install.
 *
 * Declared per-page on the Index DTO via #[WithHelper] — not registered
 * globally; other pages don't need it.
 */
final class EnvCheckHelper
{
    /** PHP minimum the framework requires. */
    private const string PHP_MINIMUM = '8.4.0';

    /** Extensions the framework genuinely uses. Anything else stays out. */
    private const array CHECKED_EXTENSIONS = ['sodium', 'pdo', 'json', 'mbstring', 'openssl'];

    public function __construct(
        private readonly Configuration $config,
        private readonly Kernel $kernel,
        private readonly RenderMetrics $metrics,
        private readonly CacheInterface $cache,
    ) {
    }

    // ── PHP runtime ───────────────────────────────────────────────

    public function phpVersion(): string
    {
        return PHP_VERSION;
    }

    public function phpVersionOk(): bool
    {
        return version_compare(PHP_VERSION, self::PHP_MINIMUM, '>=');
    }

    /**
     * @return array<string, bool> extension name → loaded flag
     */
    public function extensions(): array
    {
        $result = [];
        foreach (self::CHECKED_EXTENSIONS as $name) {
            $result[$name] = extension_loaded($name);
        }
        return $result;
    }

    // ── Filesystem writability ────────────────────────────────────

    public function filesWritable(): bool
    {
        return is_writable($this->kernel->filesDirectory());
    }

    public function cacheWritable(): bool
    {
        return $this->isWritableUnderFiles('cache');
    }

    public function logsWritable(): bool
    {
        return $this->isWritableUnderFiles('logs');
    }

    public function sessionsWritable(): bool
    {
        return $this->isWritableUnderFiles('sessions');
    }

    private function isWritableUnderFiles(string $subdir): bool
    {
        $path = $this->kernel->filesDirectory() . DIRECTORY_SEPARATOR . $subdir;
        return is_dir($path) && is_writable($path);
    }

    // ── Configured drivers ────────────────────────────────────────

    public function cacheDriver(): string
    {
        $value = $this->config->get('cache.default');
        return is_string($value) ? $value : 'unknown';
    }

    public function sessionDriver(): ?string
    {
        $value = $this->config->get('session.driver');
        return is_string($value) ? $value : null;
    }

    public function databaseConnection(): ?string
    {
        $value = $this->config->get('database.default');
        return is_string($value) ? $value : null;
    }

    // ── Build artefacts ───────────────────────────────────────────

    public function cssBuilt(): bool
    {
        $cssPath = $this->kernel->rootDirectory()
            . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . 'css'
            . DIRECTORY_SEPARATOR . 'app.min.css';
        return file_exists($cssPath);
    }

    // ── App identity ──────────────────────────────────────────────

    public function debugMode(): bool
    {
        $value = $this->config->get('app.debug');
        return is_bool($value) ? $value : ($value === 'true' || $value === '1' || $value === 1);
    }

    public function appEnvironment(): string
    {
        $value = $this->config->get('app.environment');
        return is_string($value) ? $value : 'unknown';
    }

    public function frameworkVersion(): string
    {
        try {
            return InstalledVersions::getPrettyVersion('arcanum-org/framework') ?? 'dev';
        } catch (\OutOfBoundsException) {
            return 'unknown';
        }
    }

    // ── Render-time metrics ───────────────────────────────────────

    public function renderDurationMs(): ?float
    {
        return $this->metrics->elapsedMs();
    }

    public function requestCount(): int
    {
        $value = $this->cache->get(RequestCounter::KEY, 0);
        return is_int($value) ? $value : 0;
    }
}

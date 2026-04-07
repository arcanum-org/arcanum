<?php

declare(strict_types=1);

namespace App\Test\Helpers;

use App\Helpers\EnvCheckHelper;
use App\Http\Listener\RequestCounter;
use App\Http\RenderMetrics;
use App\Test\Http\Listener\InMemoryCache;
use Arcanum\Gather\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnvCheckHelper::class)]
final class EnvCheckHelperTest extends TestCase
{
    private string $tmpRoot;

    protected function setUp(): void
    {
        $this->tmpRoot = sys_get_temp_dir() . '/arcanum_env_test_' . uniqid();
        mkdir($this->tmpRoot . '/files/cache', 0777, true);
        mkdir($this->tmpRoot . '/files/logs', 0777, true);
        mkdir($this->tmpRoot . '/files/sessions', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->cleanDir($this->tmpRoot);
    }

    private function cleanDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = glob($dir . '/*') ?: [];
        foreach ($items as $item) {
            is_dir($item) ? $this->cleanDir($item) : @unlink($item);
        }
        @chmod($dir, 0777);
        @rmdir($dir);
    }

    // ── PHP runtime ───────────────────────────────────────────────

    public function testPhpVersionMatchesRuntime(): void
    {
        $helper = $this->makeHelper();

        $this->assertSame(PHP_VERSION, $helper->phpVersion());
    }

    public function testPhpVersionOkOnSupportedRuntime(): void
    {
        // The test suite itself requires PHP 8.4+, so this is always true
        // when the test runs at all — but the explicit check guards against
        // a future runtime regression in the comparator.
        $helper = $this->makeHelper();

        $this->assertTrue($helper->phpVersionOk());
    }

    public function testExtensionsListsCheckedExtensions(): void
    {
        $helper = $this->makeHelper();

        $extensions = $helper->extensions();

        $this->assertArrayHasKey('sodium', $extensions);
        $this->assertArrayHasKey('pdo', $extensions);
        $this->assertArrayHasKey('json', $extensions);
        $this->assertArrayHasKey('mbstring', $extensions);
        $this->assertArrayHasKey('openssl', $extensions);
    }

    // ── Filesystem writability ────────────────────────────────────

    public function testWritabilityChecksRespectActualPaths(): void
    {
        $helper = $this->makeHelper();

        $this->assertTrue($helper->filesWritable());
        $this->assertTrue($helper->cacheWritable());
        $this->assertTrue($helper->logsWritable());
        $this->assertTrue($helper->sessionsWritable());
    }

    public function testCacheWritableFalseWhenDirectoryMissing(): void
    {
        rmdir($this->tmpRoot . '/files/cache');

        $helper = $this->makeHelper();

        $this->assertFalse($helper->cacheWritable());
    }

    // ── Configured drivers ────────────────────────────────────────

    public function testCacheDriverReadsConfig(): void
    {
        $helper = $this->makeHelper(config: new Configuration([
            'cache' => ['default' => 'redis'],
        ]));

        $this->assertSame('redis', $helper->cacheDriver());
    }

    public function testCacheDriverFallsBackToUnknown(): void
    {
        $helper = $this->makeHelper(config: new Configuration([]));

        $this->assertSame('unknown', $helper->cacheDriver());
    }

    public function testSessionDriverNullWhenAbsent(): void
    {
        $helper = $this->makeHelper(config: new Configuration([]));

        $this->assertNull($helper->sessionDriver());
    }

    public function testDatabaseConnectionReadsConfig(): void
    {
        $helper = $this->makeHelper(config: new Configuration([
            'database' => ['default' => 'postgres'],
        ]));

        $this->assertSame('postgres', $helper->databaseConnection());
    }

    // ── Build artefacts ───────────────────────────────────────────

    public function testCssBuiltFalseWhenBundleMissing(): void
    {
        $helper = $this->makeHelper();

        $this->assertFalse($helper->cssBuilt());
    }

    public function testCssBuiltTrueWhenBundleExists(): void
    {
        mkdir($this->tmpRoot . '/public/css', 0777, true);
        file_put_contents($this->tmpRoot . '/public/css/app.min.css', '/* built */');

        $helper = $this->makeHelper();

        $this->assertTrue($helper->cssBuilt());
    }

    // ── App identity ──────────────────────────────────────────────

    public function testDebugModeReadsBoolConfig(): void
    {
        $helper = $this->makeHelper(config: new Configuration([
            'debug' => true,
        ]));

        $this->assertTrue($helper->debugMode());
    }

    public function testDebugModeAcceptsStringTrue(): void
    {
        // Real-world: $_ENV values are always strings.
        $helper = $this->makeHelper(config: new Configuration([
            'debug' => 'true',
        ]));

        $this->assertTrue($helper->debugMode());
    }

    public function testDebugModeFalseWhenAbsent(): void
    {
        $helper = $this->makeHelper(config: new Configuration([]));

        $this->assertFalse($helper->debugMode());
    }

    public function testAppEnvironmentReadsConfig(): void
    {
        $helper = $this->makeHelper(config: new Configuration([
            'environment' => 'staging',
        ]));

        $this->assertSame('staging', $helper->appEnvironment());
    }

    public function testFrameworkVersionResolvesToString(): void
    {
        $helper = $this->makeHelper();

        $version = $helper->frameworkVersion();

        // Real Composer\InstalledVersions returns the resolved version string —
        // dev branch, tag, or 'dev' fallback. Just verify we got a non-empty
        // string, not the literal value (CI varies).
        $this->assertNotEmpty($version);
    }

    // ── Render-time metrics ───────────────────────────────────────

    public function testRenderDurationNullBeforeStart(): void
    {
        $helper = $this->makeHelper();

        $this->assertNull($helper->renderDurationMs());
    }

    public function testRenderDurationReportsElapsed(): void
    {
        $metrics = new RenderMetrics();
        $metrics->setStartTime(microtime(true));
        usleep(1000); // 1ms

        $helper = $this->makeHelper(metrics: $metrics);

        $elapsed = $helper->renderDurationMs();

        $this->assertNotNull($elapsed);
        $this->assertGreaterThanOrEqual(1.0, $elapsed);
    }

    public function testRequestCountZeroWhenAbsent(): void
    {
        $helper = $this->makeHelper();

        $this->assertSame(0, $helper->requestCount());
    }

    public function testRequestCountReadsFromCache(): void
    {
        $cache = new InMemoryCache();
        $cache->set(RequestCounter::KEY, 42);

        $helper = $this->makeHelper(cache: $cache);

        $this->assertSame(42, $helper->requestCount());
    }

    public function testRequestCountFallsBackToZeroOnCorruptValue(): void
    {
        $cache = new InMemoryCache();
        $cache->set(RequestCounter::KEY, 'not an int');

        $helper = $this->makeHelper(cache: $cache);

        $this->assertSame(0, $helper->requestCount());
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function makeHelper(
        ?Configuration $config = null,
        ?RenderMetrics $metrics = null,
        ?InMemoryCache $cache = null,
    ): EnvCheckHelper {
        return new EnvCheckHelper(
            config: $config ?? new Configuration([]),
            kernel: new FixtureKernel($this->tmpRoot),
            metrics: $metrics ?? new RenderMetrics(),
            cache: $cache ?? new InMemoryCache(),
        );
    }
}

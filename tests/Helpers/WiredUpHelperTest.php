<?php

declare(strict_types=1);

namespace App\Test\Helpers;

use App\Helpers\WiredUpHelper;
use Arcanum\Atlas\MiddlewareDiscovery;
use Arcanum\Atlas\PageDiscovery;
use Arcanum\Shodo\HelperRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Coverage for the dev-only welcome-page diagnostics helper.
 *
 * The interesting logic is in commands() and queries() — those walk
 * the filesystem and need real fixture directories. The other three
 * counters (pages, middleware, helpers) are pure delegation one-liners
 * over framework discovery objects; they're verified by the smoke
 * test against the live framework. Unit-testing them here would mean
 * stubbing final classes, which PHPUnit can't do.
 */
#[CoversClass(WiredUpHelper::class)]
final class WiredUpHelperTest extends TestCase
{
    private string $tmpRoot;

    protected function setUp(): void
    {
        $this->tmpRoot = sys_get_temp_dir() . '/arcanum_wired_test_' . uniqid();
        mkdir($this->tmpRoot . '/app/Domain/Shop/Command', 0777, true);
        mkdir($this->tmpRoot . '/app/Domain/Shop/Query', 0777, true);
        mkdir($this->tmpRoot . '/app/Domain/Auth/Query', 0777, true);
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
        @rmdir($dir);
    }

    public function testCommandsCountsCommandDtosOnly(): void
    {
        // Arrange — two real commands, one handler that should be excluded
        file_put_contents($this->tmpRoot . '/app/Domain/Shop/Command/PlaceOrder.php', '<?php');
        file_put_contents($this->tmpRoot . '/app/Domain/Shop/Command/CancelOrder.php', '<?php');
        file_put_contents($this->tmpRoot . '/app/Domain/Shop/Command/PlaceOrderHandler.php', '<?php');

        $helper = $this->makeHelper();

        $this->assertSame(2, $helper->commands());
    }

    public function testQueriesCountsQueryDtosAcrossDomains(): void
    {
        // Arrange — one query in Shop, one in Auth, plus handlers and a command
        file_put_contents($this->tmpRoot . '/app/Domain/Shop/Query/Products.php', '<?php');
        file_put_contents($this->tmpRoot . '/app/Domain/Shop/Query/ProductsHandler.php', '<?php');
        file_put_contents($this->tmpRoot . '/app/Domain/Auth/Query/Whoami.php', '<?php');
        file_put_contents($this->tmpRoot . '/app/Domain/Shop/Command/PlaceOrder.php', '<?php');

        $helper = $this->makeHelper();

        $this->assertSame(2, $helper->queries());
    }

    public function testCommandsAndQueriesReturnZeroWhenDomainRootMissing(): void
    {
        // Arrange — kernel pointing at a path with no app/Domain
        $emptyRoot = sys_get_temp_dir() . '/arcanum_wired_empty_' . uniqid();
        mkdir($emptyRoot, 0777, true);

        $helper = $this->makeHelper(rootDirectory: $emptyRoot);

        $this->assertSame(0, $helper->commands());
        $this->assertSame(0, $helper->queries());

        @rmdir($emptyRoot);
    }

    public function testHelpersDelegatesToHelperRegistry(): void
    {
        $registry = new HelperRegistry();
        $registry->register('Format', new \stdClass());
        $registry->register('Route', new \stdClass());
        $registry->register('Html', new \stdClass());

        $helper = $this->makeHelper(helperRegistry: $registry);

        $this->assertSame(3, $helper->helpers());
    }

    public function testPagesAndMiddlewareReturnZeroAgainstEmptyDiscovery(): void
    {
        // Empty fixture dirs → discover() returns []. Real instances of
        // both classes (rather than stubs) since they're final and
        // PHPUnit can't mock them.
        $emptyDir = sys_get_temp_dir() . '/arcanum_wired_empty_disc_' . uniqid();
        mkdir($emptyDir, 0777, true);

        $pageDiscovery = new PageDiscovery(
            namespace: 'App\\Pages',
            directory: $emptyDir,
        );
        $middlewareDiscovery = new MiddlewareDiscovery(
            rootNamespace: 'App\\Domain',
            rootDirectory: $emptyDir,
        );

        $helper = $this->makeHelper(
            pageDiscovery: $pageDiscovery,
            middlewareDiscovery: $middlewareDiscovery,
        );

        $this->assertSame(0, $helper->pages());
        $this->assertSame(0, $helper->middleware());

        @rmdir($emptyDir);
    }

    private function makeHelper(
        ?string $rootDirectory = null,
        ?PageDiscovery $pageDiscovery = null,
        ?MiddlewareDiscovery $middlewareDiscovery = null,
        ?HelperRegistry $helperRegistry = null,
    ): WiredUpHelper {
        $emptyDir = sys_get_temp_dir() . '/arcanum_wired_default_' . uniqid();
        if (!is_dir($emptyDir)) {
            mkdir($emptyDir, 0777, true);
        }

        return new WiredUpHelper(
            kernel: new FixtureKernel($rootDirectory ?? $this->tmpRoot),
            pages: $pageDiscovery ?? new PageDiscovery(
                namespace: 'App\\Pages',
                directory: $emptyDir,
            ),
            middleware: $middlewareDiscovery ?? new MiddlewareDiscovery(
                rootNamespace: 'App\\Domain',
                rootDirectory: $emptyDir,
            ),
            helpers: $helperRegistry ?? new HelperRegistry(),
        );
    }
}

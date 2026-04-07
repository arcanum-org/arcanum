<?php

declare(strict_types=1);

namespace App\Helpers;

use Arcanum\Atlas\MiddlewareDiscovery;
use Arcanum\Atlas\PageDiscovery;
use Arcanum\Ignition\Kernel;
use Arcanum\Shodo\HelperRegistry;

/**
 * Diagnostic counts of what's wired into the framework right now.
 *
 * Powers the welcome page's "what's wired up" panel — five quick numbers
 * that double as a smoke test. Zero commands or zero queries on a fresh
 * install instantly tells a new dev that domain discovery didn't run,
 * which is far more useful than a polished error page two clicks deeper.
 *
 * Declared per-page on the Index DTO via #[WithHelper] — not registered
 * globally; other pages don't need it.
 *
 * Counts are pulled from the live framework discovery objects (cached),
 * not from filesystem walks at render time, except for commands and
 * queries which require a domain walk because no command/query
 * discovery service exists yet. The walk is dev-only and trivial.
 */
final class WiredUpHelper
{
    public function __construct(
        private readonly Kernel $kernel,
        private readonly PageDiscovery $pages,
        private readonly MiddlewareDiscovery $middleware,
        private readonly HelperRegistry $helpers,
    ) {
    }

    public function commands(): int
    {
        return $this->countDtosUnder('Command');
    }

    public function queries(): int
    {
        return $this->countDtosUnder('Query');
    }

    public function pages(): int
    {
        return count($this->pages->discover());
    }

    public function middleware(): int
    {
        return count($this->middleware->discover());
    }

    public function helpers(): int
    {
        return count($this->helpers->all());
    }

    /**
     * Count DTO classes under app/Domain/<Domain>/{Command,Query}/.
     *
     * Walks the filesystem, excluding files that look like a handler
     * (matching `*Handler.php`). Same heuristic the framework's
     * validate:handlers command uses.
     */
    private function countDtosUnder(string $segment): int
    {
        $domainRoot = $this->kernel->rootDirectory()
            . DIRECTORY_SEPARATOR . 'app'
            . DIRECTORY_SEPARATOR . 'Domain';

        if (!is_dir($domainRoot)) {
            return 0;
        }

        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $domainRoot,
                \FilesystemIterator::SKIP_DOTS,
            ),
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if ($file->getExtension() !== 'php') {
                continue;
            }
            if (str_ends_with($file->getFilename(), 'Handler.php')) {
                continue;
            }
            $path = $file->getPathname();
            if (!str_contains($path, DIRECTORY_SEPARATOR . $segment . DIRECTORY_SEPARATOR)) {
                continue;
            }
            $count++;
        }

        return $count;
    }
}

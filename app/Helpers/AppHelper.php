<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Application-wide template helper.
 *
 * Available in every template (Page or Query) under the alias `App`.
 * Exposes runtime context that templates need to make rendering
 * decisions — currently the debug flag and the CSS asset selection.
 *
 * Usage in templates:
 *   {{! App::cssTags() !}}
 *   {{ App::debug() ? 'dev' : 'prod' }}
 */
final class AppHelper
{
    public function __construct(
        private readonly bool $debug,
        private readonly string $publicDirectory,
    ) {
    }

    /**
     * Whether the app is running in debug mode.
     */
    public function debug(): bool
    {
        return $this->debug;
    }

    /**
     * The installed framework version from Composer.
     */
    public function version(): string
    {
        $installed = $this->publicDirectory . '/../vendor/composer/installed.php';
        if (!file_exists($installed)) {
            return 'dev';
        }

        /** @var array{versions: array<string, array{pretty_version?: string}>} */
        $data = require $installed;
        return $data['versions']['arcanum-org/framework']['pretty_version'] ?? 'dev';
    }

    /**
     * Render the CSS tags for the layout.
     *
     * In production (when the built CSS bundle exists at
     * public/css/app.min.css), emits a single <link> tag.
     *
     * Otherwise, emits the Tailwind CDN play script with the inline
     * config. The CDN path is for development only — see the README
     * for the production build flow.
     */
    public function cssTags(): string
    {
        if ($this->isBuiltCssAvailable()) {
            return '<link rel="stylesheet" href="/css/app.min.css">';
        }

        return $this->cdnFallbackHtml();
    }

    private function isBuiltCssAvailable(): bool
    {
        return file_exists($this->publicDirectory . '/css/app.min.css');
    }

    private function cdnFallbackHtml(): string
    {
        return <<<'HTML'
        <!-- Tailwind CSS — CDN play script for development. Replace with built CSS for production. -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        parchment: '#faf8f1',
                        vellum: '#f4f1e8',
                        linen: '#eae6da',
                        ink: '#2c2a25',
                        copper: '#b5623f',
                        'copper-light': '#c8795a',
                        'copper-dark': '#9e5436',
                        charcoal: '#3d3a34',
                        'warm-gray': '#6b675e',
                        stone: '#9c9789',
                        sand: '#c4bfb3',
                        'light-sand': '#ddd9ce',
                        dust: '#ece9e0',
                        'deep-parchment': '#1a1915',
                        'dark-vellum': '#23211b',
                        'dark-linen': '#2d2b24',
                        'dark-sand': '#3d3a34',
                        success: '#4a7c59',
                        error: '#a63d2f',
                        warning: '#b8862e',
                        info: '#4a6fa5',
                    },
                    fontFamily: {
                        heading: ['Lora', 'Georgia', 'Times New Roman', 'serif'],
                        body: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif'],
                        code: ['JetBrains Mono', 'Fira Code', 'Source Code Pro', 'Consolas', 'monospace'],
                    },
                    maxWidth: {
                        prose: '720px',
                        layout: '1120px',
                    },
                },
            },
        }
        </script>
        <style type="text/tailwindcss">
        @layer base {
            body {
                @apply bg-parchment text-ink font-body;
            }
            .dark body {
                @apply bg-deep-parchment text-[#e8e4db];
            }
        }
        </style>
        HTML;
    }
}

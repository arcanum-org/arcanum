<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Tip-of-the-day helper for the welcome page.
 *
 * Pure, no I/O — a hardcoded list of useful Arcanum tricks rotates
 * deterministically through the day-of-year, so the same tip stays
 * stable for a calendar day and changes at midnight without any
 * persistent state.
 *
 * Declared per-page on the Index DTO via #[WithHelper], not registered
 * globally. Other pages don't need it.
 */
final class IncantationHelper
{
    /**
     * @return list<array{title: string, body: string, code: ?string}>
     */
    private function tips(): array
    {
        return [
            [
                'title' => 'Match against a value in templates',
                'body' => 'Shodo has a match directive that compiles to a PHP switch with'
                    . ' implicit break. Comma-separated case values fall through.',
                'code' => "{{ match \$status }}\n"
                    . "    {{ case 'pending', 'active' }}<span>Active</span>\n"
                    . "    {{ case 'closed' }}<span>Closed</span>\n"
                    . '{{ endmatch }}',
            ],
            [
                'title' => 'Scaffold a typed query',
                'body' => 'make:query generates the Query DTO, handler, and template in one'
                    . ' step. Pick a domain and a name; the scaffold lands at the right path.',
                'code' => 'php arcanum make:query Shop/Products',
            ],
            [
                'title' => 'Helper calls compose',
                'body' => 'The body of {{ }} is a real PHP expression. Helper calls can be'
                    . ' followed by array access, method chains, arithmetic, ternaries, or'
                    . ' nested helper calls — anything PHP allows after a method call.',
                'code' => "{{ Tip::today()['title'] }}\n"
                    . '{{ Format::number(Math::pi(), 2) }}',
            ],
            [
                'title' => 'Clear every framework cache at once',
                'body' => 'cache:clear walks every framework cache surface — templates,'
                    . ' helpers, page discovery, middleware discovery — plus any registered'
                    . ' Vault stores. One command, no missed corners.',
                'code' => 'php arcanum cache:clear',
            ],
            [
                'title' => 'Require auth on a DTO',
                'body' => 'Drop #[RequiresAuth] on a Command or Query DTO. The'
                    . ' AuthorizationGuard rejects unauthenticated requests with 401 before'
                    . ' the handler runs.',
                'code' => "#[RequiresAuth]\nfinal class DeleteAccount { }",
            ],
            [
                'title' => 'Stream a million-row export',
                'body' => 'Forge reads return a Sequencer that streams row-by-row. Iterate'
                    . ' directly with foreach — peak memory stays flat regardless of result'
                    . ' size. Materialize explicitly with toSeries() when you need it.',
                'code' => "foreach (\$db->model->allEvents() as \$row) {\n"
                    . "    \$worker->push(\$row);\n"
                    . '}',
            ],
            [
                'title' => 'Stopwatch the whole request',
                'body' => 'The framework Stopwatch records arcanum.start, boot.complete,'
                    . ' handler.start, render.complete, and arcanum.complete automatically.'
                    . ' Read elapsed time anywhere via the static accessor.',
                'code' => "Stopwatch::current()->timeBetween(\n"
                    . "    'arcanum.start',\n"
                    . "    'arcanum.complete',\n"
                    . ');',
            ],
            [
                'title' => 'CSRF in a form, with one directive',
                'body' => 'The {{ csrf }} directive expands to a hidden input with the active'
                    . ' session token. CsrfMiddleware validates it on every state-changing'
                    . ' request automatically.',
                'code' => "<form method=\"post\">\n"
                    . "    {{ csrf }}\n"
                    . '</form>',
            ],
            [
                'title' => 'Per-page helpers without polluting the registry',
                'body' => 'Some helpers only make sense for one DTO. The #[WithHelper]'
                    . ' attribute scopes a helper to that single page, with an explicit alias'
                    . ' you control.',
                'code' => "#[WithHelper(EnvCheckHelper::class, alias: 'Env')]\n"
                    . 'final class Index { }',
            ],
            [
                'title' => 'Custom error pages by status code',
                'body' => 'Drop a {code}.html file in your error templates directory and the'
                    . ' HtmlExceptionResponseRenderer picks it up automatically. The framework'
                    . ' falls back to a styled default for every status code it knows.',
                'code' => "files/templates/errors/404.html\n"
                    . 'files/templates/errors/500.html',
            ],
            [
                'title' => 'Validation lives on the DTO',
                'body' => 'Validation rules are PHP attributes on constructor parameters.'
                    . ' ValidationGuard runs them before the handler — 422 Unprocessable Entity'
                    . ' on HTTP, field-level errors on CLI, no boilerplate.',
                'code' => "public function __construct(\n"
                    . "    #[Email] public readonly string \$email,\n"
                    . "    #[MinLength(8)] public readonly string \$password,\n"
                    . ') { }',
            ],
            [
                'title' => 'Domain-scoped middleware by convention',
                'body' => 'Drop a Middleware.php file next to a domain folder. Every'
                    . ' Command/Query under that domain runs through the listed middleware.'
                    . ' No central registry, no config edits.',
                'code' => "// app/Domain/Shop/Middleware.php\n"
                    . "return [\n"
                    . "    LogShopActivity::class,\n"
                    . '];',
            ],
            [
                'title' => 'One handler, every response format',
                'body' => 'A single Query handler serves JSON, HTML, CSV, plain text, and'
                    . ' Markdown — pick the format with the URL extension. The same data goes'
                    . ' through five different formatters.',
                'code' => "GET /products.json\n"
                    . "GET /products.html\n"
                    . "GET /products.csv\n"
                    . 'GET /products.md',
            ],
            [
                'title' => 'Validate handlers exist before deploying',
                'body' => 'validate:handlers walks every Command/Query DTO and confirms its'
                    . ' handler is registered. Run it in CI to catch missing handlers at'
                    . ' build time instead of in production.',
                'code' => 'php arcanum validate:handlers',
            ],
            [
                'title' => 'Cast SQL columns at the model boundary',
                'body' => 'Forge reads JSON and tinyints back as raw strings by default.'
                    . ' Add @cast annotations to the .sql file and the columns come back as'
                    . ' the right PHP types automatically.',
                'code' => "-- @cast id int\n"
                    . "-- @cast active bool\n"
                    . "-- @cast metadata json\n"
                    . 'SELECT id, active, metadata FROM widgets;',
            ],
        ];
    }

    /**
     * Return today's incantation, picked deterministically by day-of-year.
     *
     * @return array{title: string, body: string, code: ?string}
     */
    public function today(): array
    {
        $tips = $this->tips();

        return $tips[(int) date('z') % count($tips)];
    }

    /**
     * Total number of incantations available — useful for tests
     * and for "tip N of M" UI affordances.
     */
    public function count(): int
    {
        return count($this->tips());
    }
}

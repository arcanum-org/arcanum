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
                'body' => 'Shodo has a match directive that compiles to a PHP switch'
                    . ' with implicit break — comma-separated case values fall through.',
                'code' => "{{ match \$status }}\n"
                    . "    {{ case 'pending', 'active' }}<span>Active</span>\n"
                    . "    {{ case 'closed' }}<span>Closed</span>\n"
                    . '{{ endmatch }}',
            ],
            [
                'title' => 'Generate a typed query',
                'body' => 'make:query scaffolds a Query DTO, handler, and template in one step.'
                    . ' Pick a domain and a name.',
                'code' => 'php arcanum make:query Shop/Products',
            ],
            [
                'title' => 'Clear every framework cache at once',
                'body' => 'cache:clear walks every framework cache surface — templates, helpers,'
                    . ' page discovery, middleware discovery — plus any registered Vault stores.',
                'code' => 'php arcanum cache:clear',
            ],
            [
                'title' => 'Require auth on a DTO',
                'body' => 'Drop the #[RequiresAuth] attribute on a Command or Query DTO and the'
                    . ' AuthorizationGuard rejects unauthenticated requests with 401 before the'
                    . ' handler runs.',
                'code' => "#[RequiresAuth]\nfinal class DeleteAccount { }",
            ],
            [
                'title' => 'Stream a million-row export',
                'body' => 'Forge reads return a Sequencer that streams row-by-row. Iterate it'
                    . ' directly with foreach — peak memory stays flat regardless of result size.',
                'code' => "foreach (\$db->model->allEvents() as \$row) {\n"
                    . "    \$worker->push(\$row);\n"
                    . '}',
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

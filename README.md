# Arcanum Starter App

A CQRS application built on the [Arcanum framework](https://github.com/arcanum-org/framework). No controllers, no route files — just DTOs, handlers, and conventions.

## Quick Start

```bash
composer install
php -S localhost:8000 -t public
```

Then visit:
- `http://localhost:8000/health.json` — JSON health check
- `http://localhost:8000/health.html` — HTML fallback
- `http://localhost:8000/` — Home page (template-driven)

CLI:
```bash
php bin/arcanum query:health
php bin/arcanum query:health --verbose
php bin/arcanum list
```

## How It Works

Arcanum uses **CQRS** (Command Query Responsibility Segregation) instead of MVC. There are no controllers. The URL maps directly to a DTO class, and a handler processes it.

### Queries (read operations)

```
GET /health.json
 → resolves to App\Domain\Query\Health
 → dispatched to App\Domain\Query\HealthHandler
 → handler returns data → rendered as JSON
```

Create a query by adding two files:

```php
// app/Domain/Query/Health.php — the DTO
final class Health
{
    public function __construct(
        public readonly bool $verbose = false,  // populated from ?verbose=true
    ) {}
}

// app/Domain/Query/HealthHandler.php — the handler
final class HealthHandler
{
    public function __invoke(Health $query): array
    {
        return ['status' => 'ok'];
    }
}
```

That's it. No route registration needed — the convention does the work:
- `GET /shop/products.json` → `App\Domain\Shop\Query\Products`
- `GET /reports/summary.csv` → `App\Domain\Reports\Query\Summary`

### Commands (write operations)

```
PUT /orders/place
 → resolves to App\Domain\Orders\Command\Place
 → dispatched to App\Domain\Orders\Command\PlaceHandler
 → void handler → 204 No Content
```

HTTP method determines the handler prefix:
| Method | Handler | Response |
|---|---|---|
| PUT | `SubmitHandler` | void→204, DTO→201+Location, null→202 |
| POST | `PostSubmitHandler` | same |
| PATCH | `PatchSubmitHandler` | same |
| DELETE | `DeleteSubmitHandler` | same |

### Pages (template-driven routes)

Pages live in `app/Pages/`. A template file is all you need:

```
app/Pages/About.html  →  GET /about
```

Add an optional DTO for default data:

```php
// app/Pages/About.php
final class About
{
    public function __construct(
        public readonly string $title = 'About Us',
    ) {}
}
```

Templates use `{{ }}` syntax: `{{ $title }}`, `{{ foreach($items as $item) }}`, `{{ @csrf }}`.

## Directory Structure

```
app/
  Domain/           # CQRS handlers — organized by feature
    Query/          # Root-level queries (Health)
    Auth/           # Auth examples (Whoami, AdminOnly)
      Query/        # Read operations requiring auth
    Guestbook/      # Guestbook demo (htmx + ClientBroadcast events)
      Command/      # AddEntry command + handler
      Query/        # GetEntries query + handler + template
      Event/        # EntryAdded (ClientBroadcast)
      Model/        # Forge SQL model
  Pages/            # Template-driven routes (Index)
  Templates/        # Shared layouts and partials
    layout.html     # Base layout (Tailwind, htmx, nav, footer)
    partials/       # Reusable fragments (nav.html, footer.html)
  Http/             # HTTP kernel and middleware
  Cli/              # CLI kernel
  Error/            # Error handler

config/
  app.php           # Environment, debug, namespace, templates_directory
  auth.php          # Guard type and identity resolvers
  cache.php         # Cache drivers
  cors.php          # CORS policy
  formats.php       # Response formats and default (html)
  htmx.php          # htmx version pin, CDN URL, CSRF, auth redirect
  log.php           # Logging handlers and channels
  middleware.php    # Global HTTP middleware (Cors, RateLimit, Htmx)
  routes.php        # Custom route overrides

public/
  index.php         # HTTP entry point

bin/
  arcanum           # CLI entry point
```

## Validation

Add validation rules as attributes on DTO constructor parameters:

```php
final class Submit
{
    public function __construct(
        #[NotEmpty] #[MaxLength(100)]
        public readonly string $name,
        #[NotEmpty] #[Email]
        public readonly string $email,
    ) {}
}
```

Invalid input returns 422 with field-level errors. Rules are enforced automatically by `ValidationGuard` — no manual checking needed.

## Authentication

DTOs declare their auth requirements via attributes:

```php
#[RequiresAuth]                    // 401 if not authenticated
final class Whoami {}

#[RequiresRole('admin')]           // 403 if missing role
final class AdminOnly {}
```

Configure the guard in `config/auth.php`:
```php
'guard' => 'token',                // Bearer token auth
'guard' => 'session',              // Session cookie auth
'guard' => ['session', 'token'],   // Try both, first match wins
```

## Response Formats

The URL extension determines the response format:
```
GET /health.json  → application/json
GET /health.html  → text/html
GET /health.csv   → text/csv
GET /health.txt   → text/plain
GET /health       → default format (json, configurable in config/formats.php)
```

Restrict formats per-endpoint with `#[AllowedFormats]`:
```php
#[AllowedFormats('json', 'html')]
final class Products {}
// GET /products.csv → 406 Not Acceptable
```

## Testing

Handlers are plain classes — test them directly:

```php
public function testReturnsStatusOk(): void
{
    $query = new Health();
    $handler = new HealthHandler();
    $result = $handler($query);
    $this->assertSame('ok', $result['status']);
}
```

See `tests/Domain/Query/HealthHandlerTest.php` for a complete example.

## Error Messages

Arcanum exceptions implement the `ArcanumException` interface, which provides:

- **`getTitle()`** — stable, human-readable error category (e.g., "Service Not Found")
- **`getSuggestion()`** — optional fix hint shown when `app.verbose_errors` is enabled

When writing your own exceptions, implement `ArcanumException` so the framework's error renderers (JSON and HTML) display titles and suggestions automatically:

```php
use Arcanum\Glitch\ArcanumException;

class OrderNotFound extends \RuntimeException implements ArcanumException
{
    public function __construct(private readonly int $orderId)
    {
        parent::__construct("Order #{$this->orderId} not found");
    }

    public function getTitle(): string
    {
        return 'Order Not Found';
    }

    public function getSuggestion(): ?string
    {
        return 'Check the order ID — it may have been deleted or never existed';
    }
}
```

**`app.verbose_errors`** controls whether suggestions appear in responses. It defaults to `app.debug` when not set. You can enable suggestions without stack traces, or vice versa:

```php
// config/app.php
'debug' => false,            // no stack traces
'verbose_errors' => true,    // but show suggestions
```

For "did you mean?" suggestions, use `Strings::closestMatch()`:

```php
use Arcanum\Toolkit\Strings;

$closest = Strings::closestMatch($input, $available);
// Returns the nearest match or null if nothing is close enough
```

## Front-End: Tailwind CSS + htmx

The starter app ships with **Tailwind CSS** for styling and **htmx** for interactivity — no build step required for development.

### Development (zero-build)

The base layout (`app/Templates/layout.html`) loads Tailwind via CDN play script and htmx via `{{ Htmx::script() }}` (which pulls the pinned version from `config/htmx.php`). Just start the server:

```bash
php -S localhost:8000 -t public
```

The Tailwind CDN play script includes the full DESIGN.md color palette (parchment, copper, vellum, etc.) and font families (Lora, Inter, JetBrains Mono) as inline config.

### Dark mode

Dark mode uses Tailwind's `class` strategy with a toggle in the nav bar. It persists to `localStorage` and respects `prefers-color-scheme` on first visit. Use `dark:` prefixed classes:

```html
<div class="bg-vellum dark:bg-dark-vellum text-ink dark:text-[#e8e4db]">
```

### Templates and layouts

Templates use `{{ }}` syntax with layout inheritance:

```html
{{ extends 'layout' }}

{{ section 'title' }}My Page{{ endsection }}

{{ section 'content' }}
<h1>{{ $title }}</h1>
{{ endsection }}
```

Layouts live in `app/Templates/`. The `extends` directive looks for the layout in the same directory as the child template first, then falls back to `app/Templates/`. Partials use `{{ include 'partials/nav' }}`.

### htmx integration

The framework's `Arcanum\Htmx` package provides first-class htmx 4 support. The starter app registers three middleware in `config/middleware.php` — `HtmxRequestMiddleware`, `HtmxEventTriggerMiddleware`, and `HtmxAuthRedirectMiddleware` — and the layout includes `Htmx::script()` for the CDN script and `Htmx::csrf()` for automatic CSRF token injection.

The htmx version is pinned in `config/htmx.php` (currently `4.0.0-beta1`). To bump it, change the `version` key and update the `integrity` hash if you use SRI.

**How rendering works.** Your handlers always return the same data. On a normal browser request, the framework renders the full page with the layout. On an htmx partial request (with `HX-Target`), the framework automatically extracts just the target element from the template and returns it. No special handler logic needed.

**The guestbook demo** (`app/Domain/Guestbook/`) shows all of this in action:

- `GetEntries` query returns a list of guestbook entries. The template wraps the list in `<div id="guestbook-list">` with `hx-trigger="guestbook:entry:added from:body"` so it auto-refreshes when a new entry is added.
- `AddEntry` command validates the input (via `#[NotEmpty]`, `#[MinLength]`, `#[MaxLength]` attributes), inserts the entry, and dispatches an `EntryAdded` event.
- `EntryAdded` implements `ClientBroadcast`, so the framework automatically adds it as an `HX-Trigger` response header. The guestbook list element hears the event and re-fetches itself.

This is the CQRS + htmx pattern: commands dispatch domain events, events project as `HX-Trigger` headers, and listening elements refresh themselves. No JavaScript coordination required.

The welcome page also demonstrates element extraction with the incantation card refresh button and per-row diagnostic re-check buttons — each targets a specific `id` and the framework returns just that element.

See `src/Htmx/README.md` in the framework repo for the full package reference.

### Production build

The CDN play script is fine for development but not for production — it ships the entire Tailwind compiler to every visitor. The starter app includes a built-CSS path that takes over automatically when the bundle is present.

**Install the Tailwind standalone CLI** (no Node required):

```bash
# macOS
brew install tailwindcss

# Linux
curl -sLo /usr/local/bin/tailwindcss \
    https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-linux-x64
chmod +x /usr/local/bin/tailwindcss
```

**Build or watch:**

```bash
composer css:build   # one-shot, minified → public/css/app.min.css
composer css:watch   # rebuild on file changes
```

**How the layout chooses CDN vs built:**

The base layout calls `{{! App::cssTags() !}}`, which is provided by `App\Helpers\AppHelper`. At render time it checks for `public/css/app.min.css`:

- **File exists** → emits `<link rel="stylesheet" href="/css/app.min.css">`
- **File missing** → emits the Tailwind CDN play script with the inline config

No layout edits needed when shipping to production — just run `composer css:build` and the layout switches automatically.

**Production guardrail:**

If `APP_DEBUG=false` and `public/css/app.min.css` is missing, the front controller logs a warning to the default channel:

```
Production CSS bundle missing — run `composer css:build` to generate
public/css/app.min.css. The CDN play script is being served instead,
which is not suitable for production.
```

So a missed build step won't silently ship the CDN script to production traffic — it shows up in `files/logs/app.log` immediately.

## Development

After `composer install`, a pre-commit hook is installed automatically via `contrib/setup`. It runs code style checks, PHPStan, tests, and handler validation on every commit.

```bash
composer check                      # cs-check + phpstan + phpunit
php bin/arcanum validate:handlers   # verify every DTO has a matching handler
```

`validate:handlers` catches missing or misnamed handlers before they become runtime 404s.

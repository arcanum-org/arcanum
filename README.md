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
PUT /contact/submit
 → resolves to App\Domain\Contact\Command\Submit
 → dispatched to App\Domain\Contact\Command\SubmitHandler
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
    Contact/        # Contact feature
      Command/      # Write operations (Submit)
    Auth/           # Auth examples (Whoami, AdminOnly)
      Query/        # Read operations requiring auth
  Pages/            # Template-driven routes (Index, Contact)
  Http/             # HTTP kernel and middleware
  Cli/              # CLI kernel
  Error/            # Error handler

config/
  app.php           # Environment, debug, namespace
  auth.php          # Guard type and identity resolvers
  cache.php         # Cache drivers
  cors.php          # CORS policy
  formats.php       # Response formats (json, html, csv, txt)
  log.php           # Logging handlers and channels
  middleware.php    # Global HTTP middleware
  routes.php        # Custom route overrides and page format overrides

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

## Development

After `composer install`, a pre-commit hook is installed automatically via `contrib/setup`. It runs code style checks, PHPStan, tests, and handler validation on every commit.

```bash
composer check                      # cs-check + phpstan + phpunit
php bin/arcanum validate:handlers   # verify every DTO has a matching handler
```

`validate:handlers` catches missing or misnamed handlers before they become runtime 404s.

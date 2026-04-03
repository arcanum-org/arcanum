# arcanum
A robust CQRS framework for building powerful applications.

## Development

After `composer install`, a pre-commit hook is installed automatically via `contrib/setup`. It runs code style checks, PHPStan, tests, and handler validation on every commit.

You can also run these manually:

```bash
composer check              # cs-check + phpstan + phpunit
php bin/arcanum validate:handlers   # verify every DTO has a matching handler
```

`validate:handlers` catches missing or misnamed handlers before they become runtime 404s. It's included in the pre-commit hook so you'll never accidentally commit a DTO without its handler.

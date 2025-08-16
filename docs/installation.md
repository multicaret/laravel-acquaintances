# Installation

1) Install via Composer:

```bash
composer require multicaret/laravel-acquaintances
```

2) (Optional) Publish config and migrations for customization:

```bash
php artisan vendor:publish --provider="Multicaret\Acquaintances\AcquaintancesServiceProvider"
# or granular:
php artisan vendor:publish --tag=acquaintances-config
php artisan vendor:publish --tag=acquaintances-migrations
```

3) Run migrations:

```bash
php artisan migrate
```

Notes:
- By default, acquaintances.migrations = false in config to avoid auto-loading vendor migrations in host apps. See docs/migrations.md for details on when package migrations will auto-load.
- Package targets Laravel 9–12 (Illuminate components). Ensure your PHP is >= 8.0.

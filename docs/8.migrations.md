# Migrations

The package intentionally avoids auto-loading migrations by default to respect host apps that publish vendor migrations.

Key behavior:
- config('acquaintances.migrations') defaults to false.
- Service provider logic (registerMigrations):
  - If 'migrations' is true => load migrations from package.
  - If 'migrations' is null AND no published migrations matching *acquaintances* exist in database/migrations => load migrations from package.

Publish migrations with fresh timestamps:

```bash
php artisan vendor:publish --tag=acquaintances-migrations
```

This uses updateMigrationDate() to add current timestamps so files run in order.

Table names are configurable under config('acquaintances.tables.*'). Ensure your published migrations align with any overrides.

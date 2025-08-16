# Configuration

Publish config:

```bash
php artisan vendor:publish --tag=acquaintances-config
```

Key points (config/acquaintances.php):
- migrations: default false. When false, package will not auto-load its migrations. The service provider will auto-load if either:
  - 'migrations' is true; or
  - it is null and no published migrations with *acquaintances* exist in database/migrations.
- model_namespace: 'App' for app()->version() <= 7; 'App\\Models' otherwise.
- models: class names for internal package models (friendship, interaction_relation, etc.).
- tables: override table names if needed (friendships, friendship_groups, interactions, verifications, verification_groups).
- rating: defaults and custom types. Default type is 'general'.
- friendships_groups and verifications_groups: slug => numeric code mappings.

See also: docs/migrations.md

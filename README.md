# Laravel Acquaintances

[![Total Downloads](https://img.shields.io/packagist/dt/multicaret/laravel-acquaintances.svg?style=flat-square)](https://packagist.org/packages/multicaret/laravel-acquaintances)
[![Latest Version](https://img.shields.io/github/release/multicaret/laravel-acquaintances.svg?style=flat-square)](https://github.com/multicaret/laravel-acquaintances/releases)
[![License](https://poser.pugx.org/multicaret/laravel-acquaintances/license.svg?style=flat-square)](https://packagist.org/packages/multicaret/laravel-acquaintances)

<p align="center"><img src="https://cdn.multicaret.com/packages/assets/img/laravel-acquaintances.svg?updated=3" alt="Laravel Acquaintances"></p>

Clean, modular social features for Eloquent models: Friendships, Verifications, Interactions (Follow/Like/Favorite/Report/Subscribe/Vote/View), and multi-type Ratings.

- PHP >= 8.1
- Illuminate components ^9.0 | ^10.0 | ^11.0 | ^12.0 | ^13.0 (Laravel 9–13)
- Laravel News: https://laravel-news.com/manage-friendships-likes-and-more-with-the-acquaintances-laravel-package

## What's New in v4

- **UUID / ULID morph support** — configurable per table via `morphs`, `uuidMorphs`, or `ulidMorphs`.
- **Caching layer** — in-memory request cache + optional persistent cache for interaction checks with automatic invalidation.
- **`withAcquaintanceCounts()` macro** — eager-load interaction counts in a single query.
- **`HasAcquaintanceCounters` trait** — auto-increment/decrement counter columns on your model's table.
- **`AcquaintancesCleanupJob`** — queued mass deletion of acquaintance data for deleted models.
- **Database indexes** — composite and unique indexes on all tables for faster lookups.
- **Morph map registration** — automatic morph map for all package models.
- **Strict return types** on all traits and models.
- **DB transactions** for `blockFriend()` and `blockVerification()`.
- **Interactions-only mode** — disable friendships and/or verifications via `features` config to skip their migrations entirely. Granular per-feature publish tags available.

See [CHANGELOG.md](CHANGELOG.md) for the full list and [Upgrade Notes](docs/12.upgrade.md) for migration steps.

## TL;DR

```php
$user1 = User::find(1);
$user2 = User::find(2);

// Friendships
$user1->befriend($user2);
$user2->acceptFriendRequest($user1);

// The messy breakup :(
$user2->unfriend($user1);

// Verifications (message is optional)
$user1->verify($user2, "Worked together on several Laravel projects.");
$user2->acceptVerificationRequest($user1);

if ($user1->isVerifiedWith($user2)) {
    echo "Verified!";
}
```

## Documentation

To keep this README concise, the full documentation lives under docs/:
- [Overview](docs/1.overview.md)
- [Installation](docs/2.installation.md)
- [Configuration](docs/3.configuration.md)
- [Friendships](docs/4.friendships.md)
- [Verifications](docs/5.verifications.md)
- [Interactions (Follow/Like/Favorite/Report/Subscribe/Vote/View)](docs/6.interactions.md)
- [Ratings](docs/7.ratings.md)
- [Migrations](docs/8.migrations.md)
- [Events](docs/9.events.md)
- [Testing](docs/10.testing.md)
- [FAQ](docs/11.faq.md)
- [Upgrade Notes](docs/12.upgrade.md)

## Quickstart

1) Install

```bash
composer require multicaret/laravel-acquaintances
```

2) Publish (optional) and migrate

```bash
php artisan vendor:publish --provider="Multicaret\\Acquaintances\\AcquaintancesServiceProvider"
php artisan migrate
```

### Interactions-Only Mode

If you only need interactions (Follow, Like, Favorite, Subscribe, Vote, View, Report, Rate) and don't need Friendships or Verifications, disable them in `config/acquaintances.php`:

```php
'features' => [
    'interactions' => true,
    'friendships' => false,
    'verifications' => false,
],
```

This prevents friendship and verification migrations from being loaded or published, keeping your database lean.

You can also publish migrations for specific features using granular tags:

```bash
# Only interaction migrations
php artisan vendor:publish --tag=acquaintances-migrations-interactions

# Only friendship migrations
php artisan vendor:publish --tag=acquaintances-migrations-friendships

# Only verification migrations
php artisan vendor:publish --tag=acquaintances-migrations-verifications
```

### Custom User Model Location

If your `User` model lives in a non-standard namespace (e.g., `App\Models\Core\User`), set `user_model_class_name` in `config/acquaintances.php`:

```php
'user_model_class_name' => \App\Models\Core\User::class,
```

When `null` (default), the package resolves the user model from `model_namespace` + `models.user` (i.e., `App\Models\User`).

3) Add traits to your models

```php
use Multicaret\\Acquaintances\\Traits\\Friendable;
use Multicaret\\Acquaintances\\Traits\\Verifiable;
use Multicaret\\Acquaintances\\Traits\\CanFollow;
use Multicaret\\Acquaintances\\Traits\\CanBeFollowed;
use Multicaret\\Acquaintances\\Traits\\CanLike;
use Multicaret\\Acquaintances\\Traits\\CanBeLiked;
use Multicaret\\Acquaintances\\Traits\\CanRate;
use Multicaret\\Acquaintances\\Traits\\CanBeRated;

class User extends Model {
    use Friendable, Verifiable;
    use CanFollow, CanBeFollowed;
    use CanLike, CanBeLiked;
    use CanRate, CanBeRated;
}
```

Explore the feature guides linked above for full APIs and examples.

## Compatibility

| Version | PHP       | Laravel       |
|---------|-----------|---------------|
| v4      | >= 8.1    | 9, 10, 11, 12, 13 |
| v3.x    | >= 8.0    | 9, 10, 11, 12 |

## Contributing / Changelog

- Contributing: see CONTRIBUTING.md
- Changes: see [CHANGELOG.md](CHANGELOG.md)

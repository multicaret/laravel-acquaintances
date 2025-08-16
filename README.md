# Laravel Acquaintances

[![Total Downloads](https://img.shields.io/packagist/dt/multicaret/laravel-acquaintances.svg?style=flat-square)](https://packagist.org/packages/multicaret/laravel-acquaintances)
[![Latest Version](https://img.shields.io/github/release/multicaret/laravel-acquaintances.svg?style=flat-square)](https://github.com/multicaret/laravel-acquaintances/releases)
[![License](https://poser.pugx.org/multicaret/laravel-acquaintances/license.svg?style=flat-square)](https://packagist.org/packages/multicaret/laravel-acquaintances)

<p align="center"><img src="https://cdn.multicaret.com/packages/assets/img/laravel-acquaintances.svg?updated=3" alt="Laravel Acquaintances"></p>

Clean, modular social features for Eloquent models: Friendships, Verifications, Interactions (Follow/Like/Favorite/Report/Subscribe/Vote/View), and multi-type Ratings.

- PHP >= 8.0
- Illuminate components ^9.0 | ^10.0 | ^11.0 | ^12.0 (Laravel 9–12)
- Laravel News: https://laravel-news.com/manage-friendships-likes-and-more-with-the-acquaintances-laravel-package

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
- [Overview](docs/overview.md)
- [Installation](docs/installation.md)
- [Configuration](docs/configuration.md)
- [Friendships](docs/friendships.md)
- [Verifications](docs/verifications.md)
- [Interactions (Follow/Like/Favorite/Report/Subscribe/Vote/View)](docs/interactions.md)
- [Ratings](docs/ratings.md)
- [Migrations](docs/migrations.md)
- [Events](docs/events.md)
- [Testing](docs/testing.md)
- [FAQ](docs/faq.md)
- [Upgrade Notes](docs/upgrade.md)

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

- Laravel 9–12 (Illuminate components only; no laravel/framework hard dependency)
- PHP >= 8.0

## Contributing / Changelog

- Contributing: see CONTRIBUTING.md
- Changes: see CHANGELOG.md

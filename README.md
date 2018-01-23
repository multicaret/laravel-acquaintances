# Laravel 5 Acquaintances

_Please note this package is **totally working fine**, still needs documentation and tests thu!_

 [![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE)


This package gives Eloquent models the ability to manage their acquaintances.
You can easily design your social-like System (Facebook, Twitter, Foursquare...etc).

## Models can:
- Send Friend Requests
- Accept Friend Requests
- Deny Friend Requests
- Block a User
- Group Friends
- Follow a User or another Model i.e: Post
- Like a User or another Model i.e: Post
- Subscribe a User or another Model i.e: Post
- Favorite a User or another Model i.e: Post

## Installation

First, install the package through Composer.

```php
composer require liliom/laravel-acquaintances
```

### Laravel 5.5 and up

You don't have to do anything else, this package uses the Package Auto-Discovery feature, and should be available as soon as you install it via Composer.

### Laravel 5.4 and down

Then include the service provider inside `config/app.php`.

```php
'providers' => [
    ...
    Liliom\Acquaintances\AcquaintancesServiceProvider::class,
    ...
];
```

Publish config and migrations

```
php artisan vendor:publish --provider="Liliom\Acquaintances\AcquaintancesServiceProvider"
```
Configure the published config in
```
config\acquaintances.php
```
Finally, migrate the database
```
php artisan migrate
```

## Setup a Model
```php
use Liliom\Acquaintances\Traits\CanBeFollowed;
use Liliom\Acquaintances\Traits\CanFollow;
use Liliom\Acquaintances\Traits\CanLike;
use Liliom\Acquaintances\Traits\Friendable;
//...

class User extends Model
{
    use Friendable;
    use CanLike;
    use CanFollow, CanBeFollowed;
    //...
}
```

###MORE DETAILS TO BE ADDED VERY SOON!

## Contributing
See the [CONTRIBUTING](CONTRIBUTING.md) guide.

Basically this package is a collective work of following libraries, so the credits are to [laravel-friendships](https://github.com/hootlex/laravel-friendships)
& [laravel-follow](https://github.com/overtrue/laravel-follow).

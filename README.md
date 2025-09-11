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

All available APIs are listed below for Friendships, Verifications & Interactions.


---

## Friendships:

### Friend Requests:

Add `Friendable` Trait to User model.

```php
use Multicaret\Acquaintances\Traits\Friendable;

class User extends Model
{
    use Friendable;
}
```

#### Send a Friend Request

```php
$user->befriend($recipient);
```

#### Accept a Friend Request

```php
$user->acceptFriendRequest($sender);
```

#### Deny a Friend Request

```php
$user->denyFriendRequest($sender);
```

#### Remove Friend

```php
$user->unfriend($friend);
```

#### Block a Model

```php
$user->blockFriend($friend);
```

#### Unblock a Model

```php
$user->unblockFriend($friend);
```

#### Check if Model is Friend with another Model

```php
$user->isFriendWith($friend);
```

### Check Friend Requests:

#### Check if Model has a pending friend request from another Model

```php
$user->hasFriendRequestFrom($sender);
```

#### Check if Model has already sent a friend request to another Model

```php
$user->hasSentFriendRequestTo($recipient);
```

#### Check if Model has blocked another Model

```php
$user->hasBlocked($friend);
```

#### Check if Model is blocked by another Model

```php
$user->isBlockedBy($friend);
```

---

### Retrieve Friend Requests:

#### Get a single friendship

```php
$user->getFriendship($friend);
```

#### Get a list of all Friendships

```php
$user->getAllFriendships();
$user->getAllFriendships($group_name, $perPage = 20, $fields = ['id','name']);
```

#### Get a list of pending Friendships

```php
$user->getPendingFriendships();
$user->getPendingFriendships($group_name, $perPage = 20, $fields = ['id','name']);
```

#### Get a list of accepted Friendships

```php
$user->getAcceptedFriendships();
$user->getAcceptedFriendships($group_name, $perPage = 20, $fields = ['id','name']);
```

#### Get a list of denied Friendships

```php
$user->getDeniedFriendships();
$user->getDeniedFriendships($perPage = 20, $fields = ['id','name']);
```

#### Get a list of blocked Friendships in total

```php
$user->getBlockedFriendships();
$user->getBlockedFriendships($perPage = 20, $fields = ['id','name']);
```

#### Get a list of blocked Friendships by current user

```php
$user->getBlockedFriendshipsByCurrentUser();
$user->getBlockedFriendshipsByCurrentUser($perPage = 20, $fields = ['id','name']);
```

#### Get a list of blocked Friendships by others

```php
$user->getBlockedFriendshipsByOtherUsers();
$user->getBlockedFriendshipsByOtherUsers($perPage = 20, $fields = ['id','name']);
```

#### Get a list of pending Friend Requests

```php
$user->getFriendRequests();
```

#### Get the number of Friends

```php
$user->getFriendsCount();
```

#### Get the number of Pending Requests

```php
$user->getPendingsCount();
```

#### Get the number of mutual Friends with another user

```php
$user->getMutualFriendsCount($otherUser);
```

## Retrieve Friends:

To get a collection of friend models (ex. User) use the following methods:

#### `getFriends()`

```php
$user->getFriends();
// or paginated
$user->getFriends($perPage = 20, $group_name);
// or paginated with certain fields 
$user->getFriends($perPage = 20, $group_name, $fields = ['id','name']);
// or paginated with cursor & certain fields
$user->getFriends($perPage = 20, $group_name, $fields = ['id','name'], $cursor = true);
```

Parameters:

* `$perPage`: integer (default: `0`), Get values paginated
* `$group_name`: string (default: `''`), Get collection of Friends in specific group paginated
* `$fields`: array (default: `['*']`), Specify the desired fields to query.

#### `getFriendsOfFriends()`

```php
$user->getFriendsOfFriends();
// or
$user->getFriendsOfFriends($perPage = 20);
// or 
$user->getFriendsOfFriends($perPage = 20, $fields = ['id','name']);
```

Parameters:

* `$perPage`: integer (default: `0`), Get values paginated
* `$fields`: array (default: `['*']`), Specify the desired fields to query.

#### `getMutualFriends()`

Get mutual Friends with another user

```php
$user->getMutualFriends($otherUser);
// or 
$user->getMutualFriends($otherUser, $perPage = 20);
// or 
$user->getMutualFriends($otherUser, $perPage = 20, $fields = ['id','name']);
```

Parameters:

* `$other`: Model (required), The Other user model to check mutual friends with
* `$perPage`: integer (default: `0`), Get values paginated
* `$fields`: array (default: `['*']`), Specify the desired fields to query.

## Friend Groups:

The friend groups are defined in the `config/acquaintances.php` file. The package comes with a few default groups. To
modify them, or add your own, you need to specify a `slug` and a `key`.

```php
// config/acquaintances.php
//...
'groups' => [
    'acquaintances' => 0,
    'close_friends' => 1,
    'family' => 2
];
```

Since you've configured friend groups, you can group/ungroup friends using the following methods.

#### Group a Friend

```php
$user->groupFriend($friend, $group_name);
```

#### Remove a Friend from family group

```php
$user->ungroupFriend($friend, 'family');
```

#### Remove a Friend from all groups

```php
$user->ungroupFriend($friend);
```

#### Get the number of Friends in specific group

```php
$user->getFriendsCount($group_name);
```

#### To filter `friendships` by group you can pass a group slug.

```php
$user->getAllFriendships($group_name);
$user->getAcceptedFriendships($group_name);
$user->getPendingFriendships($group_name);
...
```

---

## Verifications:

### Verification Requests:

Add `Verifiable` Trait to User model.

```php
use Multicaret\Acquaintances\Traits\Verifiable;

class User extends Model
{
    use Verifiable;
}
```

#### Send a Verification Request

```php
$user->verify($recipient, $message);
```

**Note:** The `$message` parameter is optional for verification requests. This message should ideally contain information about how the verifier knows the recipient and why they are vouching for them, but it's 'required' status is left to your discretion.

Examples:
```php
$user->verify($recipient, "I've worked with John for 2 years at ABC Company and can vouch for his expertise in Laravel development.");
$user->verify($recipient, "I met Sarah at the Laravel conference and she gave an excellent presentation on testing strategies.");
```

#### Accept a Verification Request

```php
$user->acceptVerificationRequest($sender);
```

#### Deny a Verification Request

```php
$user->denyVerificationRequest($sender);
```

#### Remove Verification

```php
$user->unverify($recipient);
```

#### Block a User's Verifications

```php
$user->blockVerification($recipient);
```

#### Unblock a User's Verifications

```php
$user->unblockVerification($recipient);
```

#### Check if User is Verified with another User

```php
$user->isVerifiedWith($recipient);
```

### Check Verification Requests:

#### Check if User has a pending verification request from another User

```php
$user->hasVerificationRequestFrom($sender);
```

#### Check if User has already sent a verification request to another User

```php
$user->hasSentVerificationRequestTo($recipient);
```

#### Check if User can verify another User

```php
$user->canVerify($recipient);
```

---

### Retrieve Verification Requests:

#### Get a single verification

```php
$user->getVerification($recipient);
```

#### Get a list of all Verifications

```php
$user->getAllVerifications();
$user->getAllVerifications($group_name, $perPage = 20, $fields = ['*'], $type = 'all');
```

#### Get a list of pending Verifications

```php
$user->getPendingVerifications();
$user->getPendingVerifications($group_name, $perPage = 20, $fields = ['*'], $type = 'all');
```

#### Get a list of accepted Verifications

```php
$user->getAcceptedVerifications();
$user->getAcceptedVerifications($group_name, $perPage = 20, $fields = ['*'], $type = 'all');
```

#### Get a list of denied Verifications

```php
$user->getDeniedVerifications();
$user->getDeniedVerifications($perPage = 20, $fields = ['*']);
```

#### Get a list of pending Verification Requests

```php
$user->getVerificationRequests();
```

#### Get the number of Verifiers

```php
$user->getVerifiersCount();
$user->getVerifiersCount($group_name, $type = 'all');
```

#### Get the number of Pending Verification Requests

```php
$user->getPendingVerificationsCount();
```

#### Get the number of mutual Verifiers with another user

```php
$user->getMutualVerifiersCount($otherUser);
```

## Retrieve Verifiers:

To get a collection of verifier models (ex. User) use the following methods:

#### `getVerifiers()`

```php
$user->getVerifiers();
// or paginated
$user->getVerifiers($perPage = 20, $group_name = '', $fields = ['*'], $cursor = false);
```

Parameters:

* `$perPage`: integer (default: `0`), Get values paginated
* `$group_name`: string (default: `''`), Get collection of Verifiers in specific group paginated
* `$fields`: array (default: `['*']`), Specify the desired fields to query.
* `$cursor`: boolean (default: `false`), Use cursor pagination

#### `getVerifiersOfVerifiers()`

```php
$user->getVerifiersOfVerifiers();
// or
$user->getVerifiersOfVerifiers($perPage = 20);
// or 
$user->getVerifiersOfVerifiers($perPage = 20, $fields = ['*']);
```

Parameters:

* `$perPage`: integer (default: `0`), Get values paginated
* `$fields`: array (default: `['*']`), Specify the desired fields to query.

#### `getMutualVerifiers()`

Get mutual Verifiers with another user

```php
$user->getMutualVerifiers($otherUser);
// or 
$user->getMutualVerifiers($otherUser, $perPage = 20);
// or 
$user->getMutualVerifiers($otherUser, $perPage = 20, $fields = ['*']);
```

Parameters:

* `$otherUser`: Model (required), The Other user model to check mutual verifiers with
* `$perPage`: integer (default: `0`), Get values paginated
* `$fields`: array (default: `['*']`), Specify the desired fields to query.

## Verification Groups:

The verification groups are defined in the `config/acquaintances.php` file. These groups categorize the type of verification method used. To modify them, or add your own, you need to specify a `slug` and a `key`.

```php
// config/acquaintances.php
//...
'verifications_groups' => [
    'text' => 0,
    'phone' => 1,
    'cam' => 2,
    'personally' => 3,
    'intimately' => 4
];
```

Since you've configured verification groups, you can group/ungroup verifications using the following methods.

#### Group a Verification

```php
$user->groupVerification($verifier, $group_name);
```

#### Remove a Verification from specific group

```php
$user->ungroupVerification($verifier, 'text');
```

#### Remove a Verification from all groups

```php
$user->ungroupVerification($verifier);
```

#### Get the number of Verifiers in specific group

```php
$user->getVerifiersCount($group_name);
```

#### To filter `verifications` by group you can pass a group slug.

```php
$user->getAllVerifications($group_name);
$user->getAcceptedVerifications($group_name);
$user->getPendingVerifications($group_name);
...
```

## Interactions

### Traits Usage:

Add `CanXXX` Traits to User model.

```php
use Multicaret\Acquaintances\Traits\CanFollow;
use Multicaret\Acquaintances\Traits\CanLike;
use Multicaret\Acquaintances\Traits\CanFavorite;
use Multicaret\Acquaintances\Traits\CanSubscribe;
use Multicaret\Acquaintances\Traits\CanVote;

class User extends Model
{
    use CanFollow, CanLike, CanFavorite, CanSubscribe, CanVote;
}
```

Add `CanBeXXX` Trait to target model, such as 'Post' or 'Book' ...:

```php
use Multicaret\Acquaintances\Traits\CanBeLiked;
use Multicaret\Acquaintances\Traits\CanBeFavorited;
use Multicaret\Acquaintances\Traits\CanBeVoted;
use Multicaret\Acquaintances\Traits\CanBeRated;

class Post extends Model
{
    use CanBeLiked, CanBeFavorited, CanBeVoted, CanBeRated;
}
```

Explore the feature guides linked above for full APIs and examples.

## Compatibility

- Laravel 9–12 (Illuminate components only; no laravel/framework hard dependency)
- PHP >= 8.0

## Contributing / Changelog

- Contributing: see CONTRIBUTING.md
- Changes: see CHANGELOG.md

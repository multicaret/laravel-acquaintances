# Laravel Acquaintances

[![Total Downloads](https://img.shields.io/packagist/dt/multicaret/laravel-acquaintances.svg?style=flat-square)](https://packagist.org/packages/multicaret/laravel-acquaintances)
[![Latest Version](https://img.shields.io/github/release/multicaret/laravel-acquaintances.svg?style=flat-square)](https://github.com/multicaret/laravel-acquaintances/releases)
[![License](https://poser.pugx.org/multicaret/laravel-acquaintances/license.svg?style=flat-square)](https://packagist.org/packages/multicaret/laravel-acquaintances)

<p align="center"><img src="https://cdn.multicaret.com/packages/assets/img/laravel-acquaintances.svg?updated=3"></p>

[Laravel News Article](https://laravel-news.com/manage-friendships-likes-and-more-with-the-acquaintances-laravel-package)

Supports Laravel 12, with no dependencies

## TL;DR

Gives eloquent models:

- Friendships & Groups ability
- Verifications & Groups ability
- Interactions ability such as:
    - Likes
    - Favorites
    - Reporting
    - Votes (up/down)
    - Subscribe
    - Follow
    - Ratings
    - Views

Take this example:

```php
$user1 = User::find(1);
$user2 = User::find(2);

// Friendships
$user1->befriend($user2);
$user2->acceptFriendRequest($user1);

// The messy breakup :(
$user2->unfriend($user1);

// Verifications work similarly to Friends, with an optional from the verifier
$message = "I've met John and worked with him. He's an excellent developer and trustworthy colleague.";
$user1->verify($user2, $message);
$user2->acceptVerificationRequest($user1);

// Check if users are verified with each other
if ($user1->isVerifiedWith($user2)) {
    echo "These users have verified each other!";
}
```

1. [Introduction](#introduction)
1. [Installation](#installation)
2. [Friendships:](#friendships)
    * [Friend Requests](#friend-requests)
    * [Check Friend Requests](#check-friend-requests)
    * [Retrieve Friend Requests](#retrieve-friend-requests)
    * [Retrieve Friends](#retrieve-friends)
    * [Friend Groups](#friend-groups)
3. [Verifications:](#verifications)
    * [Verification Requests](#verification-requests)
    * [Check Verification Requests](#check-verification-requests)
    * [Retrieve Verification Requests](#retrieve-verification-requests)
    * [Retrieve Verifications](#retrieve-verifications)
    * [Verification Groups](#verification-groups)
4. [Interactions](#interactions)
    * [Traits Usage](#traits-usage)
    * [Follow](#follow)
    * [Rate](#rate)
    * [Like](#like)
    * [Favorite](#favorite)
    * [Subscribe](#subscribe)
    * [Vote](#vote)
    * [View](#view)
    * [Parameters](#parameters)
    * [Query relations](#query-relations)
    * [Working with model](#working-with-model)
5. [Events](#events)
6. [Contributing](#contributing)

## Introduction

This light package gives Eloquent models the ability to manage their acquaintances and other cool useful stuff. You can
easily design your social-like System (Facebook, Twitter, Foursquare...etc).

##### Acquaintances includes:

- Send Friend Requests
- Accept Friend Requests
- Deny Friend Requests
- Block a User
- Group Friends
- Send Verification Requests
- Accept Verification Requests
- Deny Verification Requests
- Group Verifications
- Rate a User or a Model, supporting multiple aspects
- Follow a User or a Model
- Like a User or a Model
- Subscribe a User or a Model
- Favorite a User or a Model
- Vote (Upvote & Downvote a User or a Model)
- View a User or a Model

---

## Installation

First, install the package through Composer.

```sh
composer require multicaret/laravel-acquaintances
```

Laravel 5.8 and up => version 2.x (branch master)

Laravel 5.7 and below => version 1.x (branch v1)

Publish config and migrations:

```sh
php artisan vendor:publish --provider="Multicaret\Acquaintances\AcquaintancesServiceProvider"
```

Configure the published config in:

```
config/acquaintances.php
```

Finally, migrate the database to create the table:

```sh
php artisan migrate
```

---

## Setup a Model

Example:

```php
use Multicaret\Acquaintances\Traits\Friendable;
use Multicaret\Acquaintances\Traits\Verifiable;
use Multicaret\Acquaintances\Traits\CanFollow;
use Multicaret\Acquaintances\Traits\CanBeFollowed;
use Multicaret\Acquaintances\Traits\CanLike;
use Multicaret\Acquaintances\Traits\CanBeLiked;
use Multicaret\Acquaintances\Traits\CanRate;
use Multicaret\Acquaintances\Traits\CanBeRated;
//...

class User extends Model
{
    use Friendable;
    use Verifiable;
    use CanFollow, CanBeFollowed;
    use CanLike, CanBeLiked;
    use CanRate, CanBeRated;
    //...
}
```

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

#### Get a list of blocked Verifications

```php
$user->getBlockedVerifications();
$user->getBlockedVerifications($perPage = 20, $fields = ['*']);
```

#### Get a list of blocked Verifications by current user

```php
$user->getBlockedVerificationsByCurrentUser();
$user->getBlockedVerificationsByCurrentUser($perPage = 20, $fields = ['*']);
```

#### Get a list of blocked Verifications by others

```php
$user->getBlockedVerificationsByOtherUsers();
$user->getBlockedVerificationsByOtherUsers($perPage = 20, $fields = ['*']);
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

All available APIs are listed below.

### Follow

#### `\Multicaret\Acquaintances\Traits\CanFollow`

```php
$user->follow($targets);
$user->unfollow($targets);
$user->toggleFollow($targets);
$user->followings()->get(); // App\User:class
$user->followings(App\Post::class)->get();
$user->isFollowing($target);
$object->followingCount(); // or as attribute $object->following_count
$object->followingCountReadable(); // return readable number with precision, i.e: 5.2K
```

#### `\Multicaret\Acquaintances\Traits\CanBeFollowed`

```php
$object->followers()->get();
$object->isFollowedBy($user);
$object->followersCount(); // or as attribute $object->followers_count
$object->followersCountReadable(); // return readable number with precision, i.e: 5.2K
```

### Rate

#### `\Multicaret\Acquaintances\Traits\CanRate`

```php
// Rate type in the following line will be
// the same as the one specified
// in config('acquaintances.rating.defaults.type')
// if your app is using a single type of rating on your model,
// like one factor only, then simply use the rate() as it's shown here,
// and if you have multiple factors then
// take a look the examples exactly below this these ones. 
$user->rate($targets);
$user->unrate($targets);
$user->toggleRate($targets);
$user->ratings()->get(); // App\User:class
$user->ratings(App\Post::class)->get();
$user->hasRated($target);

// Some Examples on how to rate the object based on different factors (rating type)
$user->setRateType('bedside-manners')->rate($target, 4);
$user->setRateType('waiting-time')->rate($target, 3);
$user->setRateType('quality')->rate($target, 4);
$user->setRateType('delivery-time')->rate($target, 2);
$user->setRateType('communication')->rate($target, 5);
// Remember that you can always use the functions on $target which have this phrase "AllTypes" in them. check the below section for more details
```

#### `\Multicaret\Acquaintances\Traits\CanBeRated`

```php
$object->raters()->get();
$object->isRatedBy($user);

$object->averageRating(); // or as attribute $object->average_rating
$object->averageRatingAllTypes(); // or as attribute $object->average_rating_all_types

$object->sumRating(); // or as attribute $object->sum_rating
$object->sumRatingAllTypes(); // or as attribute $object->sum_rating_all_types_all_types

$object->sumRatingReadable(); // return readable number with precision, i.e: 5.2K
$object->sumRatingAllTypesReadable(); // return readable number with precision, i.e: 5.2K


$object->ratingPercent($max = 5); // calculating the percentage based on the passed coefficient
$object->ratingPercentAllTypes($max = 5); // calculating the percentage based on the passed coefficient

// User Related: 

$object->userAverageRatingAllTypes(); // or as attribute $object->user_average_rating_all_types

$object->userSumRatingAllTypes(); // or as attribute $object->user_sum_rating_all_types

$object->userSumRatingReadable(); // return readable number with precision, i.e: 5.2K
$object->userSumRatingAllTypesReadable(); // return readable number with precision, i.e: 5.2K


```

### Like

#### `\Multicaret\Acquaintances\Traits\CanLike`

```php
$user->like($targets);
$user->unlike($targets);
$user->toggleLike($targets);
$user->hasLiked($target);
$user->likes()->get(); // default object: App\User:class
$user->likes(App\Post::class)->get();
```

#### `\Multicaret\Acquaintances\Traits\CanBeLiked`

```php
$object->likers()->get();
$object->fans()->get(); // or $object->fans. it's an alias of likers()
$object->isLikedBy($user);
$object->likersCount(); // or as attribute $object->likers_count
$object->likersCountReadable(); // return readable number with precision, i.e: 5.2K
```

### Favorite

#### `\Multicaret\Acquaintances\Traits\CanFavorite`

```php
$user->favorite($targets);
$user->unfavorite($targets);
$user->toggleFavorite($targets);
$user->hasFavorited($target);
$user->favorites()->get(); // App\User:class
$user->favorites(App\Post::class)->get();
```

#### `\Multicaret\Acquaintances\Traits\CanBeFavorited`

```php
$object->favoriters()->get(); // or $object->favoriters 
$object->isFavoritedBy($user);
$object->favoritersCount(); // or as attribute $object->favoriters_count
$object->favoritersCountReadable(); // return readable number with precision, i.e: 5.2K
```


### Reporting

#### `\Multicaret\Acquaintances\Traits\CanReport`

```php
$user->report($targets);
$user->unreport($targets);
$user->toggleReport($targets);
$user->hasReported($target);
$user->reports()->get(); // App\User:class
$user->reports(App\Post::class)->get();
```

#### `\Multicaret\Acquaintances\Traits\CanBeReported`

```php
$object->reporters()->get(); // or $object->reporters
$object->isReportedBy($user);
$object->reportersCount(); // or as attribute $object->reporters_count
$object->reportersCountReadable(); // return readable number with precision, i.e: 5.2K
```

### Subscribe

#### `\Multicaret\Acquaintances\Traits\CanSubscribe`

```php
$user->subscribe($targets);
$user->unsubscribe($targets);
$user->toggleSubscribe($targets);
$user->hasSubscribed($target);
$user->subscriptions()->get(); // default object: App\User:class
$user->subscriptions(App\Post::class)->get();
```

#### `Multicaret\Acquaintances\Traits\CanBeSubscribed`

```php
$object->subscribers(); // or $object->subscribers 
$object->isSubscribedBy($user);
$object->subscribersCount(); // or as attribute $object->subscribers_count
$object->subscribersCountReadable(); // return readable number with precision, i.e: 5.2K
```

### Vote

#### `\Multicaret\Acquaintances\Traits\CanVote`

```php
$user->vote($target); // Vote with 'upvote' for default
$user->upvote($target);
$user->downvote($target);
$user->cancelVote($target);
$user->hasUpvoted($target);
$user->hasDownvoted($target);
$user->votes(App\Post::class)->get();
$user->upvotes(App\Post::class)->get();
$user->downvotes(App\Post::class)->get();
```

#### `\Multicaret\Acquaintances\Traits\CanBeVoted`

```php
$object->voters()->get();
$object->isVotedBy($user);
$object->votersCount(); // or as attribute $object->voters_count
$object->votersCountReadable(); // return readable number with precision, i.e: 5.2K

$object->upvoters()->get();
$object->isUpvotedBy($user);
$object->upvotersCount(); // or as attribute $object->upvoters_count
$object->upvotersCountReadable(); // return readable number with precision, i.e: 5.2K

$object->downvoters()->get();
$object->isDownvotedBy($user);
$object->downvotersCount(); // or as attribute $object->downvoters_count
$object->downvotersCountReadable(); // return readable number with precision, i.e: 5.2K
```

### View

#### `\Multicaret\Acquaintances\Traits\CanView`

```php
$user->view($targets);
$user->unview($targets);
$user->toggleView($targets);
$user->hasViewed($target);
$user->viewers()->get(); // default object: App\User:class
$user->viewers(App\Post::class)->get();
```

#### `\Multicaret\Acquaintances\Traits\CanBeViewed`

```php
$object->viewers()->get();
$object->isViewedBy($user);
$object->viewersCount(); // or as attribute $object->viewers_count
$object->viewersCountReadable(); // return readable number with precision, i.e: 5.2K
```

### Parameters

All the above mentioned methods of creating relationships, such as 'follow', 'like', 'unfollow', 'unlike', their syntax
is as follows:

```php
follow(array|int|\Illuminate\Database\Eloquent\Model $targets, $class = __CLASS__)
```

So you can call them like this:

```php
// id / int|array
$user->follow(1); // targets: 1, $class = App\User
$user->follow(1, App\Post::class); // targets: 1, $class = App\Post
$user->follow([1, 2, 3]); // targets: [1, 2, 3], $class = App\User

// Model
$post = App\Post::find(7);
$user->follow($post); // targets: $post->id, $class = App\Post

// Model array
$posts = App\Post::popular()->get();
$user->follow($posts); // targets: [1, 2, ...], $class = App\Post
```

### Query relations

```php
$followers = $user->followers;
$followers = $user->followers()->where('id', '>', 10)->get();
$followers = $user->followers()->orderByDesc('id')->get();
$followers = $user->followers()->paginate(10);
```

You may use the others in the same way.

### Working with model

```php
use Multicaret\Acquaintances\Models\InteractionRelation;

// Get most popular object
// 1- All types
$relations = InteractionRelation::popular()->get();

// 2- subject_type = App\Post
$relations = InteractionRelation::popular(App\Post::class)->get(); 

// 3- subject_type = App\User
$relations = InteractionRelation::popular('user')->get();
 
// 4- subject_type = App\Post
$relations = InteractionRelation::popular('post')->get();

// 5- Pagination
$relations = InteractionRelation::popular(App\Post::class)->paginate(15); 

```

## Events

This is the list of the events fired by default for each action:

| Event name                    | Fired                                         |
|-------------------------------|-----------------------------------------------|
| acq.friendships.sent          | When a friend request is sent                 |
| acq.friendships.accepted      | When a friend request is accepted             |
| acq.friendships.denied        | When a friend request is denied               |
| acq.friendships.blocked       | When a friend is blocked                      |
| acq.friendships.unblocked     | When a friend is unblocked                    |
| acq.friendships.cancelled     | When a friendship is cancelled                |
| acq.verifications.sent        | When a verification request is sent           |
| acq.verifications.accepted    | When a verification request is accepted       |
| acq.verifications.denied      | When a verification request is denied         |
| acq.verifications.blocked     | When a verifier is blocked                    |
| acq.verifications.unblocked   | When a verifier is unblocked                  |
| acq.verifications.cancelled   | When a verification is cancelled              |
| acq.ratings.rate              | When a an item or items get Rated             |
| acq.ratings.unrate            | When a an item or items get unRated           |
| acq.vote.up                   | When a an item or items get upvoted           |
| acq.vote.down                 | When a an item or items get downvoted         |
| acq.vote.cancel               | When a an item or items get vote cancellation |
| acq.likes.like                | When a an item or items get liked             |
| acq.likes.unlike              | When a an item or items get unliked           |
| acq.followships.follow        | When a an item or items get followed          |
| acq.followships.unfollow      | When a an item or items get unfollowed        |
| acq.favorites.favorite        | When a an item or items get favored           |
| acq.favorites.unfavorite      | When a an item or items get unfavored         |
| acq.reports.report            | When a an item or items get reported          |
| acq.reports.unreport          | When a an item or items get unreported        |
| acq.subscriptions.subscribe   | When a an item or items get subscribed        |                 
| acq.subscriptions.unsubscribe | When a an item or items get unsubscribed      | 
| acq.views.view                | When a an item or items get viewed            |
| acq.views.unview              | When a an item or items get unviewed          |                

### Contributing

See the [CONTRIBUTING](CONTRIBUTING.md) guide.

The initial version of this library was assisted by the following
repos [laravel-friendships](https://github.com/hootlex/laravel-friendships)
& [laravel-follow](https://github.com/overtrue/laravel-follow).

### Change Log

See the [log](CHANGELOG.md) file.

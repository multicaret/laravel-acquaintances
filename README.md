# Laravel 5 Acquaintances
<p align="center"><img src="https://cdn.liliomlab.com/packages/assets/img/laravel-acquaintances.svg"></p>

<p align="center">
<a href="https://packagist.org/packages/liliom/laravel-acquaintances"><img src="https://poser.pugx.org/liliom/laravel-acquaintances/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/liliom/laravel-acquaintances"><img src="https://poser.pugx.org/liliom/laravel-acquaintances/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/liliom/laravel-acquaintances"><img src="https://poser.pugx.org/liliom/laravel-acquaintances/license.svg" alt="License"></a>
</p>

1. [Introduction](#introduction)
1. [Installation](#installation)
2. [Friendships:](#friendships)
    * [Friend Requests](#friend-requests)
    * [Check Friend Requests](#check-friend-requests)
    * [Retrieve Friend Requests](#retrieve-friend-requests)
    * [Retrieve Friends](#retrieve-friends)
    * [Friend Groups](#friend-groups)
3. [Interactions](#interactions)
    * [Traits Usage](#traits-usage)
    * [Follow](#follow)
    * [Rate](#rate)
    * [Like](#like)
    * [Favorite](#favorite)
    * [Subscribe](#subscribe)
    * [Vote](#vote)
    * [Parameters](#parameters)
    * [Query relations](#query-relations)
    * [Working with model](#working-with-model)
4. [Events](#events)
5. [Contributing](#contributing)


## Introduction
This light package gives Eloquent models the ability to manage their acquaintances and other cool useful stuff.
You can easily design your social-like System (Facebook, Twitter, Foursquare...etc).
##### Acquaintances includes:
- Send Friend Requests
- Accept Friend Requests
- Deny Friend Requests
- Block a User
- Group Friends
- Rate a User or a Model, supporting multiple aspects
- Follow a User or a Model
- Like a User or a Model
- Subscribe a User or a Model
- Favorite a User or a Model
- Vote (Upvote & Downvote a User or a Model)

---
## Installation

First, install the package through Composer.

```sh
$ composer require liliom/laravel-acquaintances
```

#### Laravel 5.5 and up

You don't have to do anything else, this package uses the Package Auto-Discovery feature, and should be available as soon as you install it via Composer.

#### Laravel 5.4 and down

Then include the service provider inside `config/app.php`.

```php
'providers' => [
    ...
    Liliom\Acquaintances\AcquaintancesServiceProvider::class,
    ...
];
```

Publish config and migrations:
```sh
$ php artisan vendor:publish --provider="Liliom\Acquaintances\AcquaintancesServiceProvider"
```

Configure the published config in:
```
config\acquaintances.php
```

Finally, migrate the database to create the table:
```sh
$ php artisan migrate
```

---
## Setup a Model

Example:
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
    use CanRate, CanBeRated;
    //...
}
```
All available APIs are listed below for Friendships & Interactions.


---
## Friendships:
### Friend Requests:
Add `Friendable` Trait to User model.

```php
use Liliom\Acquaintances\Traits\Friendable;

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
```

#### Get a list of pending Friendships
```php
$user->getPendingFriendships();
```

#### Get a list of accepted Friendships
```php
$user->getAcceptedFriendships();
```

#### Get a list of denied Friendships
```php
$user->getDeniedFriendships();
```

#### Get a list of blocked Friendships
```php
$user->getBlockedFriendships();
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
// or panigated
$user->getFriends($perPage = 20, $group_name);
// or paginated with certain fields 
$user->getFriends($perPage = 20, $group_name, $fields = ['id','name']);
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
The friend groups are defined in the `config/acquaintances.php` file.
The package comes with a few default groups.
To modify them, or add your own, you need to specify a `slug` and a `key`.

```php
// config/acquaintances.php
...
'groups' => [
    'acquaintances' => 0,
    'close_friends' => 1,
    'family' => 2
]
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

## Interactions
### Traits Usage:
Add `CanXXX` Traits to User model.

```php
use Liliom\Acquaintances\Traits\CanFollow;
use Liliom\Acquaintances\Traits\CanLike;
use Liliom\Acquaintances\Traits\CanFavorite;
use Liliom\Acquaintances\Traits\CanSubscribe;
use Liliom\Acquaintances\Traits\CanVote;

class User extends Model
{
    use CanFollow, CanLike, CanFavorite, CanSubscribe, CanVote;
}
```

Add `CanBeXXX` Trait to target model, such as 'Post' or 'Book' ...:

```php
use Liliom\Acquaintances\Traits\CanBeLiked;
use Liliom\Acquaintances\Traits\CanBeFavorited;
use Liliom\Acquaintances\Traits\CanBeVoted;
use Liliom\Acquaintances\Traits\CanBeRated;

class Post extends Model
{
    use CanBeLiked, CanBeFavorited, CanBeVoted, CanBeRated;
}
```

All available APIs are listed below.

### Follow

#### `\Liliom\Acquaintances\Traits\CanFollow`

```php
$user->follow($targets)
$user->unfollow($targets)
$user->toggleFollow($targets)
$user->followings()->get() // App\User:class
$user->followings(App\Post::class)->get()
$user->isFollowing($target)
```

#### `\Liliom\Acquaintances\Traits\CanBeFollowed`

```php
$object->followers()->get()
$object->isFollowedBy($user)
$object->followersCount() // or as attribute $object->followers_count
$object->followersCountReadable() // return readable number with precision, i.e: 5.2K
```

### Rate

#### `\Liliom\Acquaintances\Traits\CanRate`

```php
$user->rate($targets)
$user->unrate($targets)
$user->toggleRate($targets)
$user->ratings()->get() // App\User:class
$user->rateings(App\Post::class)->get()
$user->hasRated($target)
```

#### `\Liliom\Acquaintances\Traits\CanBeRated`

```php
$object->raters()->get()
$object->isRatedBy($user)
$object->averageRating() // or as attribute $object->average_rating
$object->sumRating() // or as attribute $object->sum_rating
$object->sumRatingReadable()
$object->userAverageRating() // or as attribute $object->user_average_rating
$object->userSumRating() // or as attribute $object->user_sum_rating
$object->userSumRatingReadable() // return readable number with precision, i.e: 5.2K
$object->ratingPercent($max = 5) // calculating the percentage based on the passed coefficient
```

### Like

#### `\Liliom\Acquaintances\Traits\CanLike`

```php
$user->like($targets)
$user->unlike($targets)
$user->toggleLike($targets)
$user->hasLiked($target)
$user->likes()->get() // default object: App\User:class
$user->likes(App\Post::class)->get()
```

#### `\Liliom\Acquaintances\Traits\CanBeLiked`

```php
$object->likers()->get() //
$object->fans()->get() // or $object->fans. it's an alias of likers()
$object->isLikedBy($user)
$object->likersCount() // or as attribute $object->likers_count
$object->likersCountReadable() // return readable number with precision, i.e: 5.2K
```

### Favorite

#### `\Liliom\Acquaintances\Traits\CanFavorite`

```php
$user->favorite($targets)
$user->unfavorite($targets)
$user->toggleFavorite($targets)
$user->hasFavorited($target)
$user->favorites()->get() // App\User:class
$user->favorites(App\Post::class)->get()
```

#### `\Liliom\Acquaintances\Traits\CanBeFavorited`

```php
$object->favoriters()->get() // or $object->favoriters 
$object->isFavoritedBy($user)
$object->favoritersCount() // or as attribute $object->favoriters_count
$object->favoritersCountReadable() // return readable number with precision, i.e: 5.2K
```

### Subscribe

#### `\Liliom\Acquaintances\Traits\CanSubscribe`

```php
$user->subscribe($targets)
$user->unsubscribe($targets)
$user->toggleSubscribe($targets)
$user->hasSubscribed($target)
$user->subscriptions()->get() // default object: App\User:class
$user->subscriptions(App\Post::class)->get()
```

#### `Liliom\Acquaintances\Traits\CanBeSubscribed`

```php
$object->subscribers() // or $object->subscribers 
$object->isSubscribedBy($user)
$object->subscribersCount() // or as attribute $object->subscribers_count
$object->subscribersCountReadable() // return readable number with precision, i.e: 5.2K
```

### Vote

#### `\Liliom\Acquaintances\Traits\CanVote`

```php
$user->vote($target) // Vote with 'upvote' for default
$user->upvote($target)
$user->downvote($target)
$user->cancelVote($target)
$user->hasUpvoted($target)
$user->hasDownvoted($target)
$user->votes(App\Post::class)->get()
$user->upvotes(App\Post::class)->get()
$user->downvotes(App\Post::class)->get()
```

#### `\Liliom\Acquaintances\Traits\CanBeVoted`

```php
$object->voters()->get()
$object->isVotedBy($user)
$object->votersCount() // or as attribute $object->voters_count
$object->votersCountReadable() // return readable number with precision, i.e: 5.2K

$object->upvoters()->get()
$object->isUpvotedBy($user)
$object->upvotersCount() // or as attribute $object->upvoters_count
$object->upvotersCountReadable() // return readable number with precision, i.e: 5.2K

$object->downvoters()->get()
$object->isDownvotedBy($user)
$object->downvotersCount() // or as attribute $object->downvoters_count
$object->downvotersCountReadable() // return readable number with precision, i.e: 5.2K
```

### Parameters

All of the above mentioned methods of creating relationships, such as 'follow', 'like', 'unfollow', 'unlike', their syntax is as follows:

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
$followers = $user->followers
$followers = $user->followers()->where('id', '>', 10)->get()
$followers = $user->followers()->orderByDesc('id')->get()
```

You may use the others in the same way.

### Working with model

```php
use Liliom\Acquaintances\Models\InteractionRelation;

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

|Event name                     |Fired                                          |
|---------------------------    |-----------------------------------------------|
|acq.friendships.sent           |When a friend request is sent                  |
|acq.friendships.accepted       |When a friend request is accepted              |
|acq.friendships.denied         |When a friend request is denied                |
|acq.friendships.blocked        |When a friend is blocked                       |
|acq.friendships.unblocked      |When a friend is unblocked                     |
|acq.friendships.cancelled      |When a friendship is cancelled                 |
|acq.ratings.rate               |When a an item or items get Rated              |
|acq.ratings.unrate             |When a an item or items get unRated            |
|acq.vote.up                    |When a an item or items get upvoted            |
|acq.vote.down                  |When a an item or items get downvoted          |
|acq.vote.cancel                |When a an item or items get vote cancellation  |
|acq.likes.like                 |When a an item or items get liked              |
|acq.likes.unlike               |When a an item or items get unliked            |
|acq.followships.follow         |When a an item or items get followed           |
|acq.followships.unfollow       |When a an item or items get unfollowed         |
|acq.favorites.favorite         |When a an item or items get favored            |
|acq.favorites.unfavorite       |When a an item or items get unfavored          |
|acq.subscriptions.subscribe    |When a an item or items get subscribed         |                 
|acq.subscriptions.unsubscribe  |When a an item or items get unsubscribed       |                 


### Contributing
See the [CONTRIBUTING](CONTRIBUTING.md) guide.

### Change Log
See the [log](CHANGELOG.md) file.

# Interactions

Traits to add to the acting user model:

```php
use Multicaret\Acquaintances\Traits\CanFollow;
use Multicaret\Acquaintances\Traits\CanLike;
use Multicaret\Acquaintances\Traits\CanFavorite;
use Multicaret\Acquaintances\Traits\CanSubscribe;
use Multicaret\Acquaintances\Traits\CanVote;
use Multicaret\Acquaintances\Traits\CanReport;

class User extends Model {
  use CanFollow, CanLike, CanFavorite, CanSubscribe, CanVote, CanReport;
}
```

Traits to add to target models (e.g., Post):

```php
use Multicaret\Acquaintances\Traits\CanBeFollowed;
use Multicaret\Acquaintances\Traits\CanBeLiked;
use Multicaret\Acquaintances\Traits\CanBeFavorited;
use Multicaret\Acquaintances\Traits\CanBeVoted;
use Multicaret\Acquaintances\Traits\CanBeRated;
use Multicaret\Acquaintances\Traits\CanBeReported;
use Multicaret\Acquaintances\Traits\CanBeViewed;

class Post extends Model {
  use CanBeFollowed, CanBeLiked, CanBeFavorited, CanBeVoted, CanBeRated, CanBeReported, CanBeViewed;
}
```

## Follow

```php
$user->follow($targets); $user->unfollow($targets); $user->toggleFollow($targets);
$user->followings(); $object->followers();
```

## Like

```php
$user->like($targets); $user->unlike($targets); $user->toggleLike($targets);
$user->likes(); $object->likers();
```

## Favorite

```php
$user->favorite($targets); $user->unfavorite($targets); $user->toggleFavorite($targets);
$user->favorites(); $object->favoriters();
```

## Report

```php
$user->report($targets); $user->unreport($targets); $user->toggleReport($targets);
$user->reports(); $object->reporters();
```

## Subscribe

```php
$user->subscribe($targets); $user->unsubscribe($targets); $user->toggleSubscribe($targets);
$user->subscriptions(); $object->subscribers();
```

## Vote

```php
$user->vote($target); $user->upvote($target); $user->downvote($target); $user->cancelVote($target);
$object->upvoters(); $object->downvoters();
```

## View

```php
$user->view($targets); $user->unview($targets); $user->toggleView($targets);
$object->viewers();
```

## Parameters

All creators accept IDs, model instances, or collections/arrays. Most methods accept:

```php
($targets, $class = __CLASS__)
```

## Query helpers

```php
$user->followers()->paginate(10);
$user->followers()->orderByDesc('id')->get();
```

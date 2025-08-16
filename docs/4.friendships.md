# Friendships

Add the trait to your user model:

```php
use Multicaret\Acquaintances\Traits\Friendable;

class User extends Model { use Friendable; }
```

Common operations:

```php
// Requests
$user->befriend($recipient);
$user->acceptFriendRequest($sender);
$user->denyFriendRequest($sender);
$user->unfriend($friend);

// Block
$user->blockFriend($friend);
$user->unblockFriend($friend);

// Checks
$user->isFriendWith($friend);
$user->hasFriendRequestFrom($sender);
$user->hasSentFriendRequestTo($recipient);
$user->hasBlocked($friend);
$user->isBlockedBy($friend);

// Queries
$user->getFriendship($friend);
$user->getAllFriendships($group = '', $perPage = 20, $fields = ['*']);
$user->getPendingFriendships($group = '', $perPage = 20, $fields = ['*']);
$user->getAcceptedFriendships($group = '', $perPage = 20, $fields = ['*']);
$user->getDeniedFriendships($perPage = 20, $fields = ['*']);
$user->getBlockedFriendships($perPage = 20, $fields = ['*']);
$user->getBlockedFriendshipsByCurrentUser($perPage = 20, $fields = ['*']);
$user->getBlockedFriendshipsByOtherUsers($perPage = 20, $fields = ['*']);
$user->getFriendRequests();

// Friends collections
$user->getFriends($perPage = 0, $group = '', $fields = ['*'], $cursor = false);
$user->getFriendsOfFriends($perPage = 0, $fields = ['*']);
$user->getMutualFriends($otherUser, $perPage = 0, $fields = ['*']);

// Counts
$user->getFriendsCount($group = '');
$user->getPendingsCount();
$user->getMutualFriendsCount($otherUser);
```

## Groups

Configure in config/acquaintances.php under friendships_groups. Example:

```php
'friendships_groups' => [
  'acquaintances' => 0,
  'close_friends' => 1,
  'family' => 2,
],
```

APIs:

```php
$user->groupFriend($friend, 'family');
$user->ungroupFriend($friend, 'family');
$user->ungroupFriend($friend); // all groups
$user->getFriendsCount('family');
```

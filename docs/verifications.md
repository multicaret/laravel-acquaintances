# Verifications

Add the trait to your user model:

```php
use Multicaret\Acquaintances\Traits\Verifiable;

class User extends Model { use Verifiable; }
```

Common operations:

```php
// Requests
$user->verify($recipient, $message = null);
$user->acceptVerificationRequest($sender);
$user->denyVerificationRequest($sender);
$user->unverify($recipient);

// Block
$user->blockVerification($recipient);
$user->unblockVerification($recipient);

// Checks
$user->isVerifiedWith($recipient);
$user->hasVerificationRequestFrom($sender);
$user->hasSentVerificationRequestTo($recipient);
$user->canVerify($recipient);

// Queries
$user->getVerification($recipient);
$user->getAllVerifications($group = '', $perPage = 20, $fields = ['*'], $type = 'all');
$user->getPendingVerifications($group = '', $perPage = 20, $fields = ['*'], $type = 'all');
$user->getAcceptedVerifications($group = '', $perPage = 20, $fields = ['*'], $type = 'all');
$user->getDeniedVerifications($perPage = 20, $fields = ['*']);
$user->getBlockedVerifications($perPage = 20, $fields = ['*']);
$user->getBlockedVerificationsByCurrentUser($perPage = 20, $fields = ['*']);
$user->getBlockedVerificationsByOtherUsers($perPage = 20, $fields = ['*']);
$user->getVerificationRequests();

// Collections
$user->getVerifiers($perPage = 0, $group = '', $fields = ['*'], $cursor = false);
$user->getVerifiersOfVerifiers($perPage = 0, $fields = ['*']);
$user->getMutualVerifiers($otherUser, $perPage = 0, $fields = ['*']);

// Counts
$user->getVerifiersCount($group = '', $type = 'all');
$user->getPendingVerificationsCount();
$user->getMutualVerifiersCount($otherUser);
```

## Groups

Configure in config/acquaintances.php under verifications_groups. Example:

```php
'verifications_groups' => [
  'text' => 0,
  'phone' => 1,
  'cam' => 2,
  'personally' => 3,
  'intimately' => 4,
],
```

APIs:

```php
$user->groupVerification($verifier, 'text');
$user->ungroupVerification($verifier, 'text');
$user->ungroupVerification($verifier); // all groups
$user->getVerifiersCount('text');
```

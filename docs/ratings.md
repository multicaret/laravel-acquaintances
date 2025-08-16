# Ratings

Add traits:

```php
use Multicaret\Acquaintances\Traits\CanRate;    // on user/actor
use Multicaret\Acquaintances\Traits\CanBeRated; // on target model
```

Basics:

```php
$user->rate($target);       // uses default type from config('acquaintances.rating.defaults.type')
$user->unrate($target);
$user->toggleRate($target);
$user->hasRated($target);

$object->raters();
$object->isRatedBy($user);

$object->averageRating();
$object->sumRating();
$object->ratingPercent($max = 5);
```

Per-type ratings:

```php
$user->setRateType('quality')->rate($target, 4);
$user->setRateType('delivery-time')->rate($target, 3);
$user->setRateType('communication')->rate($target, 5);

$object->averageRatingAllTypes();
$object->sumRatingAllTypes();
$object->userAverageRatingAllTypes();
$object->userSumRatingAllTypes();
```

Configure rating types in config/acquaintances.php under rating.types.

<?php

namespace Multicaret\Acquaintances;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Multicaret\Acquaintances\Models\FriendFriendshipGroups;
use Multicaret\Acquaintances\Models\Friendship;
use Multicaret\Acquaintances\Models\InteractionRelation;
use Multicaret\Acquaintances\Models\Verification;
use Multicaret\Acquaintances\Models\VerificationGroups;
use Multicaret\Acquaintances\Traits\HasAcquaintanceCounters;
use stdClass;

/**
 * Class Interaction.
 */
class Interaction
{
    const RELATION_LIKE = 'like';

    const RELATION_FOLLOW = 'follow';

    const RELATION_SUBSCRIBE = 'subscribe';

    const RELATION_FAVORITE = 'favorite';

    const RELATION_UPVOTE = 'upvote';

    const RELATION_DOWNVOTE = 'downvote';

    const RELATION_RATE = 'rating';

    const RELATION_VIEW = 'view';

    const RELATION_REPORT = 'report';

    public static $pivotColumns = [
        'id',
        'subject_type',
        'relation',
        'relation_value',
        'relation_type',
        'created_at',
    ];

    /**
     * In-memory cache for relation existence checks within the current request.
     *
     * @var array<string, bool>
     */
    protected static array $requestCache = [];

    /**
     * @var array
     */
    protected static $relationMap = [
        'followings' => 'follow',
        'followers' => 'follow',
        'likes' => 'like',
        'likers' => 'like',
        'favoriters' => 'favorite',
        'favorites' => 'favorite',
        'subscriptions' => 'subscribe',
        'subscribers' => 'subscribe',
        'upvotes' => 'upvote',
        'upvoters' => 'upvote',
        'downvotes' => 'downvote',
        'downvoters' => 'downvote',
        'ratingsTo' => 'rating',
        'raters' => 'rating',
        'views' => 'view',
        'viewers' => 'view',
        'reporters' => 'report',
        'reports' => 'report',
    ];

    /**
     * @param  string  $relation
     * @param  array|string|Model  $target
     * @param  string|null  $class
     * @return bool
     */
    public static function isRelationExists(Model $model, $relation, $target, $class = null, array $updates = [])
    {
        $target = self::formatTargets($target, $class ?: self::getUserModelName(), $updates);
        $cacheKey = self::buildCacheKey($model, $relation, $target->classname, head($target->ids));

        if (isset(self::$requestCache[$cacheKey])) {
            return self::$requestCache[$cacheKey];
        }

        $resolver = function () use ($model, $relation, $target, $class) {
            $userIdFkColumnName = config('acquaintances.tables.interactions_user_id_fk_column_name', 'user_id');

            return $model->{$relation}($target->classname)
                ->where($class ? config('acquaintances.tables.interactions',
                    'interactions').'.subject_id' : config('acquaintances.tables.interactions',
                        'interactions').'.'.$userIdFkColumnName, head($target->ids))
                ->exists();
        };

        $result = self::resolveFromCache($cacheKey, $resolver);
        self::$requestCache[$cacheKey] = $result;

        return $result;
    }

    /**
     * @param  string  $relation
     * @param  array|string|Model  $targets
     * @param  string  $class
     * @return array
     *
     * @throws \Exception
     */
    public static function attachRelations(Model $model, $relation, $targets, $class, array $updates = [])
    {
        $targets = self::attachPivotsFromRelation($model->{$relation}(), $targets, $class, $updates);
        $result = $model->{$relation}($targets->classname)->sync($targets->targets, false);

        self::invalidateRelationCache($model, $relation, $targets->classname, $targets->ids);
        self::updateCounters($model, $relation, $targets, 'increment');

        return $result;
    }

    /**
     * @param  string  $relation
     * @param  array|string|Model  $targets
     * @param  string  $class
     * @return array
     */
    public static function detachRelations(Model $model, $relation, $targets, $class, array $updates = [])
    {
        $targets = self::formatTargets($targets, $class, $updates);
        $result = $model->{$relation}($targets->classname)->detach($targets->ids);

        self::invalidateRelationCache($model, $relation, $targets->classname, $targets->ids);
        self::updateCounters($model, $relation, $targets, 'decrement');

        return $result;
    }

    /**
     * @param  string  $relation
     * @param  array|string|Model  $targets
     * @param  string  $class
     * @return array
     *
     * @throws \Exception
     */
    public static function toggleRelations(Model $model, $relation, $targets, $class, array $updates = [])
    {
        $targets = self::attachPivotsFromRelation($model->{$relation}(), $targets, $class, $updates);
        $result = $model->{$relation}($targets->classname)->toggle($targets->targets);

        self::invalidateRelationCache($model, $relation, $targets->classname, $targets->ids);

        return $result;
    }

    /**
     * @param  array|string|Model  $targets
     * @param  string  $class
     * @return stdClass
     *
     * @throws \Exception
     */
    public static function attachPivotsFromRelation(MorphToMany $morph, $targets, $class, array $updates = [])
    {
        $essentialUpdates = array_merge($updates, [
            'relation' => self::getRelationTypeFromRelation($morph),
            //            'created_at' => Carbon::now(),
        ]);

        return self::formatTargets($targets, $class, $essentialUpdates);
    }

    /**
     * @param  array|string|Model  $targets
     * @param  string  $classname
     * @return stdClass
     */
    public static function formatTargets($targets, $classname, array $update = [])
    {
        $result = new stdClass;
        $result->classname = $classname;

        if (! is_array($targets)) {
            $targets = [$targets];
        }

        $result->ids = array_map(function ($target) use ($result) {
            if ($target instanceof Model) {
                $result->classname = get_class($target);

                return $target->getKey();
            }

            return intval($target);
        }, $targets);

        $result->targets = empty($update) ? $result->ids : array_combine(
            $result->ids,
            array_pad([], count($result->ids), $update)
        );

        return $result;
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected static function getRelationTypeFromRelation(MorphToMany $relation)
    {
        if (! \array_key_exists($relation->getRelationName(), self::$relationMap)) {
            throw new \Exception('Invalid relation definition.');
        }

        return self::$relationMap[$relation->getRelationName()];
    }

    public static function numberToReadable($number, $precision = 1, $divisors = null)
    {
        $shorthand = '';
        $divisor = pow(1000, 0);
        if (! isset($divisors)) {
            $divisors = [
                $divisor => $shorthand, // 1000^0 == 1
                pow(1000, 1) => 'K', // Thousand
                pow(1000, 2) => 'M', // Million
                pow(1000, 3) => 'B', // Billion
                pow(1000, 4) => 'T', // Trillion
                pow(1000, 5) => 'Qa', // Quadrillion
                pow(1000, 6) => 'Qi', // Quintillion
            ];
        }
        foreach ($divisors as $divisor => $shorthand) {
            if (abs($number) < ($divisor * 1000)) {
                break;
            }
        }

        $precision = empty($shorthand) ? 0 : $precision;

        return number_format($number / $divisor, $precision).$shorthand;
    }

    public static function getFullModelName($modelClassName)
    {
        if (class_exists($modelClassName)) {
            return Str::studly($modelClassName);
        }

        $namespace = config('acquaintances.model_namespace', 'App');

        return empty($namespace)
            ? Str::studly($modelClassName)
            : $namespace.'\\'.Str::studly($modelClassName);
    }

    public static function getUserModelName()
    {
        $className = config('acquaintances.user_model_class_name');

        if (! empty($className)) {
            return $className;
        }

        return Interaction::getFullModelName(
            config('acquaintances.models.user', 'User')
        );
    }

    public static function getInteractionRelationModelName()
    {
        return Interaction::getFullModelName(
            config(
                'acquaintances.models.interaction_relation',
                InteractionRelation::class
            )
        );
    }

    public static function getFriendshipModelName()
    {
        return Interaction::getFullModelName(
            config(
                'acquaintances.models.friendship',
                Friendship::class
            )
        );
    }

    public static function getFriendshipGroupsModelName()
    {
        return Interaction::getFullModelName(
            config(
                'acquaintances.models.friendship_groups',
                FriendFriendshipGroups::class
            )
        );
    }

    public static function getVerificationModelName()
    {
        return Interaction::getFullModelName(
            config(
                'acquaintances.models.verification',
                Verification::class
            )
        );
    }

    public static function getVerificationGroupsModelName()
    {
        return Interaction::getFullModelName(
            config(
                'acquaintances.models.verification_groups',
                VerificationGroups::class
            )
        );
    }

    /**
     * Build a unique cache key for a relation existence check.
     */
    protected static function buildCacheKey(Model $model, string $relation, string $classname, mixed $targetId): string
    {
        return sprintf(
            'acq:%s:%s:%s:%s:%s',
            $model->getMorphClass(),
            $model->getKey(),
            $relation,
            $classname,
            $targetId
        );
    }

    /**
     * Resolve a value from the configurable cache store, or directly from the resolver.
     */
    protected static function resolveFromCache(string $cacheKey, callable $resolver): bool
    {
        if (! config('acquaintances.cache.enabled', false)) {
            return $resolver();
        }

        $store = config('acquaintances.cache.store');
        $ttl = config('acquaintances.cache.ttl', 3600);

        return Cache::store($store)->remember($cacheKey, $ttl, $resolver);
    }

    /**
     * Invalidate both in-memory and persistent cache for the given relation targets.
     *
     * @param  array<int|string>  $targetIds
     */
    protected static function invalidateRelationCache(Model $model, string $relation, string $classname, array $targetIds): void
    {
        foreach ($targetIds as $targetId) {
            $cacheKey = self::buildCacheKey($model, $relation, $classname, $targetId);
            unset(self::$requestCache[$cacheKey]);

            if (config('acquaintances.cache.enabled', false)) {
                Cache::store(config('acquaintances.cache.store'))->forget($cacheKey);
            }
        }
    }

    /**
     * Flush the in-memory request cache (useful for testing).
     */
    public static function flushRequestCache(): void
    {
        self::$requestCache = [];
    }

    /**
     * Update counter cache columns on target models if they use HasAcquaintanceCounters.
     */
    protected static function updateCounters(Model $model, string $relation, stdClass $targets, string $direction): void
    {
        $relationType = self::$relationMap[$relation] ?? null;
        if (! $relationType) {
            return;
        }

        foreach ($targets->ids as $targetId) {
            $targetModel = (new $targets->classname)->find($targetId);
            if ($targetModel && in_array(HasAcquaintanceCounters::class, class_uses_recursive($targetModel))) {
                if ($direction === 'increment') {
                    $targetModel->incrementAcquaintanceCounter($relationType);
                } else {
                    $targetModel->decrementAcquaintanceCounter($relationType);
                }
            }
        }
    }
}

<?php

namespace Multicaret\Acquaintances;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
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

    public static $pivotColumns = [
        'id',
        'subject_type',
        'actor_type',
        'relation',
        'relation_value',
        'relation_type',
        'created_at'
    ];

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
    ];

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $relation
     * @param  array|string|\Illuminate\Database\Eloquent\Model  $target
     * @param  string  $class
     *
     * @param  array  $updates
     *
     * @return bool
     */
    public static function isRelationExists(Model $model, $relation, $target, $class = null, array $updates = [])
    {
        $target = self::formatTargets($target, $class ?: config('auth.providers.users.model'), $updates);

        return $model->{$relation}($target->classname)
                     ->where($class ? 'subject_id' : 'user_id', head($target->ids))
                     ->exists();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $relation
     * @param  array|string|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @param  array  $updates
     *
     * @return array
     * @throws \Exception
     */
    public static function attachRelations(Model $model, $relation, $targets, $class, array $updates = [])
    {
        $targets = self::attachPivotsFromRelation($model->{$relation}(), $targets, $class, $updates);

        return $model->{$relation}($targets->classname)->sync($targets->targets, false);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $relation
     * @param  array|string|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @param  array  $updates
     *
     * @return array
     */
    public static function detachRelations(Model $model, $relation, $targets, $class, array $updates = [])
    {
        $targets = self::formatTargets($targets, $class, $updates);

        return $model->{$relation}($targets->classname)->detach($targets->ids);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $relation
     * @param  array|string|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @param  array  $updates
     *
     * @return array
     * @throws \Exception
     */
    public static function toggleRelations(Model $model, $relation, $targets, $class, array $updates = [])
    {
        $targets = self::attachPivotsFromRelation($model->{$relation}(), $targets, $class, $updates);

        return $model->{$relation}($targets->classname)->toggle($targets->targets);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Relations\MorphToMany  $morph
     * @param  array|string|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @param  array  $updates
     *
     * @return \stdClass
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
     * @param  array|string|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $classname
     * @param  array  $update
     *
     * @return \stdClass
     */
    public static function formatTargets($targets, $classname, array $update = [])
    {
        $result = new stdClass();
        $result->classname = $classname;

        if ( ! is_array($targets)) {
            $targets = [$targets];
        }

        $result->ids = array_map(function ($target) use ($result) {
            if ($target instanceof Model) {
                $result->classname = get_class($target);
                return $target->getKey();
            }

            return intval($target);
        }, $targets);

        $result->targets = empty($update) ? $result->ids : array_combine($result->ids,
            array_pad([], count($result->ids), $update));

        return $result;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Relations\MorphToMany  $relation
     *
     * @return array
     * @throws \Exception
     *
     */
    protected static function getRelationTypeFromRelation(MorphToMany $relation)
    {
        if ( ! \array_key_exists($relation->getRelationName(), self::$relationMap)) {
            throw new \Exception('Invalid relation definition.');
        }

        return self::$relationMap[$relation->getRelationName()];
    }

    static public function numberToReadable($number, $precision = 1, $divisors = null)
    {
        $shorthand = '';
        $divisor = pow(1000, 0);
        if ( ! isset($divisors)) {
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
            : $namespace . '\\' . Str::studly($modelClassName);
    }

    public static function getUserModelName()
    {
        return Interaction::getFullModelName(
            config(
                'acquaintances.user_model_class_name',
                config('acquaintances.models.user', 'User')
            )
        );
    }

    public static function getInteractionRelationModelName()
    {
        return Interaction::getFullModelName(
            config(
                'acquaintances.models.interaction_relation',
                \Multicaret\Acquaintances\Models\InteractionRelation::class
            )
        );
    }

    public static function getFriendshipModelName()
    {
        return Interaction::getFullModelName(
            config(
                'acquaintances.models.friendship',
                \Multicaret\Acquaintances\Models\Friendship::class
            )
        );
    }

    public static function getFriendshipGroupsModelName()
    {
        return Interaction::getFullModelName(
            config(
                'acquaintances.models.friendship_groups',
                \Multicaret\Acquaintances\Models\FriendshipGroups::class
            )
        );
    }
}

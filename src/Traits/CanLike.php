<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Event;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanLike.
 */
trait CanLike
{
    /**
     * Like an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     *
     * @throws \Exception
     */
    public function like($targets, $class = __CLASS__): array
    {
        Event::dispatch('acq.likes.like', [$this, $targets]);

        return Interaction::attachRelations($this, 'likes', $targets, $class);
    }

    /**
     * Unlike an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     */
    public function unlike($targets, $class = __CLASS__): array
    {
        Event::dispatch('acq.likes.unlike', [$this, $targets]);

        return Interaction::detachRelations($this, 'likes', $targets, $class);
    }

    /**
     * Toggle like an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     *
     * @throws \Exception
     */
    public function toggleLike($targets, $class = __CLASS__): array
    {
        return Interaction::toggleRelations($this, 'likes', $targets, $class);
    }

    /**
     * Check if a model is liked by a given model.
     *
     * @param  int|array|Model  $target
     * @param  string  $class
     */
    public function hasLiked($target, $class = __CLASS__): bool
    {
        return Interaction::isRelationExists($this, 'likes', $target, $class);
    }

    /**
     * Return item likes.
     *
     * @param  string  $class
     */
    public function likes($class = __CLASS__): MorphToMany
    {
        return $this->morphedByMany($class, 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_LIKE)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }
}

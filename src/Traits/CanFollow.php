<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Event;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanFollow.
 */
trait CanFollow
{
    /**
     * Follow an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     *
     * @throws \Exception
     */
    public function follow($targets, $class = __CLASS__): array
    {
        Event::dispatch('acq.followships.follow', [$this, $targets]);

        return Interaction::attachRelations($this, 'followings', $targets, $class);
    }

    /**
     * Unfollow an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     */
    public function unfollow($targets, $class = __CLASS__): array
    {
        Event::dispatch('acq.followships.unfollow', [$this, $targets]);

        return Interaction::detachRelations($this, 'followings', $targets, $class);
    }

    /**
     * Toggle follow an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     *
     * @throws \Exception
     */
    public function toggleFollow($targets, $class = __CLASS__): array
    {
        return Interaction::toggleRelations($this, 'followings', $targets, $class);
    }

    /**
     * Check if a model is following given model.
     *
     * @param  int|array|Model  $target
     * @param  string  $class
     */
    public function isFollowing($target, $class = __CLASS__): bool
    {
        return Interaction::isRelationExists($this, 'followings', $target, $class);
    }

    /**
     * Return item followings.
     *
     * @param  string  $class
     */
    public function followings($class = __CLASS__): MorphToMany
    {
        return $this->morphedByMany(
            $class,
            'subject',
            config('acquaintances.tables.interactions')
        )
            ->wherePivot('relation', '=', Interaction::RELATION_FOLLOW)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }

    /**
     * Return following count.
     */
    public function followingCount(): int
    {
        return $this->followings()->count();
    }

    /**
     * Get followingCount attribute.
     */
    public function getFollowingCountAttribute(): int
    {
        return $this->followingCount();
    }

    /**
     * Return followingCount in a readable format.
     */
    public function followingCountReadable(int $precision = 1, ?string $divisors = null): string
    {
        return Interaction::numberToReadable($this->followingCount(), $precision, $divisors);
    }
}

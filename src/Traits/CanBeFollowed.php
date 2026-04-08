<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanBeFollowed.
 */
trait CanBeFollowed
{
    /**
     * Check if a model is followed by given model.
     *
     * @param  int|array|Model  $user
     */
    public function isFollowedBy($user): bool
    {
        return Interaction::isRelationExists($this, 'followers', $user);
    }

    /**
     * Return followers.
     */
    public function followers(): MorphToMany
    {
        return $this->morphToMany(Interaction::getUserModelName(), 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_FOLLOW)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }

    public function followersCount(): int
    {
        return $this->followers()->count();
    }

    public function getFollowersCountAttribute(): int
    {
        return $this->followersCount();
    }

    public function followersCountReadable($precision = 1, $divisors = null): string
    {
        return Interaction::numberToReadable($this->followersCount(), $precision, $divisors);
    }
}

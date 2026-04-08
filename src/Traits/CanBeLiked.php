<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanBeLiked.
 */
trait CanBeLiked
{
    /**
     * Check if a model is liked by given model.
     *
     * @param  int|array|Model  $user
     */
    public function isLikedBy($user): bool
    {
        return Interaction::isRelationExists($this, 'likers', $user);
    }

    /**
     * Return likers.
     */
    public function likers(): MorphToMany
    {
        return $this->morphToMany(Interaction::getUserModelName(), 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_LIKE)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }

    /**
     * Alias of likers.
     */
    public function fans(): MorphToMany
    {
        return $this->likers();
    }

    public function likersCount(): int
    {
        return $this->likers()->count();
    }

    public function getLikersCountAttribute(): int
    {
        return $this->likersCount();
    }

    public function likersCountFormatted($precision = 1, $divisors = null): string
    {
        return Interaction::numberToReadable($this->likersCount(), $precision, $divisors);
    }

    /**
     * Alias of likersCountFormatted.
     *
     * @param  int  $precision
     * @param  null  $divisors
     */
    public function likersCountReadable($precision = 1, $divisors = null): string
    {
        return $this->likersCountFormatted($precision, $divisors);
    }

    public function getLikersCountReadableAttribute(): int
    {
        return $this->likersCount();
    }
}

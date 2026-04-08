<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanBeFavorited.
 */
trait CanBeFavorited
{
    /**
     * Check if a model is favorited by given model.
     *
     * @param  int|array|Model  $user
     */
    public function isFavoritedBy($user): bool
    {
        return Interaction::isRelationExists($this, 'favoriters', $user);
    }

    /**
     * Return favoriters.
     */
    public function favoriters(): MorphToMany
    {
        return $this->morphToMany(Interaction::getUserModelName(), 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_FAVORITE)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }

    public function favoritersCount(): int
    {
        return $this->favoriters()->count();
    }

    public function getFavoritersCountAttribute(): int
    {
        return $this->favoritersCount();
    }

    public function favoritersCountReadable($precision = 1, $divisors = null): string
    {
        return Interaction::numberToReadable($this->favoritersCount(), $precision, $divisors);
    }
}

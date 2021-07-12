<?php


namespace Multicaret\Acquaintances\Traits;

use Multicaret\Acquaintances\Interaction;


/**
 * Trait CanBeFavorited.
 */
trait CanBeFavorited
{
    /**
     * Check if a model is favorited by given model.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $user
     *
     * @return bool
     */
    public function isFavoritedBy($user)
    {
        return Interaction::isRelationExists($this, 'favoriters', $user);
    }

    /**
     * Return favoriters.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoriters()
    {
        return $this->morphToMany(Interaction::getUserModelName(), 'subject',
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_FAVORITE)
                    ->withPivot(...Interaction::$pivotColumns)
                    ->using(Interaction::getInteractionRelationModelName())
                    ->withTimestamps();
    }

    public function favoritersCount()
    {
        return $this->favoriters()->count();
    }

    public function getFavoritersCountAttribute()
    {
        return $this->favoritersCount();
    }

    public function favoritersCountReadable($precision = 1, $divisors = null)
    {
        return Interaction::numberToReadable($this->favoritersCount(), $precision, $divisors);
    }
}

<?php


namespace Multicaret\Acquaintances\Traits;

use Multicaret\Acquaintances\Interaction;


/**
 * Trait CanBeFavorited.
 */
trait CanBeFavorited
{
    /**
     * Check if user is favorited by given user.
     *
     * @param int $user
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
        return $this->morphToMany(config('auth.providers.users.model'), 'subject',
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_FAVORITE)
                    ->withPivot(...Interaction::$pivotColumns);
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

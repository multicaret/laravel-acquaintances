<?php


namespace Multicaret\Acquaintances\Traits;

use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanBeLiked.
 */
trait CanBeLiked
{
    /**
     * Check if user is isLikedBy by given user.
     *
     * @param int $user
     *
     * @return bool
     */
    public function isLikedBy($user)
    {
        return Interaction::isRelationExists($this, 'likers', $user);
    }

    /**
     * Return followers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likers()
    {
        return $this->morphToMany(config('auth.providers.users.model'), 'subject',
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_LIKE)
                    ->withPivot(...Interaction::$pivotColumns);
    }

    /**
     * Alias of likers.
     *
     * @return mixed
     */
    public function fans()
    {
        return $this->likers();
    }

    public function likersCount()
    {
        return $this->likers()->count();
    }

    public function getLikersCountAttribute()
    {
        return $this->likersCount();
    }

    public function likersCountFormatted($precision = 1, $divisors = null)
    {
        return Interaction::numberToReadable($this->likersCount(), $precision, $divisors);
    }

    /**
     * Alias of likersCountFormatted.
     *
     * @param int  $precision
     * @param null $divisors
     *
     * @return string
     */
    public function likersCountReadable($precision = 1, $divisors = null)
    {
        return $this->likersCountFormatted($precision, $divisors);
    }

    public function getLikersCountReadableAttribute()
    {
        return $this->likersCount();
    }
}

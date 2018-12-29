<?php


namespace Liliom\Acquaintances\Traits;

use Liliom\Acquaintances\Interaction;

/**
 * Trait CanBeSubscribed.
 */
trait CanBeSubscribed
{
    /**
     * Check if user is subscribed by given user.
     *
     * @param int $user
     *
     * @return bool
     */
    public function isSubscribedBy($user)
    {
        return Interaction::isRelationExists($this, 'subscribers', $user);
    }

    /**
     * Return subscribers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subscribers()
    {
        return $this->morphToMany(config('auth.providers.users.model'), 'subject',
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_SUBSCRIBE)
                    ->withPivot(...Interaction::$pivotColumns);
    }

    public function subscribersCount()
    {
        return $this->subscribers()->count();
    }

    public function getSubscribersCountAttribute()
    {
        return $this->subscribersCount();
    }

    public function subscribersCountReadable($precision = 1, $divisors = null)
    {
        return Interaction::numberToReadable($this->subscribersCount(), $precision, $divisors);
    }
}

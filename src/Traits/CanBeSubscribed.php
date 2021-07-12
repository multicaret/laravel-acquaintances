<?php


namespace Multicaret\Acquaintances\Traits;

use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanBeSubscribed.
 */
trait CanBeSubscribed
{
    /**
     * Check if a model is subscribed by given model.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $user
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
        return $this->morphToMany(Interaction::getUserModelName(), 'subject',
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_SUBSCRIBE)
                    ->withPivot(...Interaction::$pivotColumns)
                    ->using(Interaction::getInteractionRelationModelName())
                    ->withTimestamps();
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

<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanBeSubscribed.
 */
trait CanBeSubscribed
{
    /**
     * Check if a model is subscribed by given model.
     *
     * @param  int|array|Model  $user
     */
    public function isSubscribedBy($user): bool
    {
        return Interaction::isRelationExists($this, 'subscribers', $user);
    }

    /**
     * Return subscribers.
     */
    public function subscribers(): MorphToMany
    {
        return $this->morphToMany(Interaction::getUserModelName(), 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_SUBSCRIBE)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }

    public function subscribersCount(): int
    {
        return $this->subscribers()->count();
    }

    public function getSubscribersCountAttribute(): int
    {
        return $this->subscribersCount();
    }

    public function subscribersCountReadable($precision = 1, $divisors = null): string
    {
        return Interaction::numberToReadable($this->subscribersCount(), $precision, $divisors);
    }
}

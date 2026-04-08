<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanBeViewed.
 */
trait CanBeViewed
{
    /**
     * Check if a model has been viewed by given model.
     *
     * @param  int|array|Model  $user
     */
    public function isViewedBy($user): bool
    {
        return Interaction::isRelationExists($this, 'viewers', $user);
    }

    /**
     * Return viewers.
     */
    public function viewers(): MorphToMany
    {
        return $this->morphToMany(Interaction::getUserModelName(), 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_VIEW)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }

    public function viewersCount(): int
    {
        return $this->viewers()->count();
    }

    public function getViewersCountAttribute(): int
    {
        return $this->viewersCount();
    }

    public function viewersCountFormatted($precision = 1, $divisors = null): string
    {
        return Interaction::numberToReadable($this->viewersCount(), $precision, $divisors);
    }

    /**
     * Alias of viewersCountFormatted.
     *
     * @param  int  $precision
     * @param  null  $divisors
     */
    public function viewersCountReadable($precision = 1, $divisors = null): string
    {
        return $this->viewersCountFormatted($precision, $divisors);
    }

    public function getViewersCountReadableAttribute(): int
    {
        return $this->viewersCount();
    }
}

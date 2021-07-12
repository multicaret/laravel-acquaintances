<?php


namespace Multicaret\Acquaintances\Traits;

use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanBeViewed.
 */
trait CanBeViewed
{
    /**
     * Check if a model has been viewed by given model.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $user
     *
     * @return bool
     */
    public function isViewedBy($user)
    {
        return Interaction::isRelationExists($this, 'viewers', $user);
    }

    /**
     * Return viewers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function viewers()
    {
        return $this->morphToMany(Interaction::getUserModelName(), 'subject',
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_VIEW)
                    ->withPivot(...Interaction::$pivotColumns)
                    ->using(Interaction::getInteractionRelationModelName())
                    ->withTimestamps();
    }

    public function viewersCount()
    {
        return $this->viewers()->count();
    }

    public function getViewersCountAttribute()
    {
        return $this->viewersCount();
    }

    public function viewersCountFormatted($precision = 1, $divisors = null)
    {
        return Interaction::numberToReadable($this->viewersCount(), $precision, $divisors);
    }

    /**
     * Alias of viewersCountFormatted.
     *
     * @param  int  $precision
     * @param  null  $divisors
     *
     * @return string
     */
    public function viewersCountReadable($precision = 1, $divisors = null)
    {
        return $this->viewersCountFormatted($precision, $divisors);
    }

    public function getViewersCountReadableAttribute()
    {
        return $this->viewersCount();
    }
}

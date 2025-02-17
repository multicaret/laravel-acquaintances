<?php


namespace Multicaret\Acquaintances\Traits;

use Multicaret\Acquaintances\Interaction;


/**
 * Trait CanBeReported.
 */
trait CanBeReported
{
    /**
     * Check if a model is reported by given model.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $user
     *
     * @return bool
     */
    public function isReportedBy($user)
    {
        return Interaction::isRelationExists($this, 'reporters', $user);
    }

    /**
     * Return reporters.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function reporters()
    {
        return $this->morphToMany(
            Interaction::getUserModelName(),
            'subject',
            config('acquaintances.tables.interactions')
        )
            ->wherePivot('relation', '=', Interaction::RELATION_FAVORITE)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }

    public function reportersCount()
    {
        return $this->reporters()->count();
    }

    public function getreportersCountAttribute()
    {
        return $this->reportersCount();
    }

    public function reportersCountReadable($precision = 1, $divisors = null)
    {
        return Interaction::numberToReadable($this->reportersCount(), $precision, $divisors);
    }
}

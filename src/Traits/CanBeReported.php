<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanBeReported.
 */
trait CanBeReported
{
    /**
     * Check if a model is reported by given model.
     *
     * @param  int|array|Model  $user
     */
    public function isReportedBy($user): bool
    {
        return Interaction::isRelationExists($this, 'reporters', $user);
    }

    /**
     * Return reporters.
     */
    public function reporters(): MorphToMany
    {
        return $this->morphToMany(
            Interaction::getUserModelName(),
            'subject',
            config('acquaintances.tables.interactions')
        )
            ->wherePivot('relation', '=', Interaction::RELATION_REPORT)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }

    public function reportersCount(): int
    {
        return $this->reporters()->count();
    }

    public function getReportersCountAttribute(): int
    {
        return $this->reportersCount();
    }

    public function reportersCountReadable($precision = 1, $divisors = null): string
    {
        return Interaction::numberToReadable($this->reportersCount(), $precision, $divisors);
    }
}

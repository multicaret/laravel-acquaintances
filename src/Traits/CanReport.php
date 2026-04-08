<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Event;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanReport.
 */
trait CanReport
{
    /**
     * Report an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     */
    public function report($targets, $class = __CLASS__): array
    {
        Event::dispatch('acq.reports.report', [$this, $targets]);

        return Interaction::attachRelations($this, 'reports', $targets, $class);
    }

    /**
     * Unreport an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     */
    public function unreport($targets, $class = __CLASS__): array
    {
        Event::dispatch('acq.reports.unreport', [$this, $targets]);

        return Interaction::detachRelations($this, 'reports', $targets, $class);
    }

    /**
     * Toggle report an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     */
    public function toggleReport($targets, $class = __CLASS__): array
    {
        return Interaction::toggleRelations($this, 'reports', $targets, $class);
    }

    /**
     * Check if a model has reported given model.
     *
     * @param  int|array|Model  $target
     * @param  string  $class
     */
    public function hasReported($target, $class = __CLASS__): bool
    {
        return Interaction::isRelationExists($this, 'reports', $target, $class);
    }

    /**
     * Return item reports.
     *
     * @param  string  $class
     */
    public function reports($class = __CLASS__): MorphToMany
    {
        return $this->morphedByMany(
            $class,
            'subject',
            config('acquaintances.tables.interactions')
        )
            ->wherePivot('relation', '=', Interaction::RELATION_REPORT)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }
}

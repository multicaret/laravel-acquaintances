<?php


namespace Multicaret\Acquaintances\Traits;

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
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     */
    public function report($targets, $class = __CLASS__)
    {
        Event::dispatch('acq.reports.report', [$this, $targets]);

        return Interaction::attachRelations($this, 'reports', $targets, $class);
    }

    /**
     * Unreport an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     */
    public function unreport($targets, $class = __CLASS__)
    {
        Event::dispatch('acq.reports.unreport', [$this, $targets]);

        return Interaction::detachRelations($this, 'reports', $targets, $class);
    }

    /**
     * Toggle report an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     */
    public function toggleReport($targets, $class = __CLASS__)
    {
        return Interaction::toggleRelations($this, 'reports', $targets, $class);
    }

    /**
     * Check if a model is reported given model.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $target
     * @param  string  $class
     *
     * @return bool
     */
    public function hasReported($target, $class = __CLASS__)
    {
        return Interaction::isRelationExists($this, 'reports', $target, $class);
    }

    /**
     * Return item reports.
     *
     * @param  string  $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function reports($class = __CLASS__)
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

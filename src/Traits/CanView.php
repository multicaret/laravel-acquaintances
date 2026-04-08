<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Event;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanView.
 */
trait CanView
{
    /**
     * View an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     *
     * @throws \Exception
     */
    public function view($targets, $class = __CLASS__): array
    {
        Event::dispatch('acq.views.view', [$this, $targets]);

        return Interaction::attachRelations($this, 'views', $targets, $class);
    }

    /**
     * Unview an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     */
    public function unview($targets, $class = __CLASS__): array
    {
        Event::dispatch('acq.views.unview', [$this, $targets]);

        return Interaction::detachRelations($this, 'views', $targets, $class);
    }

    /**
     * Toggle view an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     *
     * @throws \Exception
     */
    public function toggleView($targets, $class = __CLASS__): array
    {
        return Interaction::toggleRelations($this, 'views', $targets, $class);
    }

    /**
     * Check if a model has viewed a given model.
     *
     * @param  int|array|Model  $target
     * @param  string  $class
     */
    public function hasViewed($target, $class = __CLASS__): bool
    {
        return Interaction::isRelationExists($this, 'views', $target, $class);
    }

    /**
     * Return item views.
     *
     * @param  string  $class
     */
    public function views($class = __CLASS__): MorphToMany
    {
        return $this->morphedByMany($class, 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_VIEW)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }
}

<?php


namespace Multicaret\Acquaintances\Traits;

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
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     *
     * @throws \Exception
     */
    public function view($targets, $class = __CLASS__, $actor = null)
    {
        Event::dispatch('acq.views.view', [$this, $targets]);

        $updates = [];
        if ($actor) {
            $updates = array_merge(
                $updates,
                [
                    'actor_type' => $actor->getMorphClass(),
                    'actor_id'   => $actor->id,
                ]
            );
        }

        return Interaction::attachRelations($this, 'views', $targets, $class);
    }

    /**
     * Unview an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     */
    public function unview($targets, $class = __CLASS__)
    {
        Event::dispatch('acq.views.unview', [$this, $targets]);

        return Interaction::detachRelations($this, 'views', $targets, $class);
    }

    /**
     * Toggle view an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     *
     * @throws \Exception
     */
    public function toggleView($targets, $class = __CLASS__)
    {
        return Interaction::toggleRelations($this, 'views', $targets, $class);
    }

    /**
     * Check if a model has viewed a given model.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $target
     * @param  string  $class
     *
     * @return bool
     */
    public function hasViewed($target, $class = __CLASS__)
    {
        return Interaction::isRelationExists($this, 'views', $target, $class);
    }

    /**
     * Return item views.
     *
     * @param  string  $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function views($class = __CLASS__)
    {
        return $this->morphedByMany(
            $class,
            'subject',
            config('acquaintances.tables.interactions')
        )->wherePivot('relation', '=', Interaction::RELATION_VIEW)->withPivot(
            ...Interaction::$pivotColumns
        )->using(Interaction::getInteractionRelationModelName());
    }
}

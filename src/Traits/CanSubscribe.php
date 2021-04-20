<?php


namespace Multicaret\Acquaintances\Traits;

use Illuminate\Support\Facades\Event;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanSubscribe.
 */
trait CanSubscribe
{
    /**
     * Subscribe an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     *
     * @throws \Exception
     */
    public function subscribe($targets, $class = __CLASS__, $actor = null)
    {
        Event::dispatch('acq.subscriptions.subscribe', [$this, $targets]);

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

        return Interaction::attachRelations(
            $this,
            'subscriptions',
            $targets,
            $class,
            $updates
        );
    }

    /**
     * Unsubscribe an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     */
    public function unsubscribe($targets, $class = __CLASS__)
    {
        Event::dispatch('acq.subscriptions.unsubscribe', [$this, $targets]);

        return Interaction::detachRelations(
            $this,
            'subscriptions',
            $targets,
            $class
        );
    }

    /**
     * Toggle subscribe an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     *
     * @throws \Exception
     */
    public function toggleSubscribe($targets, $class = __CLASS__)
    {
        return Interaction::toggleRelations(
            $this,
            'subscriptions',
            $targets,
            $class
        );
    }

    /**
     * Check if a model is subscribed to a given model.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $target
     * @param  string  $class
     *
     * @return bool
     */
    public function hasSubscribed($target, $class = __CLASS__)
    {
        return Interaction::isRelationExists(
            $this,
            'subscriptions',
            $target,
            $class
        );
    }

    /**
     * Return user subscriptions.
     *
     * @param  string  $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subscriptions($class = __CLASS__)
    {
        return $this->morphedByMany(
            $class,
            'subject',
            config('acquaintances.tables.interactions')
        )->wherePivot('relation', '=', Interaction::RELATION_SUBSCRIBE)
            ->withPivot(...Interaction::$pivotColumns)->using(
                Interaction::getInteractionRelationModelName()
            );
    }
}

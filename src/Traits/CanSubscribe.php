<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
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
     * @param  int|array|Model  $targets
     * @param  string  $class
     *
     * @throws \Exception
     */
    public function subscribe($targets, $class = __CLASS__): array
    {
        Event::dispatch('acq.subscriptions.subscribe', [$this, $targets]);

        return Interaction::attachRelations($this, 'subscriptions', $targets, $class);
    }

    /**
     * Unsubscribe an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     */
    public function unsubscribe($targets, $class = __CLASS__): int
    {
        Event::dispatch('acq.subscriptions.unsubscribe', [$this, $targets]);

        return Interaction::detachRelations($this, 'subscriptions', $targets, $class);
    }

    /**
     * Toggle subscribe an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     *
     * @throws \Exception
     */
    public function toggleSubscribe($targets, $class = __CLASS__): array
    {
        return Interaction::toggleRelations($this, 'subscriptions', $targets, $class);
    }

    /**
     * Check if a model is subscribed to a given model.
     *
     * @param  int|array|Model  $target
     * @param  string  $class
     */
    public function hasSubscribed($target, $class = __CLASS__): bool
    {
        return Interaction::isRelationExists($this, 'subscriptions', $target, $class);
    }

    /**
     * Return user subscriptions.
     *
     * @param  string  $class
     */
    public function subscriptions($class = __CLASS__): MorphToMany
    {
        return $this->morphedByMany($class, 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_SUBSCRIBE)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }
}

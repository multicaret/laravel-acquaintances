<?php


namespace Multicaret\Acquaintances\Traits;

use Illuminate\Support\Facades\Event;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanFollow.
 */
trait CanFollow
{
    /**
     * Interaction an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     *
     * @throws \Exception
     */
    public function follow($targets, $class = __CLASS__, $actor = null)
    {
        Event::dispatch('acq.followships.follow', [$this, $targets]);
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
            'followings',
            $targets,
            $class,
            $updates
        );
    }

    /**
     * Unfollow an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     */
    public function unfollow($targets, $class = __CLASS__)
    {
        Event::dispatch('acq.followships.unfollow', [$this, $targets]);

        return Interaction::detachRelations(
            $this,
            'followings',
            $targets,
            $class
        );
    }

    /**
     * Toggle follow an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     *
     * @throws \Exception
     */
    public function toggleFollow($targets, $class = __CLASS__)
    {
        return Interaction::toggleRelations(
            $this,
            'followings',
            $targets,
            $class
        );
    }

    /**
     * Check if a model is following given model.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $target
     * @param  string  $class
     *
     * @return bool
     */
    public function isFollowing($target, $class = __CLASS__)
    {
        return Interaction::isRelationExists(
            $this,
            'followings',
            $target,
            $class
        );
    }

    /**
     * Return item followings.
     *
     * @param  string  $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followings($class = __CLASS__)
    {
        return $this->morphedByMany(
            $class,
            'subject',
            config('acquaintances.tables.interactions')
        )->wherePivot('relation', '=', Interaction::RELATION_FOLLOW)->withPivot(
            ...Interaction::$pivotColumns
        )->using(Interaction::getInteractionRelationModelName());
    }
}

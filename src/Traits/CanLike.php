<?php


namespace Multicaret\Acquaintances\Traits;

use Illuminate\Support\Facades\Event;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanLike.
 */
trait CanLike
{
    /**
     * Like an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     *
     * @throws \Exception
     */
    public function like($targets, $class = __CLASS__, $actor = null)
    {
        Event::dispatch('acq.likes.like', [$this, $targets]);

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
            'likes',
            $targets,
            $class,
            $updates
        );
    }

    /**
     * Unlike an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     */
    public function unlike($targets, $class = __CLASS__)
    {
        Event::dispatch('acq.likes.unlike', [$this, $targets]);

        return Interaction::detachRelations($this, 'likes', $targets, $class);
    }

    /**
     * Toggle like an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     *
     * @throws \Exception
     */
    public function toggleLike($targets, $class = __CLASS__)
    {
        return Interaction::toggleRelations($this, 'likes', $targets, $class);
    }

    /**
     * Check if a model is liked by a given model.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $target
     * @param  string  $class
     *
     * @return bool
     */
    public function hasLiked($target, $class = __CLASS__)
    {
        return Interaction::isRelationExists($this, 'likes', $target, $class);
    }

    /**
     * Return item likes.
     *
     * @param  string  $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likes($class = __CLASS__)
    {
        return $this->morphedByMany(
            $class,
            'subject',
            config('acquaintances.tables.interactions')
        )->wherePivot('relation', '=', Interaction::RELATION_LIKE)->withPivot(
            ...Interaction::$pivotColumns
        )->using(Interaction::getInteractionRelationModelName());
    }
}

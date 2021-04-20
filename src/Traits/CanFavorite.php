<?php


namespace Multicaret\Acquaintances\Traits;

use Illuminate\Support\Facades\Event;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanFavorite.
 */
trait CanFavorite
{
    /**
     * Favorite an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     */
    public function favorite($targets, $class = __CLASS__, $actor = null)
    {
        Event::dispatch('acq.favorites.favorite', [$this, $targets]);

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
            'favorites',
            $targets,
            $class,
            $updates
        );
    }

    /**
     * Unfavorite an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     */
    public function unfavorite($targets, $class = __CLASS__)
    {
        Event::dispatch('acq.favorites.unfavorite', [$this, $targets]);

        return Interaction::detachRelations(
            $this,
            'favorites',
            $targets,
            $class
        );
    }

    /**
     * Toggle favorite an item or items.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $targets
     * @param  string  $class
     *
     * @return array
     */
    public function toggleFavorite($targets, $class = __CLASS__)
    {
        return Interaction::toggleRelations(
            $this,
            'favorites',
            $targets,
            $class
        );
    }

    /**
     * Check if a model is favorited given model.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $target
     * @param  string  $class
     *
     * @return bool
     */
    public function hasFavorited($target, $class = __CLASS__)
    {
        return Interaction::isRelationExists(
            $this,
            'favorites',
            $target,
            $class
        );
    }

    /**
     * Return item favorites.
     *
     * @param  string  $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favorites($class = __CLASS__)
    {
        return $this->morphedByMany(
            $class,
            'subject',
            config('acquaintances.tables.interactions')
        )->wherePivot('relation', '=', Interaction::RELATION_FAVORITE)
            ->withPivot(...Interaction::$pivotColumns)->using(
                Interaction::getInteractionRelationModelName()
            );
    }
}

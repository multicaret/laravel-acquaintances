<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
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
     * @param  int|array|Model  $targets
     * @param  string  $class
     */
    public function favorite($targets, $class = __CLASS__): array
    {
        Event::dispatch('acq.favorites.favorite', [$this, $targets]);

        return Interaction::attachRelations($this, 'favorites', $targets, $class);
    }

    /**
     * Unfavorite an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     */
    public function unfavorite($targets, $class = __CLASS__): array
    {
        Event::dispatch('acq.favorites.unfavorite', [$this, $targets]);

        return Interaction::detachRelations($this, 'favorites', $targets, $class);
    }

    /**
     * Toggle favorite an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     */
    public function toggleFavorite($targets, $class = __CLASS__): array
    {
        return Interaction::toggleRelations($this, 'favorites', $targets, $class);
    }

    /**
     * Check if a model is favorited given model.
     *
     * @param  int|array|Model  $target
     * @param  string  $class
     */
    public function hasFavorited($target, $class = __CLASS__): bool
    {
        return Interaction::isRelationExists($this, 'favorites', $target, $class);
    }

    /**
     * Return item favorites.
     *
     * @param  string  $class
     */
    public function favorites($class = __CLASS__): MorphToMany
    {
        return $this->morphedByMany($class, 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_FAVORITE)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }
}

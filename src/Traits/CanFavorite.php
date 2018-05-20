<?php


namespace Liliom\Acquaintances\Traits;

use Illuminate\Support\Facades\Event;
use Liliom\Acquaintances\Interaction;

/**
 * Trait CanFavorite.
 */
trait CanFavorite
{
    /**
     * Favorite an item or items.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $targets
     * @param string                                        $class
     *
     * @return array
     */
    public function favorite($targets, $class = __CLASS__)
    {
        Event::fire('acq.favorites.favorite', [$this, $targets]);
        return Interaction::attachRelations($this, 'favorites', $targets, $class);
    }

    /**
     * Unfavorite an item or items.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $targets
     * @param string                                        $class
     *
     * @return array
     */
    public function unfavorite($targets, $class = __CLASS__)
    {
        Event::fire('acq.favorites.unfavorite', [$this, $targets]);
        return Interaction::detachRelations($this, 'favorites', $targets, $class);
    }

    /**
     * Toggle favorite an item or items.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $targets
     * @param string                                        $class
     *
     * @return array
     */
    public function toggleFavorite($targets, $class = __CLASS__)
    {
        return Interaction::toggleRelations($this, 'favorites', $targets, $class);
    }

    /**
     * Check if user is favorited given item.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $target
     * @param string                                        $class
     *
     * @return bool
     */
    public function hasFavorited($target, $class = __CLASS__)
    {
        return Interaction::isRelationExists($this, 'favorites', $target, $class);
    }

    /**
     * Return item favorites.
     *
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favorites($class = __CLASS__)
    {
        return $this->morphedByMany($class, config('acquaintances.morph_prefix'),
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_FAVORITE)
                    ->withPivot('followable_type', 'relation', 'created_at');
    }
}

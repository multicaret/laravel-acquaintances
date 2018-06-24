<?php


namespace Liliom\Acquaintances\Traits;

use Illuminate\Support\Facades\Event;
use Liliom\Acquaintances\Interaction;

/**
 * Trait CanLike.
 */
trait CanLike
{
    /**
     * Like an item or items.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $targets
     * @param string                                        $class
     *
     * @return array
     *
     * @throws \Exception
     */
    public function like($targets, $class = __CLASS__)
    {
        Event::fire('acq.likes.like', [$this, $targets]);

        return Interaction::attachRelations($this, 'likes', $targets, $class);
    }

    /**
     * Unlike an item or items.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $targets
     * @param string                                        $class
     *
     * @return array
     */
    public function unlike($targets, $class = __CLASS__)
    {
        Event::fire('acq.likes.unlike', [$this, $targets]);

        return Interaction::detachRelations($this, 'likes', $targets, $class);
    }

    /**
     * Toggle like an item or items.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $targets
     * @param string                                        $class
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
     * Check if user is liked given item.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $target
     * @param string                                        $class
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
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likes($class = __CLASS__)
    {
        return $this->morphedByMany($class, config('acquaintances.morph_prefix'),
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_LIKE)
                    ->withPivot('followable_type', 'relation', 'created_at');
    }
}

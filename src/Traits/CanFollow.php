<?php


namespace Liliom\Acquaintances\Traits;

use Liliom\Acquaintances\Follow;

/**
 * Trait CanFollow.
 */
trait CanFollow
{
    /**
     * Follow an item or items.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $targets
     * @param string                                        $class
     *
     * @return array
     */
    public function follow($targets, $class = __CLASS__)
    {
        return Follow::attachRelations($this, 'followings', $targets, $class);
    }

    /**
     * Unfollow an item or items.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $targets
     * @param string                                        $class
     *
     * @return array
     */
    public function unfollow($targets, $class = __CLASS__)
    {
        return Follow::detachRelations($this, 'followings', $targets, $class);
    }

    /**
     * Toggle follow an item or items.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $targets
     * @param string                                        $class
     *
     * @return array
     */
    public function toggleFollow($targets, $class = __CLASS__)
    {
        return Follow::toggleRelations($this, 'followings', $targets, $class);
    }

    /**
     * Check if user is following given item.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $target
     * @param string                                        $class
     *
     * @return bool
     */
    public function isFollowing($target, $class = __CLASS__)
    {
        return Follow::isRelationExists($this, 'followings', $target, $class);
    }

    /**
     * Return item followings.
     *
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followings($class = __CLASS__)
    {
        return $this->morphedByMany($class, config('acquaintance.morph_prefix'),
            config('acquaintance.tables.followships'))
                    ->wherePivot('relation', '=', Follow::RELATION_FOLLOW)
                    ->withPivot('followable_type', 'relation', 'created_at');
    }
}

<?php


namespace Liliom\Acquaintances\Traits;

use Liliom\Acquaintances\Follow;


/**
 * Trait CanBeFollowed.
 */
trait CanBeFollowed
{
    /**
     * Check if user is followed by given user.
     *
     * @param int $user
     *
     * @return bool
     */
    public function isFollowedBy($user)
    {
        return Follow::isRelationExists($this, 'followers', $user);
    }

    /**
     * Return followers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followers()
    {
        return $this->morphToMany(config('acquaintance.user_model'), config('acquaintance.morph_prefix'),
            config('acquaintance.tables.followships'))
                    ->wherePivot('relation', '=', Follow::RELATION_FOLLOW)
                    ->withPivot('followable_type', 'relation', 'created_at');
    }
}

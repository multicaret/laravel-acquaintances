<?php


namespace Liliom\Acquaintances\Traits;

use Liliom\Acquaintances\Interaction;


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
        return Interaction::isRelationExists($this, 'followers', $user);
    }

    /**
     * Return followers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followers()
    {
        return $this->morphToMany(config('acquaintances.user_model'), config('acquaintances.morph_prefix'),
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_FOLLOW)
                    ->withPivot('followable_type', 'relation', 'created_at');
    }
}

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
        return $this
            ->morphToMany(
                config('auth.providers.users.model'),
                'subject',
                config('acquaintances.tables.interactions')
            )
            ->wherePivot('relation', '=', Interaction::RELATION_FOLLOW)
            ->withPivot('subject_type', 'relation', 'created_at');
    }
}

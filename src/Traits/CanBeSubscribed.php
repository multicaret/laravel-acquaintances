<?php


namespace Liliom\Acquaintances\Traits;

use Liliom\Acquaintances\Interaction;

/**
 * Trait CanBeSubscribed.
 */
trait CanBeSubscribed
{
    /**
     * Check if user is subscribed by given user.
     *
     * @param int $user
     *
     * @return bool
     */
    public function isSubscribedBy($user)
    {
        return Interaction::isRelationExists($this, 'subscribers', $user);
    }

    /**
     * Return followers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subscribers()
    {
        return $this->morphToMany(config('acquaintances.user_model'), config('acquaintances.morph_prefix'),
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_SUBSCRIBE)
                    ->withPivot('followable_type', 'relation', 'created_at');
    }
}

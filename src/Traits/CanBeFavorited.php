<?php


namespace Liliom\Acquaintances\Traits;

use Liliom\Acquaintances\Follow;


/**
 * Trait CanBeFavorited.
 */
trait CanBeFavorited
{
    /**
     * Check if user is favorited by given user.
     *
     * @param int $user
     *
     * @return bool
     */
    public function isFavoritedBy($user)
    {
        return Follow::isRelationExists($this, 'favoriters', $user);
    }

    /**
     * Return favoriters.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoriters()
    {
        return $this->morphToMany(config('acquaintance.user_model'), config('acquaintance.morph_prefix'),
            config('acquaintance.tables.followships'))
                    ->wherePivot('relation', '=', Follow::RELATION_FAVORITE)
                    ->withPivot('followable_type', 'relation', 'created_at');
    }
}

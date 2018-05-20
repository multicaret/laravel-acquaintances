<?php


namespace Liliom\Acquaintances\Traits;

use Liliom\Acquaintances\Interaction;


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
        return Interaction::isRelationExists($this, 'favoriters', $user);
    }

    /**
     * Return favoriters.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoriters()
    {
        return $this->morphToMany(config('acquaintances.user_model'), config('acquaintances.morph_prefix'),
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_FAVORITE)
                    ->withPivot('followable_type', 'relation', 'created_at');
    }
}

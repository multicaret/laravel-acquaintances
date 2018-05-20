<?php


namespace Liliom\Acquaintances\Traits;

use Liliom\Acquaintances\Interaction;

/**
 * Trait CanBeVoted.
 */
trait CanBeVoted
{
    /**
     * Check if item is voted by given user.
     *
     * @param int $user
     *
     * @return bool
     */
    public function isVotedBy($user, $type = null)
    {
        return Interaction::isRelationExists($this, 'voters', $user);
    }

    /**
     * Check if item is upvoted by given user.
     *
     * @param int $user
     *
     * @return bool
     */
    public function isUpvotedBy($user)
    {
        return Interaction::isRelationExists($this, 'upvoters', $user);
    }

    /**
     * Check if item is downvoted by given user.
     *
     * @param int $user
     *
     * @return bool
     */
    public function isDownvotedBy($user)
    {
        return Interaction::isRelationExists($this, 'downvoters', $user);
    }

    /**
     * Return voters.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function voters()
    {
        return $this->morphToMany(config('acquaintances.user_model'), config('acquaintances.morph_prefix'),
            config('acquaintances.tables.interactions'))
                    ->wherePivotIn('relation', [Interaction::RELATION_UPVOTE, Interaction::RELATION_DOWNVOTE])
                    ->withPivot('followable_type', 'relation', 'created_at');
    }

    /**
     * Return upvoters.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function upvoters()
    {
        return $this->morphToMany(config('acquaintances.user_model'), config('acquaintances.morph_prefix'),
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_UPVOTE)
                    ->withPivot('followable_type', 'relation', 'created_at');
    }

    /**
     * Return downvoters.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function downvoters()
    {
        return $this->morphToMany(config('acquaintances.user_model'), config('acquaintances.morph_prefix'),
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_DOWNVOTE)
                    ->withPivot('followable_type', 'relation', 'created_at');
    }
}

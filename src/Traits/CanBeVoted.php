<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanBeVoted.
 */
trait CanBeVoted
{
    /**
     * Check if item is voted by given user.
     *
     * @param  int|array|Model  $user
     */
    public function isVotedBy($user, $type = null): bool
    {
        return Interaction::isRelationExists($this, 'voters', $user);
    }

    /**
     * Check if item is upvoted by given user.
     *
     * @param  int|array|Model  $user
     */
    public function isUpvotedBy($user): bool
    {
        return Interaction::isRelationExists($this, 'upvoters', $user);
    }

    /**
     * Check if item is downvoted by given user.
     *
     * @param  int|array|Model  $user
     */
    public function isDownvotedBy($user): bool
    {
        return Interaction::isRelationExists($this, 'downvoters', $user);
    }

    /**
     * Return voters.
     */
    public function voters(): MorphToMany
    {
        return $this->morphToMany(Interaction::getUserModelName(), 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivotIn('relation', [Interaction::RELATION_UPVOTE, Interaction::RELATION_DOWNVOTE])
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }

    /**
     * Return upvoters.
     */
    public function upvoters(): MorphToMany
    {
        return $this->morphToMany(Interaction::getUserModelName(), 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_UPVOTE)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }

    /**
     * Return downvoters.
     */
    public function downvoters(): MorphToMany
    {
        return $this->morphToMany(Interaction::getUserModelName(), 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_DOWNVOTE)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }

    public function upvotersCount(): int
    {
        return $this->upvoters()->count();
    }

    public function getUpvotersCountAttribute(): int
    {
        return $this->upvotersCount();
    }

    public function upvotersCountReadable($precision = 1, $divisors = null): string
    {
        return Interaction::numberToReadable($this->upvotersCount(), $precision, $divisors);
    }

    public function downvotersCount(): int
    {
        return $this->downvoters()->count();
    }

    public function getDownvotersCountAttribute(): int
    {
        return $this->downvotersCount();
    }

    public function downvotersCountReadable($precision = 1, $divisors = null): string
    {
        return Interaction::numberToReadable($this->downvotersCount(), $precision, $divisors);
    }

    public function votersCount(): int
    {
        return $this->voters()->count();
    }

    public function getVotersCountAttribute(): int
    {
        return $this->votersCount();
    }

    public function votersCountReadable($precision = 1, $divisors = null): string
    {
        return Interaction::numberToReadable($this->votersCount(), $precision, $divisors);
    }
}

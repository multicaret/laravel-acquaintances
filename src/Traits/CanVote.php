<?php


namespace Multicaret\Acquaintances\Traits;

use Illuminate\Support\Facades\Event;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanVote.
 */
trait CanVote
{
    /**
     * Vote an item or items.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $targets
     * @param string                                        $type
     * @param string                                        $class
     *
     * @return array
     *
     * @throws \Exception
     */
    public function vote($targets, $type = 'upvote', $class = __CLASS__)
    {
        $this->cancelVote($targets);

        return Interaction::attachRelations($this, str_plural($type), $targets, $class);
    }

    /**
     * Upvote an item or items.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $targets
     * @param string                                        $class
     *
     * @return array
     *
     * @throws \Exception
     */
    public function upvote($targets, $class = __CLASS__)
    {
        Event::dispatch('acq.vote.up', [$this, $targets]);

        return $this->vote($targets, 'upvote', $class);
    }

    /**
     * Downvote an item or items.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $targets
     * @param string                                        $class
     *
     * @return array
     *
     * @throws \Exception
     */
    public function downvote($targets, $class = __CLASS__)
    {
        Event::dispatch('acq.vote.down', [$this, $targets]);

        return $this->vote($targets, 'downvote', $class);
    }

    /**
     * Cancel vote for an item or items.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $targets
     * @param string                                        $class
     *
     * @return Multicaret\Acquaintances\Traits\CanVote
     */
    public function cancelVote($targets, $class = __CLASS__)
    {
        Interaction::detachRelations($this, 'upvotes', $targets, $class);
        Interaction::detachRelations($this, 'downvotes', $targets, $class);
        Event::dispatch('acq.vote.cancel', [$this, $targets]);

        return $this;
    }

    /**
     * Check if user is upvoted given item.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $target
     * @param string                                        $class
     *
     * @return bool
     */
    public function hasUpvoted($target, $class = __CLASS__)
    {
        return Interaction::isRelationExists($this, 'upvotes', $target, $class);
    }

    /**
     * Check if user is downvoted given item.
     *
     * @param int|array|\Illuminate\Database\Eloquent\Model $target
     * @param string                                        $class
     *
     * @return bool
     */
    public function hasDownvoted($target, $class = __CLASS__)
    {
        return Interaction::isRelationExists($this, 'downvotes', $target, $class);
    }

    /**
     * Return item votes.
     *
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function votes($class = __CLASS__)
    {
        return $this->morphedByMany($class, 'subject',
            config('acquaintances.tables.interactions'))
                    ->wherePivotIn('relation', [Interaction::RELATION_UPVOTE, Interaction::RELATION_DOWNVOTE])
                    ->withPivot(...Interaction::$pivotColumns);
    }

    /**
     * Return item upvotes.
     *
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function upvotes($class = __CLASS__)
    {
        return $this->morphedByMany($class, 'subject',
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_UPVOTE)
                    ->withPivot(...Interaction::$pivotColumns);
    }

    /**
     * Return item downvotes.
     *
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function downvotes($class = __CLASS__)
    {
        return $this->morphedByMany($class, 'subject',
            config('acquaintances.tables.interactions'))
                    ->wherePivot('relation', '=', Interaction::RELATION_DOWNVOTE)
                    ->withPivot(...Interaction::$pivotColumns);
    }
}

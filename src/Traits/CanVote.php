<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanVote.
 */
trait CanVote
{
    /**
     * Vote an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $type
     * @param  string  $class
     *
     * @throws \Exception
     */
    public function vote($targets, $type = 'upvote', $class = __CLASS__): array
    {
        $this->cancelVote($targets);

        return Interaction::attachRelations($this, Str::plural($type), $targets, $class);
    }

    /**
     * Upvote an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     *
     * @throws \Exception
     */
    public function upvote($targets, $class = __CLASS__): array
    {
        Event::dispatch('acq.vote.up', [$this, $targets]);

        return $this->vote($targets, 'upvote', $class);
    }

    /**
     * Downvote an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     *
     * @throws \Exception
     */
    public function downvote($targets, $class = __CLASS__): array
    {
        Event::dispatch('acq.vote.down', [$this, $targets]);

        return $this->vote($targets, 'downvote', $class);
    }

    /**
     * Cancel vote for an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     */
    public function cancelVote($targets, $class = __CLASS__): static
    {
        Interaction::detachRelations($this, 'upvotes', $targets, $class);
        Interaction::detachRelations($this, 'downvotes', $targets, $class);
        Event::dispatch('acq.vote.cancel', [$this, $targets]);

        return $this;
    }

    /**
     * Check if a model has upvoted a given model.
     *
     * @param  int|array|Model  $target
     * @param  string  $class
     */
    public function hasUpvoted($target, $class = __CLASS__): bool
    {
        return Interaction::isRelationExists($this, 'upvotes', $target, $class);
    }

    /**
     * Check if user has downvoted given item.
     *
     * @param  int|array|Model  $target
     * @param  string  $class
     */
    public function hasDownvoted($target, $class = __CLASS__): bool
    {
        return Interaction::isRelationExists($this, 'downvotes', $target, $class);
    }

    /**
     * Return item votes.
     *
     * @param  string  $class
     */
    public function votes($class = __CLASS__): MorphToMany
    {
        return $this->morphedByMany($class, 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivotIn('relation', [Interaction::RELATION_UPVOTE, Interaction::RELATION_DOWNVOTE])
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }

    /**
     * Return item upvotes.
     *
     * @param  string  $class
     */
    public function upvotes($class = __CLASS__): MorphToMany
    {
        return $this->morphedByMany($class, 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_UPVOTE)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }

    /**
     * Return item downvotes.
     *
     * @param  string  $class
     */
    public function downvotes($class = __CLASS__): MorphToMany
    {
        return $this->morphedByMany($class, 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_DOWNVOTE)
            ->withPivot(...Interaction::$pivotColumns)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();
    }
}

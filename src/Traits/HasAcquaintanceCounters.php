<?php

namespace Multicaret\Acquaintances\Traits;

use Multicaret\Acquaintances\Interaction;

/**
 * Trait HasAcquaintanceCounters
 *
 * Provides cached counter columns for interaction counts on the model's table.
 * Models using this trait should have integer columns matching the configured
 * counter names (e.g., `likes_count`, `followers_count`).
 *
 * The trait hooks into Eloquent boot to register observers that automatically
 * increment/decrement these counters when interactions are attached/detached.
 */
trait HasAcquaintanceCounters
{
    /**
     * Map of interaction relation names to their counter column names.
     * Override this method to customize which counters are tracked.
     *
     * @return array<string, string>
     */
    public function acquaintanceCounterMap(): array
    {
        return [
            Interaction::RELATION_LIKE => 'likes_count',
            Interaction::RELATION_FOLLOW => 'followers_count',
            Interaction::RELATION_SUBSCRIBE => 'subscribers_count',
            Interaction::RELATION_FAVORITE => 'favoriters_count',
            Interaction::RELATION_UPVOTE => 'upvoters_count',
            Interaction::RELATION_DOWNVOTE => 'downvoters_count',
            Interaction::RELATION_VIEW => 'viewers_count',
        ];
    }

    /**
     * Increment the counter column for the given relation type.
     */
    public function incrementAcquaintanceCounter(string $relation): void
    {
        $map = $this->acquaintanceCounterMap();

        if (isset($map[$relation]) && $this->hasColumn($map[$relation])) {
            $this->increment($map[$relation]);
        }
    }

    /**
     * Decrement the counter column for the given relation type.
     */
    public function decrementAcquaintanceCounter(string $relation): void
    {
        $map = $this->acquaintanceCounterMap();

        if (isset($map[$relation]) && $this->hasColumn($map[$relation])) {
            $this->decrement($map[$relation]);
        }
    }

    /**
     * Check if the model's table has the given column.
     */
    protected function hasColumn(string $column): bool
    {
        return $this->getConnection()
            ->getSchemaBuilder()
            ->hasColumn($this->getTable(), $column);
    }
}

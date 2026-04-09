<?php

namespace Multicaret\Acquaintances\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Multicaret\Acquaintances\Interaction;

/**
 * Provides reusable Eloquent scopes for eager-loading acquaintance counts.
 */
class AcquaintanceScopes
{
    /**
     * Register the `withAcquaintanceCounts` macro on the Eloquent Builder.
     */
    public static function register(): void
    {
        Builder::macro('withAcquaintanceCounts', function (?array $relations = null) {
            /** @var Builder $this */
            $available = [
                'likers' => Interaction::RELATION_LIKE,
                'followers' => Interaction::RELATION_FOLLOW,
                'subscribers' => Interaction::RELATION_SUBSCRIBE,
                'favoriters' => Interaction::RELATION_FAVORITE,
                'upvoters' => Interaction::RELATION_UPVOTE,
                'downvoters' => Interaction::RELATION_DOWNVOTE,
                'raters' => Interaction::RELATION_RATE,
                'viewers' => Interaction::RELATION_VIEW,
                'reporters' => Interaction::RELATION_REPORT,
            ];

            $toLoad = $relations ?? array_keys($available);

            $withCounts = [];
            foreach ($toLoad as $relation) {
                if (array_key_exists($relation, $available)) {
                    $withCounts[] = $relation;
                }
            }

            return $this->withCount($withCounts);
        });
    }
}

<?php


namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Multicaret\Acquaintances\Interaction;

trait CanBeActor
{
    /**
     * Check if a model is favorited by given model.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $user
     *
     * @return bool
     */
    public function isFavoritedBy($user)
    {
        return Interaction::isRelationExists($this, 'favoriters', $user);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function getInteractions()
    {
        return $this->morphMany(
            Interaction::getInteractionRelationModelName(),
            'actor',
        );
    }

    public function getRelation($relation)
    {
        return $this->getInteractions()->where(
            'relation',
            '=',
            $relation
        );
    }

    /**
     * @param  array|Model  $models
     * @param  array|string  $relationTypes
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder
     */
    public function getRatesVia($models, $relationTypes = [])
    {
        return $this->getRelation(Interaction::RELATION_RATE)->whereHasMorph(
            'subject',
            $models
        )->when(
            count($relationTypes) > 0,
            function ($query) use ($relationTypes) {
                $query->whereIn('relation_type', $relationTypes);
            }
        );
    }


    /**
     * @param  array|Model  $models
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder
     */
    public function getLikesVia($models
    ): \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder {
        return $this->getRelation(Interaction::RELATION_LIKE)->whereHasMorph(
            'subject',
            $models
        );
    }

    /**
     * @param  array|Model  $models
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder
     */
    public function getFollowsVia($models
    ): \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder {
        return $this->getRelation(Interaction::RELATION_FOLLOW)->whereHasMorph(
            'subject',
            $models
        );
    }

    /**
     * @param  array|Model  $models
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder
     */
    public function getSubscribesVia($models
    ): \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder {
        return $this->getRelation(Interaction::RELATION_SUBSCRIBE)
            ->whereHasMorph(
                'subject',
                $models
            );
    }

    /**
     * @param  array|Model  $models
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder
     */
    public function getFavoritesVia($models
    ): \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder {
        return $this->getRelation(Interaction::RELATION_FAVORITE)
            ->whereHasMorph(
                'subject',
                $models
            );
    }

    /**
     * @param  array|Model  $models
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder
     */
    public function getUpVotesVia($models
    ): \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder {
        return $this->getRelation(Interaction::RELATION_UPVOTE)->whereHasMorph(
            'subject',
            $models
        );
    }

    /**
     * @param  array|Model  $models
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder
     */
    public function getDownVotesVia($models
    ): \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder {
        return $this->getRelation(Interaction::RELATION_DOWNVOTE)
            ->whereHasMorph(
                'subject',
                $models
            );
    }

    /**
     * @param  array|Model  $models
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder
     */
    public function getViewsVia($models
    ): \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Builder {
        return $this->getRelation(Interaction::RELATION_VIEW)->whereHasMorph(
            'subject',
            $models
        );
    }
}

<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanBeRated.
 */
trait CanBeRated
{
    private static $ratedType = null;

    public static function bootCanBeRated(): void
    {
        self::$ratedType = config('acquaintances.rating.defaults.type');
    }

    public function setRatedType(string $ratingType): static
    {
        self::$ratedType = $ratingType;

        return $this;
    }

    private function ratedType($type = null): string
    {
        if (empty($type) && empty(self::$ratedType)) {
            $this->setRatedType(config('acquaintances.rating.defaults.type'));
        } elseif (! empty($type)) {
            $this->setRatedType($type);
        }

        return self::$ratedType;
    }

    /**
     * Check if a model is isRatedBy by given user.
     *
     * @param  int|array|Model  $user
     */
    public function isRatedBy($user): bool
    {
        return Interaction::isRelationExists($this, 'raters', $user);
    }

    /**
     * Return Raters.
     */
    public function raters($isAllTypes = false): MorphToMany
    {
        $relation = $this->morphToMany(Interaction::getUserModelName(), 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_RATE)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();

        if (! $isAllTypes) {
            $relation = $relation->wherePivot('relation_type', '=', $this->ratedType());
        }

        return $relation->withPivot(...Interaction::$pivotColumns);
    }

    /**
     * Return ratings as interaction items with lazy loading `user` relation.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Interaction::getInteractionRelationModelName(), 'subject_id')->with('user');
    }

    /**
     * Return ratings as interaction items without lazy loading `user` relation.
     */
    public function ratingsPure(): HasMany
    {
        return $this->hasMany(Interaction::getInteractionRelationModelName(), 'subject_id');
    }

    public function averageRating($ratingType = null): mixed
    {
        $this->ratedType($ratingType);

        return $this->raters()->avg('relation_value');
    }

    public function averageRatingAllTypes(): mixed
    {
        return $this->raters(true)->avg('relation_value');
    }

    public function sumRating($ratingType = null): mixed
    {
        $this->ratedType($ratingType);

        return $this->raters()->sum('relation_value');
    }

    public function sumRatingAllTypes(): mixed
    {
        return $this->raters(true)->sum('relation_value');
    }

    public function sumRatingReadable($ratingType = null, $precision = 1, $divisors = null): string
    {
        return Interaction::numberToReadable($this->sumRating($ratingType), $precision, $divisors);
    }

    public function sumRatingAllTypesReadable($precision = 1, $divisors = null): string
    {
        return Interaction::numberToReadable($this->sumRatingAllTypes(), $precision, $divisors);
    }

    public function userAverageRating($ratingType = null): mixed
    {
        $this->ratedType($ratingType);

        return $this->raters()->where('user_id', \Auth::id())->avg('relation_value');
    }

    public function userAverageRatingAllTypes(): mixed
    {
        return $this->raters(true)->where('user_id', \Auth::id())->avg('relation_value');
    }

    public function userSumRating($ratingType = null): mixed
    {
        $this->ratedType($ratingType);

        return $this->raters()->where('user_id', \Auth::id())->sum('relation_value');
    }

    public function userSumRatingAllTypes(): mixed
    {
        return $this->raters(true)->where('user_id', \Auth::id())->sum('relation_value');
    }

    public function userSumRatingReadable($ratingType = null, $precision = 1, $divisors = null): string
    {
        return Interaction::numberToReadable($this->userSumRating($ratingType), $precision, $divisors);
    }

    public function userSumRatingAllTypesReadable($precision = 1, $divisors = null): string
    {
        return Interaction::numberToReadable($this->userSumRatingAllTypes(), $precision, $divisors);
    }

    /**
     * Calculating the percentage based on the passed coefficient.
     */
    public function ratingPercent($max = null, $ratingType = null): float|int
    {
        $this->ratedType($ratingType);
        if (empty($max)) {
            $max = config('acquaintances.rating.defaults.amount');
        }
        $quantity = $this->raters()->count();
        $total = $this->sumRating();

        return ($quantity * $max) > 0 ? $total / (($quantity * $max) / 100) : 0;
    }

    /**
     * Calculating the percentage based on the passed coefficient for all types.
     */
    public function ratingPercentAllTypes($max = null): float|int
    {
        if (empty($max)) {
            $max = config('acquaintances.rating.defaults.amount');
        }
        $quantity = $this->raters(true)->count();
        $total = $this->sumRating();

        return ($quantity * $max) > 0 ? $total / (($quantity * $max) / 100) : 0;
    }

    public function getAverageRatingAttribute(): mixed
    {
        return $this->averageRating();
    }

    public function getAverageRatingAllTypesAttribute(): mixed
    {
        return $this->averageRatingAllTypes();
    }

    public function getSumRatingAttribute(): mixed
    {
        return $this->sumRating();
    }

    public function getSumRatingAllTypesAttribute(): mixed
    {
        return $this->sumRatingAllTypes();
    }

    public function getUserAverageRatingAttribute(): mixed
    {
        return $this->userAverageRating();
    }

    public function getUserAverageRatingAllTypesAttribute(): mixed
    {
        return $this->userAverageRatingAllTypes();
    }

    public function getUserSumRatingAttribute(): mixed
    {
        return $this->userSumRating();
    }

    public function getUserSumRatingAllTypesAttribute(): mixed
    {
        return $this->userSumRatingAllTypes();
    }
}

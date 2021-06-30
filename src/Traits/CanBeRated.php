<?php


namespace Multicaret\Acquaintances\Traits;

use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanBeRated.
 */
trait CanBeRated
{
    private static $ratedType = null;


    public static function bootCanBeRated()
    {
        self::$ratedType = config('acquaintances.rating.defaults.type');
    }

    public function setRatedType(string $ratingType)
    {
        self::$ratedType = $ratingType;

        return $this;
    }

    private function ratedType($type = null)
    {
        if (empty($type) && empty(self::$ratedType)) {
            $this->setRatedType(config('acquaintances.rating.defaults.type'));
        } elseif ( ! empty($type)) {
            $this->setRatedType($type);
        }

        /*
         * Todo: [minor feature] Check if the passed type exists in types array
         *  config('acquaintances.rating.types')
         */

        return self::$ratedType;
    }

    /**
     * Check if a model is isRatedBy by given user.
     *
     * @param  int|array|\Illuminate\Database\Eloquent\Model  $user
     *
     * @return bool
     */
    public function isRatedBy($user)
    {
        return Interaction::isRelationExists($this, 'raters', $user);
    }

    /**
     * Return Raters.
     *
     * @param  bool  $isAllTypes
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function raters($isAllTypes = false)
    {
        $relation = $this->morphToMany(Interaction::getUserModelName(), 'subject',
            config('acquaintances.tables.interactions'))
                         ->wherePivot('relation', '=', Interaction::RELATION_RATE)
                         ->using(Interaction::getInteractionRelationModelName());

        if ( ! $isAllTypes) {
            $relation = $relation->wherePivot('relation_type', '=', $this->ratedType());
        }

        return $relation->withPivot(...Interaction::$pivotColumns);
    }

    /**
     * Return ratings as interaction items with lazy loading `user` relation
     * for a specific type or for the default type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ratings()
    {
        return $this->hasMany(Interaction::getInteractionRelationModelName(), 'subject_id')->with('user');
    }

    /**
     * Return ratings as interaction items without lazy loading `user` relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ratingsPure()
    {
        return $this->hasMany(Interaction::getInteractionRelationModelName(), 'subject_id');
    }

    public function averageRating($ratingType = null)
    {
        $this->ratedType($ratingType);

        return $this->raters()->avg('relation_value');
    }

    public function averageRatingAllTypes()
    {
        return $this->raters(true)->avg('relation_value');
    }

    public function sumRating($ratingType = null)
    {
        $this->ratedType($ratingType);

        return $this->raters()->sum('relation_value');
    }

    public function sumRatingAllTypes()
    {
        return $this->raters(true)->sum('relation_value');
    }

    public function sumRatingReadable($ratingType = null, $precision = 1, $divisors = null)
    {
        return Interaction::numberToReadable($this->sumRating($ratingType), $precision, $divisors);
    }

    public function sumRatingAllTypesReadable($precision = 1, $divisors = null)
    {
        return Interaction::numberToReadable($this->sumRatingAllTypes(), $precision, $divisors);
    }

    public function userAverageRating($ratingType = null)
    {
        $this->ratedType($ratingType);

        return $this->raters()->where('user_id', \Auth::id())->avg('relation_value');
    }

    public function userAverageRatingAllTypes()
    {
        return $this->raters(true)->where('user_id', \Auth::id())->avg('relation_value');
    }

    public function userSumRating($ratingType = null)
    {
        $this->ratedType($ratingType);

        return $this->raters()->where('user_id', \Auth::id())->sum('relation_value');
    }

    public function userSumRatingAllTypes()
    {
        return $this->raters(true)->where('user_id', \Auth::id())->sum('relation_value');
    }

    public function userSumRatingReadable($ratingType = null, $precision = 1, $divisors = null)
    {
        return Interaction::numberToReadable($this->userSumRating($ratingType), $precision, $divisors);
    }


    public function userSumRatingAllTypesReadable($precision = 1, $divisors = null)
    {
        return Interaction::numberToReadable($this->userSumRatingAllTypes(), $precision, $divisors);
    }

    /**
     * Calculating the percentage based on the passed coefficient
     * Taking the default value of $max from within the config file
     *
     * @param  null  $max
     *
     * @param  null  $ratingType
     *
     * @return float|int
     */
    public function ratingPercent($max = null, $ratingType = null)
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
     * Calculating the percentage based on the passed coefficient
     * Taking the default value of $max from within the config file
     *
     * @param  null  $max
     *
     *
     * @return float|int
     */
    public function ratingPercentAllTypes($max = null)
    {
        if (empty($max)) {
            $max = config('acquaintances.rating.defaults.amount');
        }
        $quantity = $this->raters(true)->count();
        $total = $this->sumRating();

        return ($quantity * $max) > 0 ? $total / (($quantity * $max) / 100) : 0;
    }

    public function getAverageRatingAttribute()
    {
        return $this->averageRating();
    }

    public function getAverageRatingAllTypesAttribute()
    {
        return $this->averageRatingAllTypes();
    }

    public function getSumRatingAttribute()
    {
        return $this->sumRating();
    }

    public function getSumRatingAllTypesAttribute()
    {
        return $this->sumRatingAllTypes();
    }

    public function getUserAverageRatingAttribute()
    {
        return $this->userAverageRating();
    }

    public function getUserAverageRatingAllTypesAttribute()
    {
        return $this->userAverageRatingAllTypes();
    }

    public function getUserSumRatingAttribute()
    {
        return $this->userSumRating();
    }

    public function getUserSumRatingAllTypesAttribute()
    {
        return $this->userSumRatingAllTypes();
    }

}

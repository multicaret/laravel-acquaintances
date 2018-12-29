<?php


namespace Liliom\Acquaintances\Traits;

use Liliom\Acquaintances\Interaction;

/**
 * Trait CanBeLiked.
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
        } else if ( ! empty($type)) {
            $this->setRatedType($type);
        }

        /*
         * todo: Check if the passed type exists in types array
        *  config('acquaintances.rating.types')
        */

        return self::$ratedType;
    }

    /**
     * Check if user is isRatedBy by given user.
     *
     * @param int $user
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function raters()
    {
        $relation = $this->morphToMany(config('auth.providers.users.model'), 'subject',
            config('acquaintances.tables.interactions'))
                         ->wherePivot('relation', '=', Interaction::RELATION_RATE);

        if ($this->ratedType() != 'overall') {
            $relation = $relation->wherePivot('relation_type', '=', $this->ratedType());
        }

        return $relation->withPivot(...Interaction::$pivotColumns);
    }

    public function averageRating($ratingType = null)
    {
        $this->ratedType($ratingType);

        return $this->raters()->avg('relation_value');
    }

    public function sumRating($ratingType = null)
    {
        $this->ratedType($ratingType);

        return $this->raters()->sum('relation_value');
    }

    public function sumRatingReadable($ratingType = null, $precision = 1, $divisors = null)
    {
        return Interaction::numberToReadable($this->sumRating($ratingType), $precision, $divisors);
    }

    public function userAverageRating($ratingType = null)
    {
        $this->ratedType($ratingType);

        return $this->raters()->where('user_id', \Auth::id())->avg('relation_value');
    }

    public function userSumRating($ratingType = null)
    {
        $this->ratedType($ratingType);

        return $this->raters()->where('user_id', \Auth::id())->sum('relation_value');
    }

    public function userSumRatingReadable($ratingType = null, $precision = 1, $divisors = null)
    {
        return Interaction::numberToReadable($this->sumRating($ratingType), $precision, $divisors);
    }

    /**
     * Calculating the percentage based on the passed coefficient
     * Taking the default value of $max from within the config file
     *
     * @param null $max
     *
     * @param null $ratingType
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

    public function getAverageRatingAttribute()
    {
        return $this->averageRating();
    }

    public function getSumRatingAttribute()
    {
        return $this->sumRating();
    }

    public function getUserAverageRatingAttribute()
    {
        return $this->userAverageRating();
    }

    public function getUserSumRatingAttribute()
    {
        return $this->userSumRating();
    }

}

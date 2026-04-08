<?php

namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Event;
use Multicaret\Acquaintances\Interaction;

/**
 * Trait CanRate.
 */
trait CanRate
{
    private static $rateType = null;

    public static function bootCanRate(): void
    {
        self::$rateType = config('acquaintances.rating.defaults.type');
    }

    public function setRateType(string $ratingType): static
    {
        self::$rateType = $ratingType;

        return $this;
    }

    private function rateType($type = null): string
    {
        if (empty($type) && empty(self::$rateType)) {
            $this->setRateType(config('acquaintances.rating.defaults.type'));
        } elseif (! empty($type)) {
            $this->setRateType($type);
        }

        return self::$rateType;
    }

    /**
     * Rate an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     *
     * @throws \Exception
     */
    public function rate($targets, float $amount, $ratingType = null, $class = __CLASS__): array
    {
        Event::dispatch('acq.ratings.rate', [$this, $targets]);
        $attachRelations = Interaction::attachRelations($this, 'ratingsTo', $targets, $class, [
            'relation_value' => $amount,
            'relation_type' => $this->rateType($ratingType),
        ]);
        self::$rateType = config('acquaintances.rating.defaults.type');

        return $attachRelations;
    }

    /**
     * UnRate an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     *
     * @throws \Exception
     */
    public function unrate($targets, $ratingType = null, $class = __CLASS__): array
    {
        Event::dispatch('acq.ratings.unrate', [$this, $targets]);

        return Interaction::detachRelations($this, 'ratingsTo', $targets, $class, [
            'relation_type' => $this->rateType($ratingType),
        ]);
    }

    /**
     * Toggle Rate an item or items.
     *
     * @param  int|array|Model  $targets
     * @param  string  $class
     *
     * @throws \Exception
     */
    public function toggleRate($targets, float $amount, $ratingType = null, $class = __CLASS__): array
    {
        return Interaction::toggleRelations($this, 'ratingsTo', $targets, $class, [
            'relation_value' => $amount,
            'relation_type' => $this->rateType($ratingType),
        ]);
    }

    /**
     * Check if a model is rated by given model.
     *
     * @param  int|array|Model  $target
     * @param  string  $class
     */
    public function hasRated($target, $ratingType = null, $class = __CLASS__): bool
    {
        return Interaction::isRelationExists($this, 'ratingsTo', $target, $class, [
            'relation_type' => $this->rateType($ratingType),
        ]);
    }

    /**
     * Return item ratings.
     *
     * @param  string  $class
     */
    public function ratingsTo($class = __CLASS__): MorphToMany
    {
        $relation = $this->morphedByMany($class, 'subject',
            config('acquaintances.tables.interactions'))
            ->wherePivot('relation', '=', Interaction::RELATION_RATE)
            ->using(Interaction::getInteractionRelationModelName())
            ->withTimestamps();

        $relation = $relation->wherePivot('relation_type', '=', $this->rateType());

        return $relation->withPivot(...Interaction::$pivotColumns);
    }
}

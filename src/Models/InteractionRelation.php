<?php

namespace Multicaret\Acquaintances\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Multicaret\Acquaintances\Interaction;

/**
 * Class InteractionRelation.
 */
class InteractionRelation extends MorphPivot
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $with = ['subject'];

    public function subject(): MorphTo
    {
        return $this->morphTo('subject');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Interaction::getUserModelName());
    }

    /**
     * @param  Builder  $query
     * @param  string|null  $type
     * @return Builder
     */
    public function scopePopular($query, $type = null)
    {
        $query->select('subject_id', 'subject_type', \DB::raw('COUNT(*) AS count'))
            ->groupBy('subject_id', 'subject_type')
            ->orderByDesc('count');

        if ($type) {
            $query->where('subject_type', $this->normalizeSubjectType($type));
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        if (! $this->table) {
            $this->table = config('acquaintances.tables.interactions', 'interactions');
        }

        return parent::getTable();
    }

    /**
     * {@inheritdoc}
     */
    public function getDates()
    {
        return [parent::CREATED_AT];
    }

    /**
     * @param  string  $type
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function normalizeSubjectType($type)
    {
        $morphMap = Relation::morphMap();

        if (! empty($morphMap) && in_array($type, $morphMap, true)) {
            $type = array_search($type, $morphMap, true);
        }

        if (class_exists($type)) {
            return $type;
        }

        $namespace = config('acquaintances.model_namespace', 'App');

        $modelName = $namespace.'\\'.Str::studly($type);

        if (! class_exists($modelName)) {
            throw new InvalidArgumentException("Model {$modelName} not exists. Please check your config 'acquaintances.model_namespace'.");
        }

        return $modelName;
    }
}

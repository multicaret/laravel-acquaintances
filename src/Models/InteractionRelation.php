<?php


namespace Liliom\Acquaintances\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;

/**
 * Class InteractionRelation.
 */
class InteractionRelation extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $with = ['subject'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function subject()
    {
        return $this->morphTo('subject');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null                           $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
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
        if ( ! $this->table) {
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
     * @param string $type
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function normalizeSubjectType($type)
    {
        $morphMap = Relation::morphMap();

        if ( ! empty($morphMap) && in_array($type, $morphMap, true)) {
            $type = array_search($type, $morphMap, true);
        }

        if (class_exists($type)) {
            return $type;
        }

        $namespace = config('acquaintances.model_namespace', 'App');

        $modelName = $namespace . '\\' . studly_case($type);

        if ( ! class_exists($modelName)) {
            throw new InvalidArgumentException("Model {$modelName} not exists. Please check your config 'acquaintances.model_namespace'.");
        }

        return $modelName;
    }
}

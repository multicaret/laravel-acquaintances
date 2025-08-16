<?php


namespace Multicaret\Acquaintances\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Verification
 * @package Multicaret\Acquaintances\Models
 */
class Verification extends Model
{
    /**
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * @param  array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('acquaintances.tables.verifications');

        parent::__construct($attributes);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function sender()
    {
        return $this->morphTo('sender');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function recipient()
    {
        return $this->morphTo('recipient');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function groups()
    {
        return $this->hasMany(VerificationGroups::class, 'verification_id');
    }

    /**
     * @param  Model  $recipient
     *
     * @return $this
     */
    public function fillRecipient($recipient)
    {
        return $this->fill([
            'recipient_id' => $recipient->getKey(),
            'recipient_type' => $recipient->getMorphClass()
        ]);
    }

    /**
     * @param       $query
     * @param  Model  $model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereRecipient($query, $model)
    {
        return $query->where('recipient_id', $model->getKey())
            ->where('recipient_type', $model->getMorphClass());
    }

    /**
     * @param       $query
     * @param  Model  $model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereSender($query, $model)
    {
        return $query->where('sender_id', $model->getKey())
            ->where('sender_type', $model->getMorphClass());
    }

    /**
     * @param        $query
     * @param  Model  $model
     * @param  string  $groupSlug
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereGroup($query, $model, $groupSlug)
    {

        $groupsPivotTable = config('acquaintances.tables.verification_groups');
        $verifierPivotTable = config('acquaintances.tables.verifications');
        $groupsAvailable = config('acquaintances.verifications_groups', []);

        if ('' !== $groupSlug && isset($groupsAvailable[$groupSlug])) {

            $groupId = $groupsAvailable[$groupSlug];

            $query->join(
                $groupsPivotTable,
                function ($join) use ($groupsPivotTable, $verifierPivotTable, $groupId, $model) {
                    $join->on($groupsPivotTable . '.verification_id', '=', $verifierPivotTable . '.id')
                        ->where($groupsPivotTable . '.group_id', '=', $groupId)
                        ->where(function ($query) use ($groupsPivotTable, $verifierPivotTable, $model) {
                            $query->where($groupsPivotTable . '.verifier_id', '!=', $model->getKey())
                                ->where($groupsPivotTable . '.verifier_type', '=', $model->getMorphClass());
                        })
                        ->orWhere($groupsPivotTable . '.verifier_type', '!=', $model->getMorphClass());
                }
            );
        }

        return $query;
    }

    /**
     * @param       $query
     * @param  Model  $sender
     * @param  Model  $recipient
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenModels($query, $sender, $recipient)
    {
        $query->where(function ($queryIn) use ($sender, $recipient) {
            $queryIn->where(function ($q) use ($sender, $recipient) {
                $q->whereSender($sender)->whereRecipient($recipient);
            })->orWhere(function ($q) use ($sender, $recipient) {
                $q->whereSender($recipient)->whereRecipient($sender);
            });
        });
    }

    /**
     * @return mixed|null
     */
    public function getGroupSlugAttribute()
    {
        if ($this->status === 'accepted' && $this->groups->isNotEmpty()) {
            $groupId = $this->groups->first()->group_id;
            $groups = config('acquaintances.verifications_groups', []);
            return array_search($groupId, $groups);
        }
        return null;
    }
}

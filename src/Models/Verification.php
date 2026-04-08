<?php

namespace Multicaret\Acquaintances\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Verification
 */
class Verification extends Model
{
    /**
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function __construct(array $attributes = [])
    {
        $this->table = config('acquaintances.tables.verifications');

        parent::__construct($attributes);
    }

    public function sender(): MorphTo
    {
        return $this->morphTo('sender');
    }

    public function recipient(): MorphTo
    {
        return $this->morphTo('recipient');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(VerificationGroups::class, 'verification_id');
    }

    /**
     * @param  Model  $recipient
     * @return $this
     */
    public function fillRecipient($recipient)
    {
        return $this->fill([
            'recipient_id' => $recipient->getKey(),
            'recipient_type' => $recipient->getMorphClass(),
        ]);
    }

    /**
     * @param  Model  $model
     * @return Builder
     */
    public function scopeWhereRecipient($query, $model)
    {
        return $query->where('recipient_id', $model->getKey())
            ->where('recipient_type', $model->getMorphClass());
    }

    /**
     * @param  Model  $model
     * @return Builder
     */
    public function scopeWhereSender($query, $model)
    {
        return $query->where('sender_id', $model->getKey())
            ->where('sender_type', $model->getMorphClass());
    }

    /**
     * @param  Model  $model
     * @param  string  $groupSlug
     * @return Builder
     */
    public function scopeWhereGroup($query, $model, $groupSlug)
    {

        $groupsPivotTable = config('acquaintances.tables.verification_groups');
        $verifierPivotTable = config('acquaintances.tables.verifications');
        $groupsAvailable = config('acquaintances.verifications_groups', []);

        if ($groupSlug !== '' && isset($groupsAvailable[$groupSlug])) {

            $groupId = $groupsAvailable[$groupSlug];

            $query->join(
                $groupsPivotTable,
                function ($join) use ($groupsPivotTable, $verifierPivotTable, $groupId, $model) {
                    $join->on($groupsPivotTable.'.verification_id', '=', $verifierPivotTable.'.id')
                        ->where($groupsPivotTable.'.group_id', '=', $groupId)
                        ->where(function ($query) use ($groupsPivotTable, $model) {
                            $query->where($groupsPivotTable.'.verifier_id', '!=', $model->getKey())
                                ->where($groupsPivotTable.'.verifier_type', '=', $model->getMorphClass());
                        })
                        ->orWhere($groupsPivotTable.'.verifier_type', '!=', $model->getMorphClass());
                }
            );
        }

        return $query;
    }

    /**
     * @param  Model  $sender
     * @param  Model  $recipient
     * @return Builder
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

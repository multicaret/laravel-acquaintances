<?php

namespace Multicaret\Acquaintances\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Friendship
 */
class Friendship extends Model
{
    /**
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function __construct(array $attributes = [])
    {
        $this->table = config('acquaintances.tables.friendships');

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
        return $this->hasMany(FriendFriendshipGroups::class, 'friendship_id');
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

        $groupsPivotTable = config('acquaintances.tables.friendship_groups');
        $friendsPivotTable = config('acquaintances.tables.friendships');
        $groupsAvailable = config('acquaintances.friendships_groups', []);

        if ($groupSlug !== '' && isset($groupsAvailable[$groupSlug])) {

            $groupId = $groupsAvailable[$groupSlug];

            $query->join($groupsPivotTable,
                function ($join) use ($groupsPivotTable, $friendsPivotTable, $groupId, $model) {
                    $join->on($groupsPivotTable.'.friendship_id', '=', $friendsPivotTable.'.id')
                        ->where($groupsPivotTable.'.group_id', '=', $groupId)
                        ->where(function ($query) use ($groupsPivotTable, $model) {
                            $query->where($groupsPivotTable.'.friend_id', '!=', $model->getKey())
                                ->where($groupsPivotTable.'.friend_type', '=', $model->getMorphClass());
                        })
                        ->orWhere($groupsPivotTable.'.friend_type', '!=', $model->getMorphClass());
                });

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
}

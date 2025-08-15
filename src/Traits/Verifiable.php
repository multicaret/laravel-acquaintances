<?php


namespace Multicaret\Acquaintances\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Multicaret\Acquaintances\Interaction;
use Multicaret\Acquaintances\Models\Verification;
use Multicaret\Acquaintances\Status;

/**
 * Class Verifiable
 * @package Multicaret\Acquaintances\Traits
 */
trait Verifiable
{
    /**
     * @param  Model  $recipient
     * @param  string|null  $verificationMessage
     *
     * @return \Multicaret\Acquaintances\Models\Verification|false
     */
    public function verify(Model $recipient, ?string $verificationMessage = null)
    {

        if (! $this->canVerify($recipient)) {
            return false;
        }

        $verifierModelName = Interaction::getVerificationModelName();
        $verifier = (new $verifierModelName)->fillRecipient($recipient)->fill([
            'status' => Status::PENDING,
            'message' => $verificationMessage
        ]);

        $this->verifications()->save($verifier);

        Event::dispatch('acq.verifications.sent', [$this, $recipient]);

        return $verifier;
    }


    /**
     * @param  Model  $recipient
     *
     * @return bool
     */
    public function unverify(Model $recipient)
    {
        Event::dispatch('acq.verifications.cancelled', [$this, $recipient]);

        return $this->findVerification($recipient)->delete();
    }

    /**
     * @param  Model  $recipient
     *
     * @return bool
     */
    public function hasVerificationRequestFrom(Model $recipient)
    {
        return $this->findVerification($recipient)->whereSender($recipient)->whereStatus(Status::PENDING)->exists();
    }

    /**
     * @param  Model  $recipient
     *
     * @return bool
     */
    public function hasSentVerificationRequestTo(Model $recipient)
    {
        $verifierModelName = Interaction::getVerificationModelName();

        return $verifierModelName::whereRecipient($recipient)->whereSender($this)->whereStatus(Status::PENDING)->exists();
    }

    /**
     * @param  Model  $recipient
     *
     * @return bool
     */
    public function isVerifiedWith(Model $recipient)
    {
        return $this->findVerification($recipient)->where('status', Status::ACCEPTED)->exists();
    }

    /**
     * @param  Model  $recipient
     *
     * @return bool|int
     */
    public function acceptVerificationRequest(Model $recipient)
    {
        Event::dispatch('acq.verifications.accepted', [$this, $recipient]);

        return $this->findVerification($recipient)->whereRecipient($this)->update([
            'status' => Status::ACCEPTED,
        ]);
    }

    /**
     * @param  Model  $recipient
     *
     * @return bool|int
     */
    public function denyVerificationRequest(Model $recipient)
    {
        Event::dispatch('acq.verifications.denied', [$this, $recipient]);

        return $this->findVerification($recipient)->whereRecipient($this)->update([
            'status' => Status::DENIED,
        ]);
    }


    /**
     * @param  Model  $verifier
     * @param  string $groupSlug
     *
     * @return bool
     */
    public function groupVerification(Model $verifier, $groupSlug)
    {
        $verification = $this->findVerification($verifier)->whereStatus(Status::ACCEPTED)->first();
        $groupsAvailable = config('acquaintances.verifications_groups', []);

        if (! isset($groupsAvailable[$groupSlug]) || empty($verification)) {
            return false;
        }

        $group = $verification->groups()->firstOrCreate([
            'verification_id' => $verification->id,
            'group_id' => $groupsAvailable[$groupSlug],
            'verifier_id' => $verifier->getKey(),
            'verifier_type' => $verifier->getMorphClass(),
        ]);

        return $group->wasRecentlyCreated;
    }

    /**
     * @param  Model  $verifier
     * @param       $groupSlug
     *
     * @return bool
     */
    public function ungroupVerification(Model $verifier, $groupSlug = '')
    {
        $verification = $this->findVerification($verifier)->first();
        $groupsAvailable = config('acquaintances.verifications_groups', []);

        if (empty($verification)) {
            return false;
        }

        $where = [
            'verification_id' => $verification->id,
            'verifier_id' => $verifier->getKey(),
            'verifier_type' => $verifier->getMorphClass(),
        ];

        if ('' !== $groupSlug && isset($groupsAvailable[$groupSlug])) {
            $where['group_id'] = $groupsAvailable[$groupSlug];
        }

        $result = $verification->groups()->where($where)->delete();

        return $result;
    }

    /**
     * @param  Model  $recipient
     *
     * @return \Multicaret\Acquaintances\Models\Verification
     */
    public function blockVerification(Model $recipient)
    {
        // if there is a verification between the two users and the sender is not blocked
        // by the recipient user then delete the verification
        if (! $this->isBlockedBy($recipient)) {
            $this->findVerification($recipient)->delete();
        }

        $verificationModelName = Interaction::getVerificationModelName();
        $verification = (new $verificationModelName)->fillRecipient($recipient)->fill([
            'status' => Status::BLOCKED,
        ]);

        Event::dispatch('acq.verifications.blocked', [$this, $recipient]);

        return $this->verifications()->save($verification);
    }

    /**
     * @param  Model  $recipient
     *
     * @return mixed
     */
    public function unblockVerification(Model $recipient)
    {
        Event::dispatch('acq.verifications.unblocked', [$this, $recipient]);

        return $this->findVerification($recipient)->whereSender($this)->delete();
    }

    /**
     * @param  Model  $recipient
     *
     * @return \Multicaret\Acquaintances\Models\Verification
     */
    public function getVerification(Model $recipient)
    {
        return $this->findVerification($recipient)->first();
    }

    /**
     * @param  string  $groupSlug
     * @param  int  $perPage  Number
     * @param  array  $fields
     * @param  string  $type
     *
     * @return \Illuminate\Database\Eloquent\Collection|Verification[]
     */
    public function getAllVerifications(
        string $groupSlug = '',
        int $perPage = 0,
        array $fields = ['*'],
        string $type = 'all'
    ) {
        return $this->getOrPaginateVerifications($this->findVerifications(null, $groupSlug, $type), $perPage, $fields);
    }

    /**
     * @param  string  $groupSlug
     * @param  int  $perPage  Number
     * @param  array  $fields
     * @param  string  $type
     *
     * @return \Illuminate\Database\Eloquent\Collection|Verification[]
     */
    public function getPendingVerifications(
        string $groupSlug = '',
        int $perPage = 0,
        array $fields = ['*'],
        string $type = 'all'
    ) {
        return $this->getOrPaginateVerifications($this->findVerifications(Status::PENDING, $groupSlug, $type), $perPage, $fields);
    }

    /**
     * @param  string  $groupSlug
     * @param  int  $perPage  Number
     * @param  array  $fields
     * @param  string  $type
     *
     * @return \Illuminate\Database\Eloquent\Collection|Verification[]
     */
    public function getAcceptedVerifications(
        string $groupSlug = '',
        int $perPage = 0,
        array $fields = ['*'],
        string $type = 'all'
    ) {
        return $this->getOrPaginateVerifications($this->findVerifications(Status::ACCEPTED, $groupSlug, $type), $perPage, $fields);
    }

    /**
     * @param  int  $perPage  Number
     * @param  array  $fields
     *
     * @return \Illuminate\Database\Eloquent\Collection|Verification[]
     */
    public function getDeniedVerifications(int $perPage = 0, array $fields = ['*'])
    {
        return $this->getOrPaginateVerifications($this->findVerifications(Status::DENIED), $perPage, $fields);
    }

    /**
     * @param  int  $perPage  Number
     * @param  array  $fields
     *
     * @return \Illuminate\Database\Eloquent\Collection|Verification[]
     */
    public function getBlockedVerifications(int $perPage = 0, array $fields = ['*'])
    {
        return $this->getOrPaginateVerifications($this->findVerifications(Status::BLOCKED), $perPage, $fields);
    }

    public function getBlockedVerificationsByCurrentUser(int $perPage = 0, array $fields = ['*'])
    {
        return $this->getOrPaginateVerifications($this->findVerifications(Status::BLOCKED, type: 'sender'), $perPage, $fields);
    }

    public function getBlockedVerificationsByOtherUsers(int $perPage = 0, array $fields = ['*'])
    {
        return $this->getOrPaginateVerifications($this->findVerifications(Status::BLOCKED, type: 'recipient'), $perPage, $fields);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|Verification[]
     */
    public function getVerificationRequests()
    {
        $verifierModelName = Interaction::getVerificationModelName();

        return $verifierModelName::whereRecipient($this)->whereStatus(Status::PENDING)->get();
    }

    /**
     * This method will not return Verification models
     * It will return the 'verifiers' models. ex: App\User
     *
     * @param  int  $perPage  Number
     * @param  string  $groupSlug
     *
     * @param  array  $fields
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVerifiers($perPage = 0, $groupSlug = '', array $fields = ['*'], bool $cursor = false)
    {
        return $this->getOrPaginateVerifications($this->getVerifiersQueryBuilder($groupSlug), $perPage, $fields, $cursor);
    }

    /**
     * This method will not return Verification models
     * It will return the 'verifiers' models. ex: App\User
     *
     * @param  Model  $other
     * @param  int  $perPage  Number
     *
     * @param  array  $fields
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMutualVerifiers(Model $other, $perPage = 0, array $fields = ['*'])
    {
        return $this->getOrPaginateVerifications($this->getMutualVerifiersQueryBuilder($other), $perPage, $fields);
    }

    /**
     * Get the number of verifiers
     *
     * @return integer
     */
    public function getMutualVerifiersCount($other)
    {
        return $this->getMutualVerifiersQueryBuilder($other)->count();
    }

    /**
     * Get the number of pending verifiers requests
     *
     * @return integer
     */
    public function getPendingVerificationsCount()
    {
        return $this->getPendingVerifications()->count();
    }

    /**
     * This method will not return Verification models
     * It will return the 'verifiers' models. ex: App\User
     *
     * @param  int  $perPage  Number
     *
     * @param  array  $fields
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVerifiersOfVerifiers($perPage = 0, array $fields = ['*'])
    {
        return $this->getOrPaginateVerifications($this->getVerifiersOfVerifiersQueryBuilder(), $perPage, $fields);
    }

    /**
     * Get the number of verifiers
     *
     * @param  string  $groupSlug
     * @param  string  $type
     *
     * @return integer
     */
    public function getVerifiersCount($groupSlug = '', $type = 'all')
    {
        $verifiersCount = $this->findVerifications(Status::ACCEPTED, $groupSlug, $type)->count();

        return $verifiersCount;
    }

    /**
     * @param  Model  $recipient
     *
     * @return bool
     */
    public function canVerify($recipient)
    {
        // if user has Blocked the recipient and changed his mind
        // he can send a verifier request after unblocking
        if ($this->hasBlocked($recipient)) {
            $this->unblockFriend($recipient);

            return true;
        }

        // if sender has a verification with the recipient return false
        if ($verification = $this->getVerification($recipient)) {
            // if previous verification was Denied then let the user send fr
            if ($verification->status != Status::DENIED) {
                return false;
            }
        }

        return true;
    }


    /**
     * @param  Model  $recipient
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function findVerification(Model $recipient)
    {
        $verificationModelName = Interaction::getVerificationModelName();

        return $verificationModelName::betweenModels($this, $recipient);
    }

    /**
     * @param         $status
     * @param  string $groupSlug
     * @param  string $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function findVerifications($status = null, string $groupSlug = '', string $type = 'all')
    {
        $verificationModelName = Interaction::getVerificationModelName();
        $query = $verificationModelName::where(function ($query) use ($type) {
            switch ($type) {
                case 'all':
                    $query->where(function ($q) {
                        $q->whereSender($this);
                    })
                        ->orWhere(function ($q) {
                            $q->whereRecipient($this);
                        });
                    break;
                case 'sender':
                    $query->where(function ($q) {
                        $q->whereSender($this);
                    });
                    break;
                case 'recipient':
                    $query->where(function ($q) {
                        $q->whereRecipient($this);
                    });
                    break;
            }
        })->whereGroup($this, $groupSlug)
            ->orderByRaw("FIELD(status, '" . implode("','", Status::getOrderedStatuses()) . "')");

        if (! is_null($status)) {
            $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Get the query builder of the 'verifier' model
     *
     * @param  string  $groupSlug
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getVerifiersQueryBuilder($groupSlug = '')
    {
        $verifications = $this->findVerifications(Status::ACCEPTED, $groupSlug)->get(['sender_id', 'recipient_id']);
        $recipients = $verifications->pluck('recipient_id')->all();
        $senders = $verifications->pluck('sender_id')->all();

        return $this->where('id', '!=', $this->getKey())
            ->whereIn('id', array_merge($recipients, $senders));
    }

    /**
     * Get the query builder of the 'verifier' model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getMutualVerifiersQueryBuilder(Model $other)
    {
        $user1['verifications'] = $this->findVerifications(Status::ACCEPTED)->get(['sender_id', 'recipient_id']);
        $user1['recipients'] = $user1['verifications']->pluck('recipient_id')->all();
        $user1['senders'] = $user1['verifications']->pluck('sender_id')->all();

        $user2['verifications'] = $other->findVerifications(Status::ACCEPTED)->get(['sender_id', 'recipient_id']);
        $user2['recipients'] = $user2['verifications']->pluck('recipient_id')->all();
        $user2['senders'] = $user2['verifications']->pluck('sender_id')->all();

        $mutualVerifications = array_unique(
            array_intersect(
                array_merge($user1['recipients'], $user1['senders']),
                array_merge($user2['recipients'], $user2['senders'])
            )
        );

        return $this->whereNotIn('id', [$this->getKey(), $other->getKey()])
            ->whereIn('id', $mutualVerifications);
    }

    /**
     * Get the query builder for verifiersOfVerifiers ('verifier' model)
     *
     * @param  string  $groupSlug
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getVerifiersOfVerifiersQueryBuilder($groupSlug = '')
    {
        $verifications = $this->findVerifications(Status::ACCEPTED)->get(['sender_id', 'recipient_id']);
        $recipients = $verifications->pluck('recipient_id')->all();
        $senders = $verifications->pluck('sender_id')->all();

        $verifierIds = array_unique(array_merge($recipients, $senders));

        $verificationModelName = Interaction::getVerificationModelName();
        $fofs = $verificationModelName::where('status', Status::ACCEPTED)
            ->where(function ($query) use ($verifierIds) {
                $query->where(function ($q) use ($verifierIds) {
                    $q->whereIn('sender_id', $verifierIds);
                })->orWhere(function ($q) use ($verifierIds) {
                    $q->whereIn('recipient_id', $verifierIds);
                });
            })
            ->whereGroup($this, $groupSlug)
            ->get(['sender_id', 'recipient_id']);

        $fofIds = array_unique(
            array_merge($fofs->pluck('sender_id')->all(), $fofs->pluck('recipient_id')->all())
        );

        return $this->whereIn('id', $fofIds)->whereNotIn('id', $verifierIds);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function verifications()
    {
        $verificationModelName = Interaction::getVerificationModelName();

        return $this->morphMany($verificationModelName, 'sender');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function verificationGroups()
    {
        $verificationGroupsModelName = Interaction::getVerificationGroupsModelName();

        return $this->morphMany($verificationGroupsModelName, 'verifier');
    }

    protected function getOrPaginateVerifications($builder, $perPage, array $fields = ['*'], bool $cursor = false)
    {
        if ($perPage == 0) {
            return $builder->get($fields);
        }

        if ($cursor) {
            return $builder->cursorPaginate($perPage, $fields);
        }

        return $builder->paginate($perPage, $fields);
    }
}

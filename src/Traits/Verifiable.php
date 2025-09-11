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
     * @param  string|null  $groupSlug
     *
     * @return \Multicaret\Acquaintances\Models\Verification|false
     */
    public function verify(Model $recipient, ?string $message = null, ?string $group = null)
    {

        // Prevent self-verification
        if ($this->getKey() === $recipient->getKey() && $this->getMorphClass() === $recipient->getMorphClass()) {
            throw new \InvalidArgumentException('Users cannot verify themselves.');
        }

        // Validate message length
        if ($message !== null) {
            $maxLength = config('platform.verification.max_length', 255);

            if (strlen($message) > $maxLength) {
                throw new \InvalidArgumentException(
                    "Verification message cannot exceed {$maxLength} characters. Current message length: " . strlen($message)
                );
            }
        }

        if (! $this->canVerify($recipient, $group)) {
            return false;
        }

        $verifierModelName = Interaction::getVerificationModelName();
        $verifier = (new $verifierModelName)->fillRecipient($recipient)->fill([
            'status' => Status::PENDING,
            'message' => $message,
            'group_slug' => $group,
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
    public function unverify(Model $recipient, int $verificationId)
    {
        Event::dispatch('acq.verifications.cancelled', [$this, $recipient]);

        return $this->findVerification($recipient, $verificationId)->delete();
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
     * Check if verified with a specific group type
     *
     * @param  Model  $recipient
     * @param  string  $groupSlug
     *
     * @return bool
     */
    public function isVerifiedWithGroup(Model $recipient, string $groupSlug)
    {
        $groupsAvailable = config('acquaintances.verifications_groups', []);

        if (!isset($groupsAvailable[$groupSlug])) {
            return false;
        }

        $groupId = $groupsAvailable[$groupSlug];

        $query = $this->findVerification($recipient)
            ->where('status', Status::ACCEPTED)
            ->whereHas('groups', function ($query) use ($groupId) {
                $query->where('group_id', $groupId);
            });

        return $query->exists();
    }

    /**
     * Get count of accepted verifications with a user
     *
     * @param  Model  $recipient
     *
     * @return int
     */
    public function getVerificationCount(Model $recipient)
    {
        return $this->findVerification($recipient)->where('status', Status::ACCEPTED)->count();
    }

    /**
     * Get all verification groups between users
     *
     * @param  Model  $recipient
     *
     * @return \Illuminate\Support\Collection
     */
    public function getVerificationGroups(Model $recipient)
    {
        $verificationModelName = Interaction::getVerificationModelName();
        $groupsAvailable = config('acquaintances.verifications_groups', []);

        if (empty($groupsAvailable)) {
            return collect([]);
        }

        // Get all accepted verifications with groups
        $verifications = $verificationModelName::where(function ($query) use ($recipient) {
            $query->where(function ($q) use ($recipient) {
                $q->where('sender_id', $this->getKey())
                    ->where('sender_type', $this->getMorphClass())
                    ->where('recipient_id', $recipient->getKey())
                    ->where('recipient_type', $recipient->getMorphClass());
            })->orWhere(function ($q) use ($recipient) {
                $q->where('sender_id', $recipient->getKey())
                    ->where('sender_type', $recipient->getMorphClass())
                    ->where('recipient_id', $this->getKey())
                    ->where('recipient_type', $this->getMorphClass());
            });
        })
            ->where('status', Status::ACCEPTED)
            ->with('groups')
            ->get();

        $groupSlugs = collect([]);

        foreach ($verifications as $verification) {
            if ($verification->groups) {
                foreach ($verification->groups as $group) {
                    $groupSlug = array_search($group->group_id, $groupsAvailable);
                    if ($groupSlug !== false) {
                        $groupSlugs->push($groupSlug);
                    }
                }
            }
        }

        return $groupSlugs->unique()->values();
    }

    /**
     * Accept a specific verification request by ID
     *
     * @param  int  $verificationId
     *
     * @return bool|int
     */
    public function acceptVerificationRequest($verificationId)
    {
        $verificationModelName = Interaction::getVerificationModelName();
        $verification = $verificationModelName::where('id', $verificationId)
            ->whereRecipient($this)
            ->first();

        if (!$verification) {
            return false;
        }

        Event::dispatch('acq.verifications.accepted', [$this, $verification->sender]);

        return $verification->update([
            'status' => Status::ACCEPTED,
        ]);
    }

    /**
     * Deny a specific verification request by ID
     *
     * @param  int  $verificationId
     *
     * @return bool|int
     */
    public function denyVerificationRequest($verificationId)
    {
        $verificationModelName = Interaction::getVerificationModelName();
        $verification = $verificationModelName::where('id', $verificationId)
            ->whereRecipient($this)
            ->first();

        if (!$verification) {
            return false;  // <-- Returns false if not found or not pending
        }

        Event::dispatch('acq.verifications.denied', [$this, $verification->sender]);

        return $verification->update([
            'status' => Status::DENIED,
        ]);
    }


    /**
     * @param  Model  $verifier
     * @param  string $groupSlug
     * @param  int|null $verificationId Specific verification to group (optional)
     *
     * @return bool
     */
    public function groupVerification(Model $verifier, $groupSlug, $verificationId = null)
    {
        $groupsAvailable = config('acquaintances.verifications_groups', []);

        if (! isset($groupsAvailable[$groupSlug])) {
            return false;
        }

        // If specific verification ID is provided, use it
        if ($verificationId) {
            $verification = $this->findVerification($verifier)
                ->whereStatus(Status::ACCEPTED)
                ->where('id', $verificationId)
                ->first();
        } else {
            // For backward compatibility, get the latest accepted verification
            $verification = $this->findVerification($verifier)
                ->whereStatus(Status::ACCEPTED)
                ->latest()
                ->first();
        }

        if (empty($verification)) {
            return false;
        }

        // Always allow grouping - no restrictions
        $group = $verification->groups()->updateOrCreate([
            'verification_id' => $verification->id,
            'group_id' => $groupsAvailable[$groupSlug],
            'verifier_id' => $verifier->getKey(),
            'verifier_type' => $verifier->getMorphClass(),
        ], [
            // Add any additional fields that should be updated here if needed
            'updated_at' => now(),
        ]);

        return $group->wasRecentlyCreated;
    }

    /**
     * @param  Model  $verifier
     * @param  int|null $verificationId
     *
     * @return bool
     */
    public function ungroupVerification(Model $verifier, ?int $verificationId = null)
    {
        // If specific verification ID is provided, use it
        if ($verificationId) {
            $verification = $this->findVerification($verifier)
                ->where('id', $verificationId)
                ->first();

            $where = [
                'verification_id' => $verification->id,
            ];
        } else {
            // For backward compatibility, get the latest accepted verification
            $verification = $this->findVerification($verifier)
                ->latest()
                ->first();

            $where = [
                'verification_id' => $verification->id,
                'verifier_id' => $verifier->getKey(),
                'verifier_type' => $verifier->getMorphClass(),
            ];
        }

        if (empty($verification)) {
            return false;
        }

        $result = $verification->groups()->where($where)->delete();

        return $result;
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
     * Get the latest verification between users
     *
     * @param  Model  $recipient
     *
     * @return \Multicaret\Acquaintances\Models\Verification|null
     */
    public function getLatestVerification(Model $recipient)
    {
        $verificationModelName = Interaction::getVerificationModelName();

        return $verificationModelName::where(function ($query) use ($recipient) {
            $query->where(function ($q) use ($recipient) {
                $q->where('sender_id', $this->getKey())
                    ->where('sender_type', $this->getMorphClass())
                    ->where('recipient_id', $recipient->getKey())
                    ->where('recipient_type', $recipient->getMorphClass());
            })->orWhere(function ($q) use ($recipient) {
                $q->where('sender_id', $recipient->getKey())
                    ->where('sender_type', $recipient->getMorphClass())
                    ->where('recipient_id', $this->getKey())
                    ->where('recipient_type', $this->getMorphClass());
            });
        })->orderBy('id', 'desc')->first();
    }

    /**
     * Get all verifications between users
     *
     * @param  Model  $recipient
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllVerificationsWith(Model $recipient)
    {
        return $this->findVerification($recipient)->get();
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
     * @param  string|null  $groupSlug
     * @param  int|null  $perPage  Number
     * @param  array|null  $fields
     * @param  string|null  $type
     *
     * @return \Illuminate\Database\Eloquent\Collection|Verification[]
     */
    public function getPendingVerifications(
        ?string $groupSlug = null,
        ?int $perPage = 0,
        ?array $fields = ['*'],
        ?string $type = null
    ) {
        return $this->getOrPaginateVerifications($this->findVerifications(Status::PENDING, $groupSlug, $type), $perPage, $fields);
    }

    /**
     * @param  string|null  $groupSlug
     * @param  int|null  $perPage  Number
     * @param  array|null  $fields
     * @param  string|null  $type
     *
     * @return \Illuminate\Database\Eloquent\Collection|Verification[]
     */
    public function getAcceptedVerifications(
        ?string $groupSlug = null,
        ?int $perPage = 0,
        ?array $fields = ['*'],
        ?string $type = null
    ) {
        return $this->getOrPaginateVerifications($this->findVerifications(Status::ACCEPTED, $groupSlug, $type), $perPage, $fields);
    }

    /**
     * @param  string|null  $groupSlug
     * @param  int  $perPage  Number
     * @param  array  $fields
     * @param  string|null  $type
     *
     * @return \Illuminate\Database\Eloquent\Collection|Verification[]
     */
    public function getDeniedVerifications(
        ?string $groupSlug = null,
        ?int $perPage = 0,
        ?array $fields = ['*'],
        ?string $type = null
    ) {
        return $this->getOrPaginateVerifications($this->findVerifications(Status::DENIED, $groupSlug, $type), $perPage, $fields);
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
    public function getVerifiersCount(?string $groupSlug = null, ?string $type = null)
    {
        $verifiersCount = $this->findVerifications(Status::ACCEPTED, $groupSlug, $type)->count();

        return $verifiersCount;
    }

    /**
     * @param  Model  $recipient
     * @param  string|null  $groupSlug
     *
     * @return bool
     */
    public function canVerify($recipient, $groupSlug = null)
    {
        // Check if there's a blocked verification between the users
        $verification = $this->getVerification($recipient);
        if ($verification && $verification->status === Status::BLOCKED) {
            return false;
        }

        // Check if the recipient has blocked this user in verifications
        $recipientVerification = $recipient->getVerification($this);
        if ($recipientVerification && $recipientVerification->status === Status::BLOCKED) {
            return false;
        }

        // Always allow verifications if not blocked - let the application layer handle any other restrictions
        return true;
    }

    /**
     * @param  Model  $recipient
     * @param  int|null  $verificationId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function findVerification(Model $recipient, ?int $verificationId = null)
    {
        $verificationModelName = Interaction::getVerificationModelName();

        $query = $verificationModelName::betweenModels($this, $recipient);

        if ($verificationId !== null) {
            $query->where('id', $verificationId);
        }
        return $query;
    }

    /**
     * @param string|null $status
     * @param string|null $groupSlug
     * @param string|null $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function findVerifications(?string $status = null, ?string $groupSlug = null, ?string $type = null)
    {
        $verificationModelName = Interaction::getVerificationModelName();
        $query = $verificationModelName::where(function ($query) use ($type) {
            switch ($type) {
                case null:
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
        });

        if (! is_null($groupSlug)) {
            $query->whereGroup($this, $groupSlug)
                ->orderByRaw("FIELD(status, '" . implode("','", Status::getOrderedStatuses()) . "')");
        }

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

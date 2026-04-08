<?php

namespace Multicaret\Acquaintances\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Multicaret\Acquaintances\Interaction;

/**
 * Queued job to clean up all acquaintance data for a given model.
 *
 * Useful when a user deletes their account to avoid blocking
 * the main request thread with mass deletions.
 */
class AcquaintancesCleanupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Model $model
    ) {}

    public function handle(): void
    {
        DB::transaction(function () {
            $this->cleanupInteractions();
            $this->cleanupFriendships();
            $this->cleanupVerifications();
        });

        Interaction::flushRequestCache();
    }

    protected function cleanupInteractions(): void
    {
        $interactionsTable = config('acquaintances.tables.interactions', 'interactions');
        $userIdFkColumnName = config('acquaintances.tables.interactions_user_id_fk_column_name', 'user_id');

        DB::table($interactionsTable)
            ->where($userIdFkColumnName, $this->model->getKey())
            ->delete();

        DB::table($interactionsTable)
            ->where('subject_id', $this->model->getKey())
            ->where('subject_type', $this->model->getMorphClass())
            ->delete();
    }

    protected function cleanupFriendships(): void
    {
        $friendshipsTable = config('acquaintances.tables.friendships', 'friendships');

        DB::table($friendshipsTable)
            ->where(function ($query) {
                $query->where('sender_id', $this->model->getKey())
                    ->where('sender_type', $this->model->getMorphClass());
            })
            ->orWhere(function ($query) {
                $query->where('recipient_id', $this->model->getKey())
                    ->where('recipient_type', $this->model->getMorphClass());
            })
            ->delete();
    }

    protected function cleanupVerifications(): void
    {
        $verificationsTable = config('acquaintances.tables.verifications', 'verifications');

        DB::table($verificationsTable)
            ->where(function ($query) {
                $query->where('sender_id', $this->model->getKey())
                    ->where('sender_type', $this->model->getMorphClass());
            })
            ->orWhere(function ($query) {
                $query->where('recipient_id', $this->model->getKey())
                    ->where('recipient_type', $this->model->getMorphClass());
            })
            ->delete();
    }
}

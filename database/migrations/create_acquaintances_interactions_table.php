<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Multicaret\Acquaintances\Interaction;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('acquaintances.tables.interactions', 'interactions'), function (Blueprint $table) {
            $table->id();

            $userModel = Interaction::getUserModelName();
            $userModel = (new $userModel);
            $userIdFkColumnName = config('acquaintances.tables.interactions_user_id_fk_column_name', 'user_id');

            $morphType = config('acquaintances.tables.interactions_morph_type', 'morphs');
            $userIdFkType = config('acquaintances.tables.interactions_user_id_fk_column_type');

            $table->{$userIdFkType}($userIdFkColumnName)->index();
            $table->{$morphType}('subject');
            $table->string('relation', 50)->default('follow')->comment('follow/like/subscribe/favorite/upvote/downvote');
            $table->double('relation_value')->nullable();
            $table->string('relation_type', 50)->nullable();
            $table->timestamps();

            $table->foreign($userIdFkColumnName)
                ->references($userModel->getKeyName())
                ->on($userModel->getTable())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unique(
                [$userIdFkColumnName, 'subject_id', 'subject_type', 'relation', 'relation_type'],
                'interactions_unique_relation'
            );

            $table->index(
                [$userIdFkColumnName, 'relation'],
                'interactions_user_relation_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('acquaintances.tables.interactions', 'interactions'));
    }
};

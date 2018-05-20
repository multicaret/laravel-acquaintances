<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAcquaintancesInteractionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('acquaintances.tables.interactions', 'interactions'), function (Blueprint $table) {
            $userForeignKey = config('follow.users_table_foreign_key', 'user_id');
            $table->unsignedInteger($userForeignKey);
            $table->morphs('followable');
            $table->string('relation')->default('follow')->comment('follow/like/subscribe/favorite/upvote/downvote');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign($userForeignKey)
                  ->references(config('acquaintances.users_table_primary_key', 'id'))
                  ->on(config('acquaintances.users_table_name', 'users'))
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(config('acquaintances.tables.interactions', 'interactions'), function ($table) {
            $table->dropForeign(config('acquaintances.tables.interactions', 'interactions') . '_user_id_foreign');
        });

        Schema::drop(config('acquaintances.tables.interactions', 'interactions'));
    }
}

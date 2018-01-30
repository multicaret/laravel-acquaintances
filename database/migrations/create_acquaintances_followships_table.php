<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAcquaintancesFollowshipsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('acquaintances.tables.followships', 'followships'), function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->morphs('followable');
            $table->string('relation')->default('follow')->comment('follow/like/subscribe/favorite/');
            $table->timestamp('created_at');

            $table->foreign('user_id')
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
        Schema::table(config('acquaintances.tables.followships', 'followships'), function ($table) {
            $table->dropForeign(config('acquaintances.tables.followships', 'followships') . '_user_id_foreign');
        });

        Schema::drop(config('acquaintances.tables.followships', 'followships'));
    }
}

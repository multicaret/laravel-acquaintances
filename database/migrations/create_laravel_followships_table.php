<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLaravelFollowshipsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('acquaintance.tables.followships', 'followships'), function (Blueprint $table) {
            $table->unsignedInteger('user_id');
//            $table->unsignedInteger('followable_id');
//            $table->string('followable_type')->index();
            $table->morphs('followable');
            $table->string('relation')->default('follow')->comment('follow/like/subscribe/favorite/');
            $table->timestamp('created_at');

            $table->foreign('user_id')
                  ->references(config('acquaintance.users_table_primary_key', 'id'))
                  ->on(config('acquaintance.users_table_name', 'users'))
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(config('acquaintance.tables.followships', 'followships'), function ($table) {
            $table->dropForeign(config('acquaintance.tables.followships', 'followships') . '_user_id_foreign');
        });

        Schema::drop(config('acquaintance.tables.followships', 'followships'));
    }
}

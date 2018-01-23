<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateFriendshipsGroupsTable
 */
class CreateLaravelFriendshipsGroupsTable extends Migration
{

    public function up()
    {

        Schema::create(config('acquaintance.tables.friendship_groups'), function (Blueprint $table) {

            $table->integer('friendship_id')->unsigned();
            $table->morphs('friend');
            $table->integer('group_id')->unsigned();

            $table->foreign('friendship_id')
                  ->references('id')
                  ->on(config('acquaintance.tables.friendships'))
                  ->onDelete('cascade');

            $table->unique(['friendship_id', 'friend_id', 'friend_type', 'group_id'], 'unique');

        });

    }

    public function down()
    {
        Schema::dropIfExists(config('acquaintance.tables.friendship_groups'));
    }

}
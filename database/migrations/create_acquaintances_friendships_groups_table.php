<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateFriendshipsGroupsTable
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('acquaintances.tables.friendship_groups'), function (Blueprint $table) {
            $morphType = config('acquaintances.tables.friendship_groups_morph_type', 'morphs');

            $table->id();
            $table->unsignedBigInteger('friendship_id')->unsigned();
            $table->{$morphType}('friend');
            $table->integer('group_id')->unsigned();

            $table->foreign('friendship_id')
                ->references('id')
                ->on(config('acquaintances.tables.friendships'))
                ->onDelete('cascade');

            $table->unique(['friendship_id', 'friend_id', 'friend_type', 'group_id'], 'friendship_groups_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('acquaintances.tables.friendship_groups'));
    }
};

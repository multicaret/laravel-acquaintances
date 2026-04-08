<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('acquaintances.tables.friendships'), function (Blueprint $table) {
            $morphType = config('acquaintances.tables.friendships_morph_type', 'morphs');

            $table->id();
            $table->{$morphType}('sender');
            $table->{$morphType}('recipient');
            $table->string('status')->default('pending')->comment('pending/accepted/denied/blocked/');
            $table->timestamps();

            $table->index(['sender_id', 'recipient_id'], 'friendships_sender_recipient_index');
            $table->index('status', 'friendships_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('acquaintances.tables.friendships'));
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateVerificationsGroupsTable
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('acquaintances.tables.verification_groups'), function (Blueprint $table) {
            $morphType = config('acquaintances.tables.verification_groups_morph_type', 'morphs');

            $table->id();
            $table->unsignedBigInteger('verification_id')->unsigned();
            $table->{$morphType}('verifier');
            $table->integer('group_id')->unsigned();

            $table->foreign('verification_id')
                ->references('id')
                ->on(config('acquaintances.tables.verifications'))
                ->onDelete('cascade');

            $table->unique(['verification_id', 'verifier_id', 'verifier_type', 'group_id'], 'verification_groups_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('acquaintances.tables.verification_groups'));
    }
};

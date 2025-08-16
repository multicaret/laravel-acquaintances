<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateVerificationsGroupsTable
 */
class CreateAcquaintancesVerificationsGroupsTable extends Migration
{

    public function up()
    {

        Schema::create(config('acquaintances.tables.verification_groups'), function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('verification_id')->unsigned();
            $table->morphs('verifier');
            $table->integer('group_id')->unsigned();

            $table->foreign('verification_id')
                ->references('id')
                ->on(config('acquaintances.tables.verifications'))
                ->onDelete('cascade');

            $table->unique(['verification_id', 'verifier_id', 'verifier_type', 'group_id'], 'unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('acquaintances.tables.verification_groups'));
    }
}

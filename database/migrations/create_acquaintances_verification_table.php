<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAcquaintancesVerificationTable extends Migration
{

    public function up()
    {

        Schema::create(config('acquaintances.tables.verifications'), function (Blueprint $table) {
            $table->id();
            $table->morphs('sender');
            $table->morphs('recipient');
            $table->string('message')->nullable()->comment('Verification message');
            $table->string('status')->default('pending')->comment('pending/accepted/denied/blocked/');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('acquaintances.tables.verifications'));
    }
}

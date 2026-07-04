<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_account_basic_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode')->nullable();
            $table->string('educationName')->nullable();
            $table->string('educationImage')->nullable();
            $table->string('languageskills')->nullable();
            $table->string('languageskillratio')->nullable();
            $table->string('typeoflearning')->nullable();
            $table->string('idproof')->nullable();
            $table->string('idproofnumber')->nullable();
            $table->string('photo')->nullable();
            $table->string('plan')->nullable();
            $table->string('registrationId')->nullable();
            $table->string('verficationstatus')->nullable();
            $table->string('mailStatus')->nullable();
            $table->string('notes')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_account_basic_infos');
    }
};

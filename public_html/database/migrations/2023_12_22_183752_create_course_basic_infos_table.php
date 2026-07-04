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
        Schema::create('course_basic_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('course_category_id');
            $table->foreign('course_category_id')->references('id')->on('course_categories');
            $table->string('course_id');
            $table->string('course_name'); 
            $table->string('duration_type'); 
            $table->string('duration'); 
            $table->string('credits'); 
            $table->integer('course_price'); 
            $table->integer('course_size'); 
            $table->string('course_description')->nullable();
            $table->string('course_image')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *id 
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_basic_infos');
    }
};

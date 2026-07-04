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
        Schema::create('course_lesson_basic_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('course_basic_info_id');
            $table->foreign('course_basic_info_id')->references('id')->on('course_basic_infos');
            $table->bigInteger('lesson_id');
            $table->string('lesson_name');  
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations. 
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_lesson_basic_infos');
    }
};

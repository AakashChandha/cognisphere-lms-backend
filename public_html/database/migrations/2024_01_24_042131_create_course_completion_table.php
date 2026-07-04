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
        Schema::create('course_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by'); 
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('course_id');
            $table->foreign('course_id')->references('id')->on('course_basic_infos');
            $table->unsignedBigInteger('lesson_id');
            $table->foreign('lesson_id')->references('id')->on('course_lesson_basic_infos');
            $table->integer('count_of_topics');
            $table->integer('count_of_topics_completed');
            $table->integer('coureses_topic_percentage');
            $table->integer('coureses_topic_completed_percentage');
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
        Schema::dropIfExists('course_completions');
    }
};

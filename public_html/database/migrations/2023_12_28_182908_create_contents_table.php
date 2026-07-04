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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by'); 
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('course_id');
            $table->foreign('course_id')->references('id')->on('course_basic_infos');
            $table->unsignedBigInteger('course_lesson_id');
            $table->foreign('course_lesson_id')->references('id')->on('course_lesson_basic_infos');
            $table->string('content_id')->uniqid();
            $table->string('content_name');
            $table->integer('content_type');
            $table->longText('content_value');
            $table->string('file_path')->nullable();
            $table->integer('content_order');
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
        Schema::dropIfExists('contents');
    }
};

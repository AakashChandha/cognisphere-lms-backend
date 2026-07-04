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
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('quiz_id');
            $table->foreign('quiz_id')->references('id')->on('assessment_details');
            $table->string('question_name', 2000);
            $table->string('question_type')->nullable();
            $table->string('option1', 2000)->nullable();
            $table->string('option2', 2000)->nullable();
            $table->string('option3', 2000)->nullable();
            $table->string('option4', 2000)->nullable();
            $table->string('answer', 2000)->nullable();
            $table->string('answer_explanation', 2000)->nullable();
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
        Schema::dropIfExists('assessment_questions');
    }
};

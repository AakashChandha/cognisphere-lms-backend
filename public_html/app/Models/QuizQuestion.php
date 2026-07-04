<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;
    protected $table = 'quiz_questions';
    protected $fillable = [
        'quiz_id',
        'question_name',
        'option1',
        'option2',
        'option3',
        'option4',
        'answer',
        'answer_explanation',
        'status',
        'created_by'
    ];
}

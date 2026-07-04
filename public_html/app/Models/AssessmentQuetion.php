<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentQuetion extends Model
{
    use HasFactory;

    protected $table = 'assessment_questions';

    protected $fillable = [
        'quiz_id',
        'question_id',
        'question_name',
        'question_type',
        'option1',
        'option2',
        'option3',
        'option4',
        'answer',
        'answer_explanation',
        'status',
        'created_by',
        'section',
        'type',
        'chapter'
    ];
}

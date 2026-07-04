<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveRecallQuestion extends Model
{
    use HasFactory;

    protected $table = 'activerecall_questions';

    protected $fillable = [
        'quiz_id',
        'question_id',
        'question_name',
        'question_type',
        'option1',
        'option2',
        'option3',
        'option4',
        'section',
        'type',
        'chapter',
        'answer',
        'answer_explanation',
        'status',
        'created_by'
    ];
}

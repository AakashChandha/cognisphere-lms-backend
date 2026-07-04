<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAnswer extends Model
{
    use HasFactory;

    protected $table = 'assessment_answers';

    protected $fillable = [
        'user_id',
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

    
    public function assessmentDetails()
    {
        return $this->belongsTo(AssessmentDetails::class, 'quiz_id','id');
    }  
    
    public function userBasicInfo()
    {
        return $this->belongsTo(UserAccountBasicInfo::class, 'user_id','id');
    }    
}

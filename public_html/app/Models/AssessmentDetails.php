<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentDetails extends Model
{
    use HasFactory;

    protected $table = 'assessment_details';

    protected $fillable = [
        'course_id',
        'question_id',
        'status',
        'created_by'
    ];

    public function courseBasicInfo()
    {
        return $this->belongsTo(CourseBasicInfo::class, 'course_id','id');
    }    
    public function courseLessonBasicInfo()
    {
        return $this->belongsTo(CourseLessonBasicInfo::class, 'lesson_id','id');
    }
    public function assessmentQuestions()
    {
        return $this->hasMany(AssessmentQuestion::class, 'quiz_id','id');
    }
    public function assessmentAnswers()
    {
        return $this->hasMany(AssessmentAnswer::class, 'quiz_id','id');
    }    
    public function userBasicInfo()
    {
        return $this->belongsTo(UserAccountBasicInfo::class, 'user_id','id');
    }    
    public function assessmentSetting()
    {
        return $this->belongsTo(AssessmentSetting::class, 'lesson_id','lesson_id');
    }

}

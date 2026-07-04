<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentResubmit extends Model
{
    use HasFactory;

    protected $table = 'assessment_resubmit';

    protected $fillable = [
        'course_id',
        'course_category_id',
        'lesson_id',
        'user_id',
        'quiz_id',
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
    public function userBasicInfo()
    {
        return $this->belongsTo(UserAccountBasicInfo::class, 'user_id','user_id');
    }    
    public function assessmentQuestions()
    {
        return $this->hasMany(AssessmentQuestion::class, 'quiz_id','id');
    }
}

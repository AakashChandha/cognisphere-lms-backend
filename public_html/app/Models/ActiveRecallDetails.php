<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveRecallDetails extends Model
{
    use HasFactory;

    protected $table = 'activerecall_details';

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
    public function activerecallQuestions()
    {
        return $this->hasMany(ActiveRecallQuestion::class, 'quiz_id','id');
    }
}

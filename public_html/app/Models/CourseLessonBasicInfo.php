<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseLessonBasicInfo extends Model
{
    use HasFactory;
    
    protected   $table = "course_lesson_basic_infos";

    public function CourseBasicInfo()
    {
        return $this->belongsTo(CourseBasicInfo::class );
    }

    public function content()
    {
        return $this->hasMany(Content::class, 'course_lesson_id');
    }
    
    public function quizDetails()
    {
        return $this->belongsTo(QuizDetails::class ,'lesson_id');
    }
}

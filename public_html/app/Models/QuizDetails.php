<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizDetails extends Model
{
    use HasFactory;
    protected $table = 'quiz_details';
    protected $fillable = [
        'course_id',
        'lesson_id',
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
    public function quizQuestions()
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id','id');
    }
    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id','id');
    }
}

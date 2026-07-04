<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCompleted extends Model
{
    use HasFactory;
    protected $table = 'course_completions';
    protected $fillable = [
        'user_id',
        'course_id',
        'Lesson_id',
        'coureses_topic_percentage',
        'coureses_topic_completed_percentage',
        'count_of_topics',
        'count_of_topics_completed',
        'created_by',
        'status'
    ];
}

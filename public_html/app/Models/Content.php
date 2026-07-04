<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;
    
    protected $table = 'contents';

    protected $fillable = [
        'course_id',
        'course_lesson_id',
        'content_id',
        'content_name',
        'content_type',
        'content_value',
        'file_path',
        'content_order',
        'created_by',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courses()
    {
        return $this->belongsTo(CourseBasicInfo::class, 'course_id');
    }

    public function courseLessonInfo()
    {
        return $this->belongsTo(CourseLessonBasicInfo::class ,'course_lesson_id');
    }
}

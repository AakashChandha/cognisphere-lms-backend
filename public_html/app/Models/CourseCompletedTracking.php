<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCompletedTracking extends Model
{
    use HasFactory;

    protected $table = 'course_completed_trackings';
    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'content_id',
        'progress',
        'status',
        'created_at'
    ];
}

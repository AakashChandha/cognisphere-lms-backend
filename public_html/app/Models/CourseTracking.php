<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseTracking extends Model
{
    use HasFactory;

    protected $table = 'courses_track';

    protected $fillable = [
        'track_id',
        'user_id',
        'course_id',
        'lesson_id',
        'content_id',
        'status',
    ];
}

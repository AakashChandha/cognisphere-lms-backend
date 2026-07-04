<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentSetting extends Model
{
    use HasFactory;

    protected $table = 'assessment_setting';
 
    protected $fillable = [
        'course_id',
        'course_category_id',
        'lesson_id',
        'section1_question',
        'section1_mark',
        'section1_total',
        'section2_question',
        'section2_mark',
        'section2_total',
        'status',
        'created_by'
    ];

    public function courseBasicInfo()
    {
        return $this->belongsTo(CourseBasicInfo::class, 'course_id','id');
    }
    public function courseCategory()
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id','id');
    }
    public function courseLessonBasicInfo()
    {
        return $this->belongsTo(CourseLessonBasicInfo::class, 'lesson_id','id');
    }
}

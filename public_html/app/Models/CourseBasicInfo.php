<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseBasicInfo extends Model
{
    use HasFactory; 
    protected   $table = "course_basic_infos";

    public function courseCategory()
    {
        return $this->belongsTo(CourseCategory::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courseLessonBasicInfo()
    {
        return $this->hasMany(CourseLessonBasicInfo::class);
    }

    public function content()
    {
        return $this->hasMany(Content::class);
    }

    public function calculateCompletionPercentage($userId)
    {
        $courseId = $this->id;
        $models = self::find($courseId);

        $completed = 0;
        $pending = 0;
        $contents = Content::where('course_id',$courseId)->get();
        foreach ($contents as $content) {
            $completedTracking = CourseCompletedTracking::where('user_id',$userId)->where('course_id',$courseId)->where('lesson_id',$content->course_lesson_id)->where('content_id',$content->id)->first();
            if(isset($completedTracking) && $completedTracking->progress == 1) {
                $completed = $completed+1;
            } else {
                $pending = $pending+1;
            }
        }
        $completed_percentage = 0;//round($completed,2);
        if($completed > 0) {
            $completed_percentage = round(($completed/count($contents))*100,0);
        }
        $models->completed_percentage = $completed_percentage; 
        return $models;
    }

    public function courseCategoryInfo()
    {
        return $this->hasOneThrough(
            CourseCategory::class,
            CourseBasicInfo::class,
            'id', // Foreign key on CourseBasicInfo table (primary key of CourseBasicInfo)
            'id', // Foreign key on CourseCategory table (primary key of CourseCategory)
            'course_id', // Foreign key on LearnerCertificate table (pointing to CourseBasicInfo)
            'category_id' // Foreign key on CourseBasicInfo table (pointing to CourseCategory)
        );
    }
}

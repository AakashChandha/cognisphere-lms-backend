<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoContent extends Model
{
    use HasFactory;

    protected $table = 'video_contents';

    protected $fillable = [
        'title',
        'course_id',
        'video_url',
        'embeded_url',
        'status',
        'created_by'
    ];

    // ALTER TABLE `video_contents` ADD `embeded_url` TEXT NULL AFTER `video_url`;


    public function courseBasicInfo()
    {
        return $this->belongsTo(CourseBasicInfo::class, 'course_id', 'id');
    }
}

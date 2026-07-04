<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AudioContent extends Model
{
    use HasFactory;

    protected $table = 'audio_contents';

    protected $fillable = [
        'title',
        'course_id',
        'video_url',
        'status',
        'created_by'
    ];

    public function courseBasicInfo()
    {
        return $this->belongsTo(CourseBasicInfo::class, 'course_id','id');
    }
}

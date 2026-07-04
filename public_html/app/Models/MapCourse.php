<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapCourse extends Model
{
    use HasFactory;
    protected $table = 'course_batches';
    protected $fillable = [
        'course_id',
        'instructor_id',
        'batch_id',
        'status',
        'created_by',
    ];

    public function courseBasicInfo()
    {
        return $this->belongsTo(CourseBasicInfo::class, 'course_id','id');
    }
    
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id','id');
    }
    
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id','id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by','id');
    }
    
    public function sessionInfo()
    {
        return $this->belongsTo(Session::class, 'session_id','id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entrollment extends Model
{
    use HasFactory;
    protected $table = "enrollments";
    
    protected $fillable = [
        'user_id',
        'course_id',
        'batch_id',
        'session_id',
        'course_type',
        'user_group_id',
        'enrolled',
        'attendance_status',
        'created_by',
        'status',
        'code'
    ];

    public function courseBasicInfo()
    {
        return $this->belongsTo(CourseBasicInfo::class, 'course_id','id');
    }
    
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id','id');
    }

    public function userGroup()
    {
        return $this->belongsTo(UserGroup::class, 'user_group_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function sessionInfo()
    {
        return $this->belongsTo(Session::class, 'session_id','id');
    }
}

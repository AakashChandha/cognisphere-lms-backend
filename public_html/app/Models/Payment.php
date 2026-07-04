<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }
    public function courseBasicInfo()
    {
        return $this->belongsTo(CourseBasicInfo::class, 'course_id','id');
    }
    
    public function enrollmentInfo()
    {
        /*
        return $this->hasOne(Entrollment::class, 'course_id', 'course_id')
                    ->where(function ($query) {
                        $query->where('user_id', $this->user_id);
                    });
        */
        /*
        return $this->hasOne(Entrollment::class, 'course_id', 'course_id')
        ->where(function ($query) {
            $query->where('user_id', $this->user_id);
        });
        */
        return $this->hasOne(Entrollment::class, 'course_id','course_id')
        ->where('user_id','user_id'); 
    }
}

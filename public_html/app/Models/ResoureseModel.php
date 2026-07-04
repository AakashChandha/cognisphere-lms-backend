<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResoureseModel extends Model
{
    use HasFactory;

    protected $table = 'resource_contents';

    protected $fillable = [
        'course_id',
        'url',
        'status',
        'created_by'
    ];
    
    public function courseBasicInfo()
    {
        return $this->belongsTo(CourseBasicInfo::class, 'course_id','id');
    }
    
}

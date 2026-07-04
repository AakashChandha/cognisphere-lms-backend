<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;
    protected $table = "batches";
    protected $fillable = [
        'batch_type',
        'batch_name',
        'batch_size',
        'batch_category',
        'batch_course',
        'batch_company',
        'created_by',
        'status',
    ];

    
    public function batchCategory()
    {
        return $this->belongsTo(CourseCategory::class ,'batch_category');
    }
    public function batchCourse()
    {
        return $this->belongsTo(CourseBasicInfo::class ,'batch_course');
    }
    public function batchCompany()
    {
        return $this->belongsTo(UserGroup::class ,'batch_company');
    }

    

    public function Session()
    {
        return $this->hasMany(Session::class);
    }

    
}

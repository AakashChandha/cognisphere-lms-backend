<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearnerCertificate extends Model
{
    use HasFactory;

    protected $table = 'learner_certificates';

    protected $fillable = [
        'user_id',
        'course_id',
        'certificate_id',
        'status',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    } 

    public function courseBasicInfo()
    {
        return $this->belongsTo(CourseBasicInfo::class, 'course_id','id');
    }
    
    public function certificate()
    {
        return $this->belongsTo(CertificateDetails::class, 'id','certificate_id');
    }
    
    public function courseCategoryInfo()
    {
        return $this->hasOneThrough(
            CourseCategory::class,
            CourseBasicInfo::class,
            'id', // Foreign key on CourseBasicInfo table (primary key of CourseBasicInfo)
            'id', // Foreign key on CourseCategory table (primary key of CourseCategory)
            'course_id', // Foreign key on LearnerCertificate table (pointing to CourseBasicInfo)
            'course_category_id' // Foreign key on CourseBasicInfo table (pointing to CourseCategory)
        );
    }

}

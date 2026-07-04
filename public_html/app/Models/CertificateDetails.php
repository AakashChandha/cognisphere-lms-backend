<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateDetails extends Model
{
    use HasFactory;

    protected $table = 'certificate_details';

    protected $fillable = [
        'certificate_id',
        'grade',
        'course_start_date',
        'course_end_date',
        'certificate_validity',
        'status',
        'created_by',
    ];
}

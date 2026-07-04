<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = "attendance";

    protected $fillable = [
        'course_id',
        'learner_id',
        'session',
        'date',
        'attendance',
        'created_by',
        'status',
    ];
}

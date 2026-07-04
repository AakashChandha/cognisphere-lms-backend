<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Learnerfeessummary extends Model
{
    use HasFactory;
    protected $table = 'learner_fees_summaries';

    protected $fillable = [
        'user_id',
        'course_id',
        'actual_fee',
        'discount',
        'paid_fee',
        'balance_fee',
        'last_paid_fee',
        'created_by',
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
}

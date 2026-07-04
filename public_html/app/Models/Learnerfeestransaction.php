<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Learnerfeestransaction extends Model
{
    use HasFactory;
    protected $table = 'learner_fees_transactions';
    protected $fillable = [
        'user_id',
        'course_id',
        'mode_of_payment',
        'paid_amount',
        'date_of_payment',
        'transaction_id',
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

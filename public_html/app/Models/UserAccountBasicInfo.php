<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAccountBasicInfo extends Model
{
    use HasFactory;
    protected $table = 'user_account_basic_infos';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'address',
        'state',
        'country',
        'city',
        'pincode',
        'educationName',
        'educationImage',
        'languageskills',
        'languageskillratio',
        'typeoflearning',
        'idproof',
        'idproofnumber',
        'photo',
        'plan',
        'registrationId',
        'verficationstatus',
        'mailStatus',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }
}

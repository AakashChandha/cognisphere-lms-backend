<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
        'phone_code',
        'phone_number',
        'user_group_id',
        'status',
        'session_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function courseBasicInfo()
    {
        return $this->hasMany(CourseBasicInfo::class);
    }

    
    public function userGroup()
    {
        return $this->belongsTo(UserGroup::class, 'user_group_id', 'id');
    }
    
    public function userAccountBasicInfo()
    {
        return $this->hasOne(UserAccountBasicInfo::class, 'user_id', 'id');
    }
}

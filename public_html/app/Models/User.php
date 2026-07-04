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
     * Default attribute values for columns that may be NOT NULL in production DB.
     */
    protected $attributes = [
        'phone_code' => 0,
        'phone_number' => null,
        'role' => '0',
        'status' => 1,
    ];

    /**
     * Build a complete users row for API self-registration.
     * Matches production schema in DB dump (phone_code is int, phone_number nullable).
     */
    public static function attributesForRegistration(array $input): array
    {
        $userGroupId = $input['user_group_id']
            ?? UserGroup::where('name', 'Learner')->value('id')
            ?? 2;

        $phoneCode = array_key_exists('phone_code', $input) && $input['phone_code'] !== ''
            ? (int) $input['phone_code']
            : 0;

        $phoneNumber = array_key_exists('phone_number', $input) && $input['phone_number'] !== ''
            ? (string) $input['phone_number']
            : null;

        return [
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'user_group_id' => $userGroupId,
            'phone_code' => $phoneCode,
            'phone_number' => $phoneNumber,
            'role' => $input['role'] ?? null,
            'status' => $input['status'] ?? 1,
            'session_id' => null,
        ];
    }

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
        'phone_code' => 'integer',
        'status' => 'boolean',
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

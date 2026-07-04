<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    use HasFactory;
    protected $table = 'user_groups';
    protected $fillable = [
        'name',
        'role',
        'username',
        'organization',
    ];
    public function userAccounts()
    {
        return $this->hasMany(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGroupPermission extends Model
{
    use HasFactory;
    
    protected $table = 'user_group_permissions';
    
    protected $fillable = [
        'user_group_id',
        'create_user',
        'edit_user',
        'view_user',
        'delete_user',
    ];
    
}

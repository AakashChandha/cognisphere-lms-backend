<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserGroup;
use App\Models\UserGroupPermission;


class UserGroupSeeder extends Seeder
{
    public function run()
    {
        // Insert sample data into the usergroup table
        UserGroup::create([
            'name' => 'Admin',
            'role' => 'admin',
            'username' => 'admin_user',
            'organization' => 'Coginiphere',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        UserGroup::create([
            'name' => 'Learner',
            'role' => 'learner',
            'username' => 'learner_user',
            'organization' => 'Coginiphere',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        UserGroup::create([
            'name' => 'Instructor',
            'role' => 'instructor',
            'username' => 'instructor_user',
            'organization' => 'Coginiphere',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert data into the user_group_permission table
        UserGroupPermission::create([
            'user_group_id' => 1,
            'create_user' => 1,
            'edit_user' => 1,
            'view_user' => 1,
            'delete_user' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        UserGroupPermission::create([
            'user_group_id' => 2,
            'create_user' => 0,
            'edit_user' => 0,
            'view_user' => 1,
            'delete_user' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        

        UserGroupPermission::create([
            'user_group_id' => 3,
            'create_user' => 0,
            'edit_user' => 0,
            'view_user' => 1,
            'delete_user' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

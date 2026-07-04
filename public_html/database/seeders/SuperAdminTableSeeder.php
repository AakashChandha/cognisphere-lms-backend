<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;


class SuperAdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'user_group_id' => 1,
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'role' => 1,
            'password' => bcrypt('password'),
        ]);
    }
}

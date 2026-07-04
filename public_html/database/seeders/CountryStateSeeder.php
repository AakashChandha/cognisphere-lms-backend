<?php
 
namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\State;
 
 
class CountryStateSeeder extends Seeder
{
    public function run()
    {
        // Insert sample data into the usergroup table
        Country::create([
            'id' => 1,
            'shortname' => 'IND',
            'name' => 'India',
            'phonecode' => '91',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
 
        State::create([
            'id' => 1,
            'country_id' => 1,
            'name' => 'Tamil Nadu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
 
        State::create([
            'id' => 2,
            'country_id' => 1,
            'name' => 'Kerala',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
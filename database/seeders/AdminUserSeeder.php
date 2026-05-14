<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Government;
use App\Models\Area;
use App\Models\WorkDay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // create work time
        WorkDay::firstOrCreate(['value' => 'all days']);

        // Create or get a default government
        $government = Government::firstOrCreate(
            ['name' => 'Cairo'],
            ['name' => 'Giza']
        );

        // Create or get a default area for the government
        $area = Area::firstOrCreate(
            [
                'name' => 'Haram St',
                'government_id' => $government->id
            ],
            [
                'name' => 'King feisal',
                'government_id' => $government->id
            ]
        );

        // Create admin user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@swswapp.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@swswapp.com',
                'phone' => '0000000000',
                'government_id' => $government->id,
                'area_id' => $area->id,
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make('123456789'),
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\DeliveryUser;
use App\Models\Government;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DeliveryUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $government = Government::firstOrCreate(
            [
                'name_en' => 'Cairo',
                'name_ar' => 'القاهرة'
            ],
            [
                'name_en' => 'Giza',
                'name_ar' => 'الجيزة'
            ]
        );

        // Create or get a default area for the government
        $area = Area::firstOrCreate(
            [
                'name_en' => 'Haram St',
                'name_ar' => 'شارع الهرم',
                'government_id' => $government->id
            ],
            [
                'name_en' => 'King feisal',
                'name_ar' => 'شارع الملك فيصل',
                'government_id' => $government->id
            ]
        );
        DeliveryUser::create([           
                'level_id' => '1',
                'shift_id' => '1',
                'name' => 'delivery User',
                'email' => 'delivery@gmail.com',
                'phone' => '0000000000',
                'type' => 'company',
                'birthdate' => '1998-3-3',
                'government_id' => $government->id,
                'area_id' => $area->id,
                'status' => 'pending',
                'password' => Hash::make('123456789'),
        ]);
    }
}

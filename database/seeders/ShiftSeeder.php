<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Shift::Create(
            [
                'name_en' => 'Morning Shift',
                'name_ar' => 'شيفت صباحي',
                'from_time' => '08:00:00',
                'to_time' => '16:00:00'
            ],

            [
                'name_en' => 'Evening Shift',
                'name_ar' => 'شيفت مسائي',
                'from_time' => '16:00:00',
                'to_time' => '00:00:00',
            ]
        );
    }
}

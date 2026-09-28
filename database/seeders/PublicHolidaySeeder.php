<?php

namespace Database\Seeders;

use App\Models\PublicHoliday;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PublicHolidaySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([now()->year, now()->addYear()->year] as $year) {
            PublicHoliday::updateOrCreate(['holiday_date' => "{$year}-01-01"], ['name' => 'Tahun Baru Masehi']);
            PublicHoliday::updateOrCreate(['holiday_date' => "{$year}-08-17"], ['name' => 'Hari Kemerdekaan Republik Indonesia']);
        }
    }
}

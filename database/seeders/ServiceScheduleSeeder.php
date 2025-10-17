<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use App\Models\ServiceSchedule;

class ServiceScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = Service::all();

        foreach ($services as $service) {
            $schedule = [
                ['day_of_week' => 1, 'start_time' => '10:00', 'end_time' => '20:00'], // Пн
                ['day_of_week' => 2, 'start_time' => '10:00', 'end_time' => '20:00'], // Вт
                ['day_of_week' => 3, 'start_time' => '10:00', 'end_time' => '20:00'], // Ср
                ['day_of_week' => 4, 'start_time' => '10:00', 'end_time' => '20:00'], // Чт
                ['day_of_week' => 5, 'start_time' => '10:00', 'end_time' => '20:00'], // Пт
                ['day_of_week' => 6, 'start_time' => '10:00', 'end_time' => '20:00'], // Сб
            ];

            foreach ($schedule as $daySchedule) {
                ServiceSchedule::firstOrCreate([
                    'service_id'  => $service->id,
                    'day_of_week' => $daySchedule['day_of_week'],
                ], [
                    'start_time' => $daySchedule['start_time'],
                    'end_time'   => $daySchedule['end_time'],
                    'is_active'  => true,
                ]);
            }
        }
    }
}

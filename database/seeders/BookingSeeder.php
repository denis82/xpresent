<?php

namespace Database\Seeders;


use Carbon\Carbon;
use App\Models\Service;
use App\Models\Booking;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name'             => 'Поездка на квадроцикле 30 минут',
                'description'      => 'Поездка на квадроцикле по болоту',
                'duration_minutes' => 30,
                'is_active'        => true,
            ],
            [
                'name'             => 'Поездка на квадроцикле 60 минут',
                'description'      => 'Продолжительная поездка на квадроцикле по болоту',
                'duration_minutes' => 60,
                'is_active'        => true,
            ],
            [
                'name'             => 'Тур на эндуро 60 минут',
                'description'      => 'Тур на мотоцикле эндуро по пересеченной местности',
                'duration_minutes' => 60,
                'is_active'        => true,
            ],
            [
                'name'             => 'Тур на эндуро 120 минут',
                'description'      => 'Продолжительный тур на эндуро по пересеченной местности',
                'duration_minutes' => 120,
                'is_active'        => true,
            ],
        ];

        $createdServices = [];
        foreach ($services as $serviceData) {
            $service = Service::firstOrCreate(
                ['name' => $serviceData['name']],
                $serviceData
            );
            $createdServices[$serviceData['name']] = $service;
        }

        $bookings = [
            //  30 мин
            [
                'service'        => 'Поездка на квадроцикле 30 минут',
                'date'           => '2025-10-16',
                'start_time'     => '13:00',
                'customer_name'  => 'Иван Петров',
                'customer_email' => 'ivan@example.com',
                'customer_phone' => '+79161234567',
            ],
            [
                'service'        => 'Поездка на квадроцикле 30 минут',
                'date'           => '2025-10-16',
                'start_time'     => '16:00',
                'customer_name'  => 'Мария Сидорова',
                'customer_email' => 'maria@example.com',
                'customer_phone' => '+79167654321',
            ],
            [
                'service'        => 'Поездка на квадроцикле 30 минут',
                'date'           => '2025-10-17',
                'start_time'     => '10:00',
                'customer_name'  => 'Алексей Козлов',
                'customer_email' => 'alex@example.com',
                'customer_phone' => '+79169998877',
            ],
            [
                'service'        => 'Поездка на квадроцикле 30 минут',
                'date'           => '2025-10-17',
                'start_time'     => '11:00',
                'customer_name'  => 'Ольга Новикова',
                'customer_email' => 'olga@example.com',
                'customer_phone' => '+79165554433',
            ],
            [
                'service'        => 'Поездка на квадроцикле 30 минут',
                'date'           => '2025-10-17',
                'start_time'     => '13:00',
                'customer_name'  => 'Дмитрий Волков',
                'customer_email' => 'dmitry@example.com',
                'customer_phone' => '+79162223344',
            ],
            [
                'service'        => 'Поездка на квадроцикле 30 минут',
                'date'           => '2025-10-17',
                'start_time'     => '18:00',
                'customer_name'  => 'Екатерина Морозова',
                'customer_email' => 'ekaterina@example.com',
                'customer_phone' => '+79163332211',
            ],

            // 60 мин
            [
                'service'        => 'Поездка на квадроцикле 60 минут',
                'date'           => '2025-10-16',
                'start_time'     => '10:00',
                'customer_name'  => 'Сергей Иванов',
                'customer_email' => 'sergey@example.com',
                'customer_phone' => '+79164445566',
            ],

            // 60 мин
            [
                'service'        => 'Тур на эндуро 60 минут',
                'date'           => '2025-10-16',
                'start_time'     => '10:00',
                'customer_name'  => 'Андрей Смирнов',
                'customer_email' => 'andrey@example.com',
                'customer_phone' => '+79167778899',
            ],
            [
                'service'        => 'Тур на эндуро 60 минут',
                'date'           => '2025-10-16',
                'start_time'     => '11:30',
                'customer_name'  => 'Наталья Ковалева',
                'customer_email' => 'natalia@example.com',
                'customer_phone' => '+79168889900',
            ],
            [
                'service'        => 'Тур на эндуро 60 минут',
                'date'           => '2025-10-16',
                'start_time'     => '18:30',
                'customer_name'  => 'Павел Орлов',
                'customer_email' => 'pavel@example.com',
                'customer_phone' => '+79169990011',
            ],

            // 120 мин
            [
                'service'        => 'Тур на эндуро 120 минут',
                'date'           => '2025-10-17',
                'start_time'     => '14:00',
                'customer_name'  => 'Виктор Жуков',
                'customer_email' => 'viktor@example.com',
                'customer_phone' => '+79161112233',
            ],
        ];

        foreach ($bookings as $bookingData) {
            $service = $createdServices[$bookingData['service']];

            // + 30 мин на переодевание например

            $startTime = Carbon::parse($bookingData['start_time']);
            $endTime = $startTime->copy()->addMinutes($service->duration_minutes + 30);

            Booking::create([
                'service_id'             => $service->id,
                'customer_name'          => $bookingData['customer_name'],
                'customer_email'         => $bookingData['customer_email'],
                'customer_phone'         => $bookingData['customer_phone'],
                'booking_date'           => $bookingData['date'],
                'start_time'             => $bookingData['start_time'],
                'end_time'               => $endTime->format('H:i'),
                'total_duration_minutes' => $service->duration_minutes + 30,
                'status'                 => 'confirmed',
            ]);
        }
    }
}

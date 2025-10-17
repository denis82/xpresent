<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Service;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class BookingService
{
    private const BUFFER_TIME = 30;        // буфер между бронированиями

    public function getAvailableSlots($serviceId, $date)
    {

        if (!$this->isDateAvailable($date)) {
            return [];
        }

        $service = Service::findOrFail($serviceId);
        $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;

        $schedule = $service->activeSchedules()
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$schedule) {
            return [];
        }

        $bookedSlots = $this->getBookedSlots($serviceId, $date);

        $totalDuration = $service->duration_minutes + self::BUFFER_TIME;

        return $this->generateSlots($schedule, $totalDuration, $bookedSlots);
    }

    /**
     * Проверить доступность даты
     *
     * @param string $date
     * @return boolean
     */
    private function isDateAvailable(string $date): bool
    {
        $carbonDate = Carbon::parse($date);

        // Проверка воскресенья
        if ($carbonDate->isSunday()) {
            return false;
        }
        // Проверка что дата не в прошлом
        if ($carbonDate->isPast() && !$carbonDate->isToday()) {
            return false;
        }
        return true;
    }

    /**
     * Получить все занятые слоты
     *
     * @param [type] $serviceId
     * @param [type] $date
     * @return array
     */
    private function getBookedSlots($serviceId, $date): array
    {
        return Booking::where('service_id', $serviceId)
            ->where('booking_date', $date)
            ->where('status', 'confirmed')
            ->get(['start_time', 'end_time'])
            ->map(function ($booking) {
                return [
                    'start' => Carbon::parse($booking->start_time),
                    'end' => Carbon::parse($booking->end_time)
                ];
            })
            ->toArray();
    }

    /**
     * Генерация доступных слотов
     *
     * @param [type] $schedule
     * @param [type] $totalDuration
     * @param array $bookedSlots
     * @return array
     */
    private function generateSlots($schedule, $totalDuration, array $bookedSlots): array
    {
        $slots = [];
        $currentTime = Carbon::parse($schedule->start_time);
        $endTime = Carbon::parse($schedule->end_time);

        while ($currentTime->copy()->addMinutes($totalDuration) <= $endTime) {
            $slotEnd = $currentTime->copy()->addMinutes($totalDuration);

            if (!$this->hasTimeOverlap($currentTime, $slotEnd, $bookedSlots)) {
                $slots[] = [
                    'start_time' => $currentTime->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'display' => $currentTime->format('H:i') . ' - ' . $slotEnd->format('H:i')
                ];
            }

            $currentTime->addMinutes($totalDuration);
        }

        return $slots;
    }

    /**
     * Проверить пересечение времени с занятыми слотами
     *
     * @param Carbon $slotStart
     * @param Carbon $slotEnd
     * @param array $bookedSlots
     * @return boolean
     */
    private function hasTimeOverlap(Carbon $slotStart, Carbon $slotEnd, array $bookedSlots): bool
    {
        foreach ($bookedSlots as $bookedSlot) {
            if ($this->isTimeOverlapping($slotStart, $slotEnd, $bookedSlot['start'], $bookedSlot['end'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверить пересечение двух временных интервалов
     *
     * @param [type] $startSlot
     * @param [type] $endSlot
     * @param [type] $startBooked
     * @param [type] $endBooked
     * @return boolean
     */
    private function isTimeOverlapping($startSlot, $endSlot, $startBooked, $endBooked): bool
    {
        return $startSlot < $endBooked && $endSlot > $startBooked;
    }

    /**
     * Создать бронь
     *
     * @param array $bookingData
     * @return void
     */
    public function createBooking(array $bookingData)
    {
        $this->validateBooking($bookingData);

        $service = Service::where('id', $bookingData['service_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $endTime = Carbon::parse($bookingData['start_time'])
            ->addMinutes($service->duration_minutes + 30)
            ->format('H:i');

        $isAvailable = !Booking::where('service_id', $bookingData['service_id'])
            ->where('booking_date', $bookingData['booking_date'])
            ->where('status', 'confirmed')
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $bookingData['start_time'])
            ->exists();

        if (!$isAvailable) {
            throw new \Exception('Выбранное время уже занято');
        }

        $booking = Booking::create([
            'service_id'             => $bookingData['service_id'],
            'customer_name'          => $bookingData['customer_name'],
            'customer_email'         => $bookingData['customer_email'],
            'customer_phone'         => $bookingData['customer_phone'],
            'booking_date'           => $bookingData['booking_date'],
            'start_time'             => $bookingData['start_time'],
            'end_time'               => $endTime,
            'total_duration_minutes' => $service->duration_minutes + self::BUFFER_TIME,
            'status'                 => 'confirmed'
        ]);

        $freshBooking = Booking::find($booking->id);
        if (!$freshBooking) {
            throw new \Exception('Запись не сохранилась в базе данных');
        }

        return $booking;
    }

    /**
     * Валидация брони
     *
     * @param array $data
     * @return void
     */
    private function validateBooking(array $data)
    {
        if (Carbon::parse($data['booking_date'])->isSunday()) {
            throw new \Exception('Бронирование в воскресенье невозможно');
        }

        $startTime = Carbon::parse($data['start_time']);
        if ($startTime->lt(Carbon::parse('10:00')) || $startTime->gt(Carbon::parse('20:00'))) {
            throw new \Exception('Бронирование возможно только с 10:00 до 20:00');
        }
    }

}

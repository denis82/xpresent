<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;
use App\Models\Booking;
use Illuminate\Support\Carbon;
use App\Services\BookingService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;

class BookingController extends Controller
{

    public function store(BookingRequest $bookingRequest)
    {
        $request = $bookingRequest;

        return DB::transaction(function() use ($request) {

            $existingBookings = Booking::where('booking_date', $request->booking_date)
                ->where('service_id', $request->service_id)
                ->where('status', 'confirmed')
                ->lockForUpdate()
                ->get();

            $service = Service::findOrFail($request->service_id);
            $totalDuration = $service->duration_minutes + 30;
            $endTime = Carbon::parse($request->start_time)->addMinutes($totalDuration)->format('H:i:s');
            $startTime = Carbon::parse($request->start_time)->format('H:i:s');

            foreach ($existingBookings as $booking) {
                if ($this->timeOverlaps(
                    $startTime, $endTime,
                    $booking->start_time, $booking->end_time
                )) {
                    throw new \Exception('Это время уже занято. Пожалуйста, выберите другое время.');
                }
            }
            $bookingService = new BookingService();
        try {
            $booking = $bookingService->createBooking([
                'service_id'             => $request->service_id,
                'customer_name'          => $request->customer_name,
                'customer_email'         => $request->customer_email,
                'booking_date'           => $request->booking_date,
                'customer_phone'         => $request->customer_phone,
                'start_time'             => $startTime,
                'end_time'               => $endTime,
                'total_duration_minutes' => $totalDuration,
                'status'                 => 'confirmed',
            ]);

            } catch (\Exception $e) {
            }
            return $booking;
        });
    }

    private function timeOverlaps($start1, $end1, $start2, $end2)
    {
        return ($start1 < $end2) && ($end1 > $start2);
    }
}

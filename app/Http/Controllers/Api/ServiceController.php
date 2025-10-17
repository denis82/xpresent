<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;
use App\Services\BookingService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', true)->get();
        return response()->json($services);
    }

    public function getAvailableSlots(Service $service, Request $request)
    {
        $date = $request->get('date');
        $bookingService = new BookingService();
        $slots = $bookingService->getAvailableSlots($service->id, $date);

        return response()->json($slots);
    }
}

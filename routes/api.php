<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ServiceController;


Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{service}/available-slots', [ServiceController::class, 'getAvailableSlots']);
Route::post('/bookings', [BookingController::class, 'store']);

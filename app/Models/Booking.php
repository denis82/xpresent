<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'service_id', 'customer_name', 'customer_email', 'customer_phone',
        'booking_date', 'start_time', 'end_time', 'status', 'total_duration_minutes'
    ];

    protected $casts = [
        'booking_date' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}

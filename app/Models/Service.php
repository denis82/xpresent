<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'description', 'duration_minutes', 'is_active'];

    public function schedules()
    {
        return $this->hasMany(ServiceSchedule::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function activeSchedules()
    {
        return $this->schedules()->where('is_active', true);
    }
}

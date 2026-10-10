<?php

namespace App\Modules\Booking\Listeners;
use App\Modules\Booking\Events\BookingCancelled;
use App\Modules\Booking\Events\BookingCompleted;
use App\Modules\Booking\Events\BookingConfirmed;
use Illuminate\Support\Facades\Cache;

class InvalidateMostBookedServicesCache
{
    public function handle(
        BookingConfirmed|BookingCancelled|BookingCompleted $event
    ): void {
        Cache::tags([
            'admin-reports',
            'most-booked-services',
        ])->flush();
    }
}
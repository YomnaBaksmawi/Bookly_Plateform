<?php

use App\Modules\Booking\Jobs\SendBookingReminderJob;
use App\Modules\Booking\Models\Booking;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $from = now()->addHours(23);
    $to = now()->addHours(25);

    Booking::query()
        ->where('status', 'confirmed')
        ->whereBetween('starts_at', [$from, $to])
        ->pluck('id')
        ->each(fn ($bookingId) =>
            SendBookingReminderJob::dispatch($bookingId)
        );
})->hourly();
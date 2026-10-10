<?php

namespace App\Modules\Booking\Listeners;

use App\Modules\Booking\Events\BookingCancelled;
use App\Modules\Booking\Events\BookingCompleted;
use App\Modules\Booking\Events\BookingConfirmed;
use App\Modules\Booking\Notifications\BookingStatusChangedNotification;
use Illuminate\Support\Facades\Notification;

class SendBookingStatusNotification
{
    public function handle(BookingConfirmed|BookingCancelled|BookingCompleted $event): void
    {
        $booking = $event->booking->load([
            'customer',
            'bookingSlot.serviceLocation.location.provider',
        ]);

        $status = match (true) {
            $event instanceof BookingConfirmed => 'confirmed',
            $event instanceof BookingCancelled => 'cancelled',
            $event instanceof BookingCompleted => 'completed',
        };

        Notification::send(
            $booking->customer,
            new BookingStatusChangedNotification($booking, $status)
        );

        Notification::send(
            $booking->bookingSlot->serviceLocation->location->provider,
            new BookingStatusChangedNotification($booking, $status)
        );
    }
}
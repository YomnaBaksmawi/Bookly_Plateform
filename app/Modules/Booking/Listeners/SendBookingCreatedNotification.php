<?php

namespace App\Modules\Booking\Listeners;

use App\Modules\Booking\Events\BookingCreated;
use App\Modules\Booking\Notifications\BookingCreatedNotification;
use App\Modules\Booking\Notifications\ProviderBookingCreatedNotification;
use Illuminate\Support\Facades\Notification;

class SendBookingCreatedNotification
{
    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking->load([
            'customer',
            'bookingSlot.serviceLocation.location.provider',
        ]);

        Notification::send(
            $booking->customer,
            new BookingCreatedNotification($booking)
        );

        Notification::send(
            $booking->bookingSlot->serviceLocation->location->provider,
            new ProviderBookingCreatedNotification($booking)
        );
    }
}
<?php

namespace App\Modules\Booking\Listeners;

use App\Models\User;
use App\Modules\Booking\Events\BookingCancelled;
use App\Modules\Booking\Events\BookingCompleted;
use App\Modules\Notification\Notifications\AdminBookingStatusChangedNotification;

class SendAdminBookingStatusNotification
{
    public function handle(BookingCancelled|BookingCompleted $event): void
    {
        $status = $event instanceof BookingCancelled
            ? 'cancelled'
            : 'completed';

        $admins = User::query()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(
                new AdminBookingStatusChangedNotification(
                    $event->booking,
                    $status
                )
            );
        }
    }
}
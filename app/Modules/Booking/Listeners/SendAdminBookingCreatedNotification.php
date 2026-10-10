<?php

namespace App\Modules\Booking\Listeners;

use App\Models\User;
use App\Modules\Booking\Events\BookingCreated;
use App\Modules\Notification\Notifications\AdminBookingCreatedNotification;
class SendAdminBookingCreatedNotification
{
    public function handle(BookingCreated $event): void
    {
        $admins = User::query()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(
                new AdminBookingCreatedNotification($event->booking)
            );
        }
    }
}
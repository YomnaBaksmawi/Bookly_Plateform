<?php

namespace App\Modules\Notification\Notifications;
use App\Modules\Booking\Models\Booking;
use App\Modules\Notification\Channels\FirebaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminBookingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking
    ) {
    }

    public function via(object $notifiable): array
    {
        return [
            'database',
            FirebaseChannel::class,
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Booking',
            'message' => 'A new booking has been created.',
            'booking_id' => $this->booking->id,
        ];
    }

    public function toFirebase(object $notifiable): array
    {
        return [
            'title' => 'New Booking',
            'body' => 'A new booking has been created.',
            'data' => [
                'type' => 'booking_created',
                'booking_id' => (string) $this->booking->id,
            ],
        ];
    }
}
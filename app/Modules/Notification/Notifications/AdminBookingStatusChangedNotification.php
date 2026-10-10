<?php

namespace App\Modules\Notification\Notifications;

use App\Modules\Booking\Models\Booking;
use App\Modules\Notification\Channels\FirebaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminBookingStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $status
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
            'title' => 'Booking Status Changed',
            'message' => "Booking #{$this->booking->id} has been {$this->status}.",
            'booking_id' => $this->booking->id,
            'status' => $this->status,
        ];
    }

    public function toFirebase(object $notifiable): array
    {
        return [
            'title' => 'Booking Status Changed',
            'body' => "Booking #{$this->booking->id} has been {$this->status}.",
            'data' => [
                'type' => 'booking_status_changed',
                'booking_id' => (string) $this->booking->id,
                'status' => $this->status,
            ],
        ];
    }
}
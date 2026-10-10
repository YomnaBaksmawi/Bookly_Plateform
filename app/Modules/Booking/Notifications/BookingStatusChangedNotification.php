<?php

namespace App\Modules\Booking\Notifications;

use App\Modules\Booking\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;
public $tries = 3;

public $backoff = [10, 30, 60];
    public function __construct(
        private Booking $booking,
        private string $status
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Booking {$this->status}")
            ->line("Your booking #{$this->booking->id} is now {$this->status}.")
            ->line("Thank you for using Bookly.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'status' => $this->status,
        ];
    }
}
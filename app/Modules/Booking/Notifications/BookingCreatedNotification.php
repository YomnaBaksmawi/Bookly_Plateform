<?php

namespace App\Modules\Booking\Notifications;

use App\Modules\Booking\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
class BookingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;
public $tries = 3;

public $backoff = [10, 30, 60];
    public function __construct(public Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking Created')
            ->greeting("Hello {$notifiable->name}")
            ->line('Your booking has been created successfully.')
            ->line("Booking #{$this->booking->id}")
            ->line("Status: {$this->booking->status}")
         ->action('View Booking', url("/api/v1/bookings/{$this->booking->id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'status' => $this->booking->status,
            'message' => 'Your booking has been created successfully.',
        ];
    }
}
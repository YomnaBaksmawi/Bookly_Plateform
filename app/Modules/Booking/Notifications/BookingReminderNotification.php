<?php

namespace App\Modules\Booking\Notifications;

use App\Modules\Booking\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(private Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

   public function toMail(object $notifiable): MailMessage
{
    $bookingDate = $this->booking->starts_at->format('Y-m-d');
    $bookingTime = $this->booking->starts_at->format('H:i');

    return (new MailMessage)
        ->subject('Booking Reminder')
        ->line("Reminder: your booking #{$this->booking->id} is coming up.")
        ->line("Date: {$bookingDate}")
        ->line("Time: {$bookingTime}")
        ->line('Thank you for using Bookly.');
}

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'type' => '24h',
            'starts_at' => $this->booking->starts_at->toISOString(),
        ];
    }
}
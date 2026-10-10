<?php

namespace App\Modules\Booking\Notifications;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Booking\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProviderBookingCreatedNotification extends Notification implements ShouldQueue
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
            ->subject('New Booking Received')
            ->greeting("Hello {$notifiable->name}")
            ->line('You have received a new booking.')
            ->line("Booking #{$this->booking->id}")
            ->line("Status: {$this->booking->status}")
            ->line("Customer: {$this->booking->customer->name}");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'status' => $this->booking->status,
            'message' => 'You have received a new booking.',
        ];
    }
}
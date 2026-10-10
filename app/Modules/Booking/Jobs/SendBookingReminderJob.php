<?php
namespace App\Modules\Booking\Jobs;

use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Models\BookingReminder;
use App\Modules\Booking\Notifications\BookingReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\QueryException;

class SendBookingReminderJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $backoff = [10, 30, 60];

    public $timeout = 120;

    public function __construct(public int $bookingId)
    {
    }

    public function handle(): void
    {
        $booking = Booking::with('customer')
            ->find($this->bookingId);

        if (! $booking) {
            return;
        }

        if ($booking->status !== 'confirmed') {
            return;
        }

        if ($booking->starts_at->isPast()) {
            return;
        }

        try {
            BookingReminder::create([
                'booking_id' => $booking->id,
                'type' => '24h',
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return;
            }

            throw $e;
        }

        Notification::send(
            $booking->customer,
            new BookingReminderNotification($booking)
        );

        BookingReminder::where('booking_id', $booking->id)
            ->where('type', '24h')
            ->update([
                'sent_at' => now(),
            ]);
    }
}

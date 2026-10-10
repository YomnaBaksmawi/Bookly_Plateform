<?php

namespace App\Modules\Booking\Services;

use App\Models\User;
use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Exceptions\BookingCancellationNotAllowedException;
use App\Modules\Booking\Models\Booking;
use App\Modules\BookingSlot\Models\BookingSlot;
use App\Modules\Booking\Exceptions\BookingSlotUnavailableException;
use App\Modules\Booking\Exceptions\BookingReschedulingNotAllowedException;
use RuntimeException;

class BookingReschedulingService
{
    public function validate(Booking $booking, BookingSlot $newSlot, User $user): void
    {
        $status = BookingStatus::from($booking->status);

        if ($status->isFinal()) {
            throw new BookingReschedulingNotAllowedException(
                "Booking cannot be rescheduled because its status is {$status->value}."
            );
        }

        if ($user->role === 'customer' && now()->greaterThanOrEqualTo($booking->starts_at->copy()->subHours(24))) {
            throw new BookingCancellationNotAllowedException(
                'Booking must be rescheduled at least 24 hours before the appointment.'
            );
        }

        if ($newSlot->status !== 'available') {
            throw new BookingSlotUnavailableException();
        }

        if ($newSlot->service_location_id !== $booking->service_location_id) {
            throw  new BookingReschedulingNotAllowedException(
                'The new slot must belong to the same service and location.'
            );
        }

        if ($newSlot->id === $booking->booking_slot_id) {
            throw  new BookingReschedulingNotAllowedException(
                'The new slot must be different from the current slot.'
            );
        }
    }
}
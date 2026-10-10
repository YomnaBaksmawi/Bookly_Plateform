<?php

namespace App\Modules\Booking\Services;

use App\Models\User;
use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Exceptions\BookingCancellationNotAllowedException;
use App\Modules\Booking\Models\Booking;

class BookingCancellationService
{
    public function validate(Booking $booking, User $user): void
    {
        $status = BookingStatus::from($booking->status);

        if (! $status->canBeCancelled()) {
            throw new BookingCancellationNotAllowedException(
                "Booking cannot be cancelled because its status is {$status->value}."
            );
        }

        if ($user->role === 'customer' && now()->greaterThanOrEqualTo($booking->starts_at->copy()->subHours(24))) {
            throw new BookingCancellationNotAllowedException(
                'Booking must be cancelled at least 24 hours before the appointment.'
            );
        }
    }
}
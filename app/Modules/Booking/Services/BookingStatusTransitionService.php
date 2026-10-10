<?php

namespace App\Modules\Booking\Services;

use App\Modules\Booking\Enums\BookingStatus;
use InvalidArgumentException;

class BookingStatusTransitionService
{
    public function canTransition(BookingStatus $from, BookingStatus $to): bool
    {
        return match ($from) {
            BookingStatus::PENDING => $to === BookingStatus::CONFIRMED,

            BookingStatus::CONFIRMED => in_array($to, [
                BookingStatus::COMPLETED,
                BookingStatus::NO_SHOW,
            ]),

            BookingStatus::CANCELLED,
            BookingStatus::COMPLETED,
            BookingStatus::NO_SHOW => false,
        };
    }

    public function transition(BookingStatus $from, BookingStatus $to): BookingStatus
    {
        if (! $this->canTransition($from, $to)) {
            throw new InvalidArgumentException(
                "Cannot transition booking from {$from->value} to {$to->value}."
            );
        }

        return $to;
    }
}
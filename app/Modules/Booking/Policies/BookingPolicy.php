<?php

namespace App\Modules\Booking\Policies;

use App\Models\User;
use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Models\Booking;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'customer') {
            return $booking->customer_id === $user->id;
        }

        if ($user->role === 'provider') {
            return $booking->serviceLocation?->location?->provider_id === $user->id;
        }

        return false;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'customer') {
            return $booking->customer_id === $user->id;
        }

        if ($user->role === 'provider') {
            return $booking->serviceLocation?->location?->provider_id === $user->id;
        }

        return false;
    }

    public function changeStatus(
        User $user,
        Booking $booking,
        BookingStatus $newStatus
    ): bool {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'provider') {
            return $booking->serviceLocation?->location?->provider_id === $user->id
                && $newStatus !== BookingStatus::CANCELLED;
        }

        return false;
    }

    public function reschedule(User $user, Booking $booking): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'customer') {
            return $booking->customer_id === $user->id;
        }

        if ($user->role === 'provider') {
            return $booking->serviceLocation?->location?->provider_id === $user->id;
        }

        return false;
    }
    public function viewAny(User $user): bool
{
    if ($user->role === 'admin') {
        return true;
    }

    if ($user->role === 'customer') {
        return true;
    }

    if ($user->role === 'provider') {
        return true;
    }

    return false;
}
}
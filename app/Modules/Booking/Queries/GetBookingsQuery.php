<?php

namespace App\Modules\Booking\Queries;

use App\Modules\Booking\Models\Booking;
use App\Models\User;

class GetBookingsQuery
{
    public function execute(User $user)
    {
        return Booking::query()
            ->with([
                'customer',
                'serviceLocation.service',
                'serviceLocation.location',
            ])
            ->when(
                $user->role === 'customer',
                fn ($query) => $query->where('customer_id', $user->id)
            )
            ->when(
                $user->role === 'provider',
                fn ($query) => $query->whereHas(
                    'serviceLocation.location',
                    fn ($query) => $query->where('provider_id', $user->id)
                )
            )
            ->latest('starts_at')
            ->paginate(20);
    }
}
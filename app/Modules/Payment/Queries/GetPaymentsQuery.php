<?php

namespace App\Modules\Payment\Queries;

use App\Models\User;
use App\Modules\Payment\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetPaymentsQuery
{
    public function execute(User $user): LengthAwarePaginator
    {
        return Payment::query()
            ->with([
                'booking:id,customer_id,service_location_id,starts_at,ends_at,status',
                'booking.customer:id,name,email',
            ])
            ->when(
                $user->role === 'customer',
                fn ($query) => $query->whereHas(
                    'booking',
                    fn ($booking) => $booking->where('customer_id', $user->id)
                )
            )
            ->latest()
            ->paginate(15);
    }
}
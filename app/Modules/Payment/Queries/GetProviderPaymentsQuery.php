<?php

namespace App\Modules\Payment\Queries;

use App\Models\User;
use App\Modules\Payment\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetProviderPaymentsQuery
{
    public function execute(User $user): LengthAwarePaginator
    {
        return Payment::query()
            ->with([
                'booking:id,customer_id,service_location_id,starts_at,ends_at,status,price_at_booking',
                'booking.customer:id,name,email',
                'booking.serviceLocation:id,location_id,service_id',
                'booking.serviceLocation.location:id,provider_id,name',
                'booking.serviceLocation.service:id,name',
            ])
            ->whereHas(
                'booking.serviceLocation.location',
                fn ($query) => $query->where('provider_id', $user->id)
            )
            ->latest()
            ->paginate(15);
    }
}
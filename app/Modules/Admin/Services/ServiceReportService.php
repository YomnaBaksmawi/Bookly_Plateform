<?php

namespace App\Modules\Admin\Services;

use App\Modules\Booking\Models\Booking;
use App\Modules\Service\Models\Service;
use Carbon\Carbon;

class ServiceReportService
{
    public function get(array $filters): array
    {
        $services = Service::query()
            ->with('provider')
            ->get();

        return $services->map(function (Service $service) use ($filters) {

            $query = Booking::query()
                ->whereHas(
                    'serviceLocation',
                    fn ($query) => $query->where('service_id', $service->id)
                );

            if (!empty($filters['from'])) {
                $query->where(
                    'starts_at',
                    '>=',
                    Carbon::parse($filters['from'])->startOfDay()
                );
            }

            if (!empty($filters['to'])) {
                $query->where(
                    'starts_at',
                    '<=',
                    Carbon::parse($filters['to'])->endOfDay()
                );
            }

            $bookingIds = (clone $query)->pluck('id');

            $revenue = Booking::query()
                ->whereIn('id', $bookingIds)
                ->whereHas(
                    'payment',
                    fn ($query) => $query->where('status', 'paid')
                )
                ->with('payment:id,booking_id,amount,status')
                ->get()
                ->sum(fn ($booking) => $booking->payment->amount);

            return [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'provider_id' => $service->provider_id,
                'provider_name' => $service->provider?->name,
                'total_bookings' => (clone $query)->count(),
                'completed_bookings' => (clone $query)
                    ->where('status', 'completed')
                    ->count(),
                'cancelled_bookings' => (clone $query)
                    ->where('status', 'cancelled')
                    ->count(),
                'revenue' => number_format(
                    (float) $revenue,
                    2,
                    '.',
                    ''
                ),
            ];
        })->values()->all();
    }
}
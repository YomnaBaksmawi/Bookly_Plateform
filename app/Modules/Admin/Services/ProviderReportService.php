<?php

namespace App\Modules\Admin\Services;

use App\Models\User;
use App\Modules\Booking\Models\Booking;
use Carbon\Carbon;

class ProviderReportService
{
    public function get(array $filters): array
    {
        $providers = User::query()
            ->where('role', 'provider')
            ->get();

        return $providers->map(function (User $provider) use ($filters) {

            $query = Booking::query()
                ->whereHas(
                    'serviceLocation.service',
                    fn ($query) => $query->where('provider_id', $provider->id)
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

            $totalBookings = (clone $query)->count();

            $completedBookings = (clone $query)
                ->where('status', 'completed')
                ->count();

            $cancelledBookings = (clone $query)
                ->where('status', 'cancelled')
                ->count();

            $bookingIds = (clone $query)->pluck('id');

            $revenue = Booking::query()
                ->whereIn('id', $bookingIds)
                ->whereHas(
                    'payment',
                    fn ($query) => $query->where('status', 'paid')
                )
                ->with('payment:id,booking_id,amount,status')
                ->get()
                ->sum(fn (Booking $booking) => $booking->payment->amount);

            return [
                'provider_id' => $provider->id,
                'provider_name' => $provider->name,
                'total_bookings' => $totalBookings,
                'completed_bookings' => $completedBookings,
                'cancelled_bookings' => $cancelledBookings,
                'revenue' => number_format((float) $revenue, 2, '.', ''),
            ];
        })->values()->all();
    }
}
<?php

namespace App\Modules\Booking\Services;

use App\Modules\Booking\Exceptions\BookingSlotUnavailableException;
use App\Modules\Booking\Models\Booking;
use App\Modules\BookingSlot\Models\BookingSlot;
use Carbon\Carbon;

class BookingService
{
    public function create(int $customerId, BookingSlot $slot, ?string $notes = null): Booking
    {
        if ($slot->status !== 'available') {
            throw new BookingSlotUnavailableException;
        }

        $serviceLocation = $slot->serviceLocation()->with(['service', 'location'])->firstOrFail();
        $service = $serviceLocation->service;
        $location = $serviceLocation->location;

        if ($service->status !== 'active' || ! $location->is_active) {
            throw new BookingSlotUnavailableException;
        }

        $startsAt = Carbon::parse(
            "{$slot->date->format('Y-m-d')} {$slot->start_time}",
            $location->timezone
        );

        $endsAt = Carbon::parse(
            "{$slot->date->format('Y-m-d')} {$slot->end_time}",
            $location->timezone
        );

        $booking = Booking::create([
            'customer_id' => $customerId,
            'service_location_id' => $serviceLocation->id,
            'booking_slot_id' => $slot->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => 'pending',
            'price_at_booking' => $service->price,
            'notes' => $notes,
        ]);

        $slot->update([
            'status' => 'booked',
            'locked_at' => null,
            'expires_at' => null,
        ]);

        $booking->statusLogs()->create([
            'from_status' => null,
            'to_status' => 'pending',
            'changed_by' => $customerId,
        ]);

        return $booking;
    }
}

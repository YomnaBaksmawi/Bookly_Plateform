<?php

namespace App\Modules\Booking\Actions;

use App\Models\User;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Services\BookingReschedulingService;
use App\Modules\BookingSlot\Models\BookingSlot;
use Illuminate\Support\Facades\DB;

class RescheduleBookingAction
{
    public function __construct( private BookingReschedulingService $reschedulingService) { }

    public function execute(  Booking $booking,  int $newSlotId, User $user, ?string $reason = null ): Booking {
        return DB::transaction(function () use ($booking, $newSlotId, $user, $reason) {
            $booking = Booking::query()->whereKey($booking->id)->lockForUpdate() ->firstOrFail();

            $oldSlot = BookingSlot::query()->whereKey($booking->booking_slot_id)->lockForUpdate()->firstOrFail();

            $newSlot = BookingSlot::query()->whereKey($newSlotId)->lockForUpdate()->firstOrFail();

            $this->reschedulingService->validate($booking, $newSlot, $user);

            $serviceLocation = $newSlot->serviceLocation()->with(['service', 'location'])->firstOrFail();

            $location = $serviceLocation->location;

            $startsAt = $newSlot->date->copy()->setTimeFromTimeString($newSlot->start_time)->setTimezone($location->timezone);

            $endsAt = $newSlot->date->copy()->setTimeFromTimeString($newSlot->end_time)->setTimezone($location->timezone);

            $oldSlot->update([
                'status' => 'available',
                'locked_at' => null,
                'expires_at' => null,
            ]);

            $newSlot->update([
                'status' => 'booked',
                'locked_at' => null,
                'expires_at' => null,
            ]);

            $booking->update([
                'booking_slot_id' => $newSlot->id,
                'service_location_id' => $newSlot->service_location_id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);

            return $booking->fresh([
                'bookingSlot.serviceLocation.service',
                'bookingSlot.serviceLocation.location',
            ]);
        });
    }
}
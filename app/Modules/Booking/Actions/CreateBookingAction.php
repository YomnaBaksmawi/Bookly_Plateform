<?php

namespace App\Modules\Booking\Actions;

use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Services\BookingService;
use App\Modules\BookingSlot\Models\BookingSlot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Modules\Booking\Events\BookingCreated;
class CreateBookingAction
{
    public function __construct(
        private BookingService $bookingService
    ) {
    }

    public function execute(int $customerId, int $slotId, ?string $notes = null): Booking
    {
        $booking = DB::transaction(function () use ($customerId, $slotId, $notes) {
            $slot = BookingSlot::query()
                ->whereKey($slotId)
                ->lockForUpdate()
                ->firstOrFail();

            return $this->bookingService->create($customerId, $slot, $notes);
        });

        $booking->load(['bookingSlot.serviceLocation.service', 'bookingSlot.serviceLocation.location']);

        if ($booking->wasRecentlyCreated) {
            $location = $booking->bookingSlot->serviceLocation->location;

            Cache::forget(
                "availability:{$booking->bookingSlot->serviceLocation->service_id}:{$location->id}:{$booking->bookingSlot->date->format('Y-m-d')}"
            );
            Cache::tags([
    'admin-reports',
    'most-booked-services',
])->flush();
event(new BookingCreated($booking));
        }

        

        return $booking;
    }
}
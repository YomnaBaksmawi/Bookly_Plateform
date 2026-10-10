<?php

namespace App\Modules\Booking\Actions;
use App\Models\User;
use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Services\BookingCancellationService;
use Illuminate\Support\Facades\DB;
use App\Modules\Booking\Events\BookingCancelled;

class CancelBookingAction
{
    public function __construct(
        private BookingCancellationService $cancellationService) {
    }

    public function execute(Booking $booking, User $user, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($booking, $user, $reason) {
            $booking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $this->cancellationService->validate($booking, $user);

            $slot = $booking->bookingSlot()->lockForUpdate()->firstOrFail();

            $currentStatus = $booking->status;

            $booking->update([
                'status' => BookingStatus::CANCELLED->value,
            ]);

            $slot->update([
                'status' => 'available',
                'locked_at' => null,
                'expires_at' => null,
            ]);

            $booking->statusLogs()->create([
                'from_status' => $currentStatus,
                'to_status' => BookingStatus::CANCELLED->value,
                'changed_by' => $user->id,
                'reason' => $reason,
            ]);
    BookingCancelled::dispatch($booking);

            return $booking->fresh();
        });
    }
}
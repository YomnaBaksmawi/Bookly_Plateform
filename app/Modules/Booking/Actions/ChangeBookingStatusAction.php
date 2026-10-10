<?php

namespace App\Modules\Booking\Actions;

use App\Models\User;
use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Services\BookingStatusTransitionService;
use Illuminate\Support\Facades\DB;
use App\Modules\Booking\Events\BookingCancelled;
use App\Modules\Booking\Events\BookingCompleted;
use App\Modules\Booking\Events\BookingConfirmed;
class ChangeBookingStatusAction
{
    public function __construct(
        private BookingStatusTransitionService $transitionService
    ) {
    }

   public function execute(Booking $booking, BookingStatus $newStatus, User $user, ?string $reason = null): Booking
{
    $booking = DB::transaction(function () use ($booking, $newStatus, $user, $reason) {
        $booking = Booking::query()
            ->whereKey($booking->id)
            ->lockForUpdate()
            ->firstOrFail();

        $currentStatus = BookingStatus::from($booking->status);

        $this->transitionService->transition($currentStatus, $newStatus);

        $booking->update([
            'status' => $newStatus->value,
        ]);

        $booking->statusLogs()->create([
            'from_status' => $currentStatus->value,
            'to_status' => $newStatus->value,
            'changed_by' => $user->id,
            'reason' => $reason,
        ]);

        return $booking->fresh();
    });

    match ($newStatus) {
        BookingStatus::CONFIRMED => BookingConfirmed::dispatch($booking),
        BookingStatus::CANCELLED => BookingCancelled::dispatch($booking),
        BookingStatus::COMPLETED => BookingCompleted::dispatch($booking),
        default => null,
    };

    return $booking;
}
}
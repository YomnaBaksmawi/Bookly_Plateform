<?php

namespace App\Modules\Booking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Actions\CancelBookingAction;
use App\Modules\Booking\Actions\ChangeBookingStatusAction;
use App\Modules\Booking\Actions\CreateBookingAction;
use App\Modules\Booking\Actions\RescheduleBookingAction;
use App\Modules\Booking\Http\Requests\CancelBookingRequest;
use App\Modules\Booking\Http\Requests\ChangeBookingStatusRequest;
use App\Modules\Booking\Http\Requests\CreateBookingRequest;
use App\Modules\Booking\Http\Requests\RescheduleBookingRequest;
use App\Modules\Booking\Http\Resources\BookingResource;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Queries\GetBookingsQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class BookingController extends Controller
{
    public function store(CreateBookingRequest $request, CreateBookingAction $action): JsonResponse
    {
        $booking = $action->execute(auth()->id(), $request->integer('booking_slot_id'), $request->input('notes'));

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully.',
            'data' => new BookingResource($booking),
        ], 201);
    }

    public function changeStatus(ChangeBookingStatusRequest $request, Booking $booking, ChangeBookingStatusAction $action): JsonResponse
    {
        $newStatus = $request->status();

        Gate::authorize('changeStatus', [$booking, $newStatus]);

        $booking = $action->execute(
            $booking,
            $newStatus,
            $request->user(),
            $request->input('reason')
        );

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully.',
            'data' => new BookingResource($booking),
        ]);
    }

    public function cancel(CancelBookingRequest $request, Booking $booking, CancelBookingAction $action): JsonResponse
    {
        Gate::authorize('cancel', $booking);

        $booking = $action->execute($booking, $request->user(), $request->input('reason'));

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully.',
            'data' => new BookingResource($booking),
        ]);
    }

    public function reschedule(RescheduleBookingRequest $request, Booking $booking, RescheduleBookingAction $action): JsonResponse
    {
        Gate::authorize('reschedule', $booking);

        $booking = $action->execute(
            $booking,
            $request->integer('booking_slot_id'),
            $request->user(),
            $request->input('reason')
        );

        return response()->json([
            'success' => true,
            'message' => 'Booking rescheduled successfully.',
            'data' => new BookingResource($booking),
        ]);
    }
    // public function index(){

    //     return Booking::with('customer')->get();
    // }

    public function index(GetBookingsQuery $query)
    {
        return $query->execute(auth()->user());
    }

    public function show(Booking $booking): JsonResponse
    {
        Gate::authorize('view', $booking);

        $booking->load([
            'bookingSlot.serviceLocation.service',
            'bookingSlot.serviceLocation.location',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking retrieved successfully.',
            'data' => new BookingResource($booking),
        ]);
    }
}

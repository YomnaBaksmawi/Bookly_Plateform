<?php

use App\Models\User;
use App\Modules\Booking\Exceptions\BookingCancellationNotAllowedException;
use App\Modules\Booking\Exceptions\BookingReschedulingNotAllowedException;
use App\Modules\Booking\Exceptions\BookingSlotUnavailableException;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Services\BookingReschedulingService;
use App\Modules\ServiceLocation\Models\ServiceLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Support\BookingFixtures;

uses(TestCase::class, RefreshDatabase::class);

function rsCustomer(): User
{
    return BookingFixtures::customer();
}

function rsBooking(ServiceLocation $serviceLocation, array $attributes = []): Booking
{
    $slot = BookingFixtures::slot($serviceLocation, now()->addDays(7)->toDateString(), '09:00', 60);

    return Booking::create(array_merge([
        'customer_id' => rsCustomer()->id,
        'service_location_id' => $serviceLocation->id,
        'booking_slot_id' => $slot->id,
        'starts_at' => now()->addDays(3),
        'ends_at' => now()->addDays(3)->addHour(),
        'status' => 'confirmed',
        'price_at_booking' => 120,
    ], $attributes));
}

it('allows rescheduling to an available slot of the same service and location', function () {
    [, , , , $serviceLocation] = BookingFixtures::stack();
    $booking = rsBooking($serviceLocation);
    $newSlot = BookingFixtures::slot($serviceLocation, now()->addDays(7)->toDateString(), '11:00', 60);

    (new BookingReschedulingService)->validate($booking, $newSlot, rsCustomer());
    expect(true)->toBeTrue();
});

it('blocks rescheduling a booking in a final state', function () {
    [, , , , $serviceLocation] = BookingFixtures::stack();
    $booking = rsBooking($serviceLocation, ['status' => 'completed']);
    $newSlot = BookingFixtures::slot($serviceLocation, now()->addDays(7)->toDateString(), '11:00', 60);

    expect(fn () => (new BookingReschedulingService)->validate($booking, $newSlot, rsCustomer()))
        ->toThrow(BookingReschedulingNotAllowedException::class, 'cannot be rescheduled');
});

it('blocks customers from rescheduling inside the 24 hour window', function () {
    [, , , , $serviceLocation] = BookingFixtures::stack();
    $booking = rsBooking($serviceLocation, ['starts_at' => now()->addHours(23)]);
    $newSlot = BookingFixtures::slot($serviceLocation, now()->addDays(7)->toDateString(), '11:00', 60);

    expect(fn () => (new BookingReschedulingService)->validate($booking, $newSlot, rsCustomer()))
        ->toThrow(BookingCancellationNotAllowedException::class, 'at least 24 hours');
});

it('rejects a target slot that is already booked', function () {
    [, , , , $serviceLocation] = BookingFixtures::stack();
    $booking = rsBooking($serviceLocation);
    $newSlot = BookingFixtures::slot($serviceLocation, now()->addDays(7)->toDateString(), '11:00', 60, 'booked');

    expect(fn () => (new BookingReschedulingService)->validate($booking, $newSlot, rsCustomer()))
        ->toThrow(BookingSlotUnavailableException::class);
});

it('rejects a slot from a different service or location', function () {
    [$provider, $category, , , $serviceLocation] = BookingFixtures::stack();
    $otherProvider = BookingFixtures::provider();
    $otherLocation = BookingFixtures::location($otherProvider, ['name' => 'Second Studio']);
    $otherService = BookingFixtures::service($otherProvider, $category, ['name' => 'Other Service']);
    $otherServiceLocation = BookingFixtures::serviceLocation($otherService, $otherLocation);

    $booking = rsBooking($serviceLocation);
    $newSlot = BookingFixtures::slot($otherServiceLocation, now()->addDays(7)->toDateString(), '11:00', 60);

    expect(fn () => (new BookingReschedulingService)->validate($booking, $newSlot, rsCustomer()))
        ->toThrow(BookingReschedulingNotAllowedException::class, 'same service and location');
});

it('rejects the slot the booking currently occupies', function () {
    [, , , , $serviceLocation] = BookingFixtures::stack();
    $booking = rsBooking($serviceLocation);

    expect(fn () => (new BookingReschedulingService)->validate($booking, $booking->bookingSlot, rsCustomer()))
        ->toThrow(BookingReschedulingNotAllowedException::class, 'must be different');
});
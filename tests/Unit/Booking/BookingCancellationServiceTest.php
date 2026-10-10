<?php

use App\Modules\Booking\Exceptions\BookingCancellationNotAllowedException;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Services\BookingCancellationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Support\BookingFixtures;

uses(TestCase::class, RefreshDatabase::class);

function makeBooking(array $attributes = []): Booking
{
    [, , , , $serviceLocation] = BookingFixtures::stack();
    $slot = BookingFixtures::slot($serviceLocation, now()->addDays(7)->toDateString(), '09:00', 60);

    return Booking::create(array_merge([
        'customer_id' => BookingFixtures::customer()->id,
        'service_location_id' => $serviceLocation->id,
        'booking_slot_id' => $slot->id,
        'starts_at' => now()->addDays(3),
        'ends_at' => now()->addDays(3)->addHour(),
        'status' => 'confirmed',
        'price_at_booking' => 120,
    ], $attributes));
}

it('allows a customer to cancel a confirmed booking more than 24 hours ahead', function () {
    $booking = makeBooking(['starts_at' => now()->addHours(25)]);

    (new BookingCancellationService)->validate($booking, BookingFixtures::customer());
    expect(true)->toBeTrue();
});

it('blocks a customer from cancelling inside the 24 hour window', function () {
    $booking = makeBooking(['starts_at' => now()->addHours(23)]);

    expect(fn () => (new BookingCancellationService)->validate($booking, BookingFixtures::customer()))
        ->toThrow(BookingCancellationNotAllowedException::class, 'at least 24 hours');
});

it('exempts providers from the 24 hour cancellation window', function () {
    $booking = makeBooking(['starts_at' => now()->addHour()]);

    (new BookingCancellationService)->validate($booking, BookingFixtures::provider());
    expect(true)->toBeTrue();
});

it('blocks cancellation once a booking is completed', function () {
    $booking = makeBooking(['status' => 'completed']);

    expect(fn () => (new BookingCancellationService)->validate($booking, BookingFixtures::provider()))
        ->toThrow(BookingCancellationNotAllowedException::class, 'cannot be cancelled');
});

it('blocks cancelling an already cancelled booking', function () {
    $booking = makeBooking(['status' => 'cancelled']);

    expect(fn () => (new BookingCancellationService)->validate($booking, BookingFixtures::customer()))
        ->toThrow(BookingCancellationNotAllowedException::class, 'cannot be cancelled');
});
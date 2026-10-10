<?php

use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Policies\BookingPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Support\BookingFixtures;

uses(TestCase::class, RefreshDatabase::class);

function policyBooking(): array
{
    [$provider, , , , $serviceLocation] = BookingFixtures::stack();
    $customer = BookingFixtures::customer();
    $slot = BookingFixtures::slot($serviceLocation, now()->addDays(7)->toDateString(), '09:00', 60);

    $booking = Booking::create([
        'customer_id' => $customer->id,
        'service_location_id' => $serviceLocation->id,
        'booking_slot_id' => $slot->id,
        'starts_at' => now()->addDays(3),
        'ends_at' => now()->addDays(3)->addHour(),
        'status' => 'pending',
        'price_at_booking' => 120,
    ]);

    return [$provider, $customer, $booking];
}

it('lets an admin view, cancel and reschedule any booking', function () {
    [$provider, , $booking] = policyBooking();
    $admin = BookingFixtures::customer(['role' => 'admin']);

    expect((new BookingPolicy)->view($admin, $booking))->toBeTrue();
    expect((new BookingPolicy)->cancel($admin, $booking))->toBeTrue();
    expect((new BookingPolicy)->reschedule($admin, $booking))->toBeTrue();
    expect((new BookingPolicy)->changeStatus($admin, $booking, BookingStatus::CONFIRMED))->toBeTrue();
});

it('lets the owning customer view, cancel and reschedule their booking', function () {
    [, $customer, $booking] = policyBooking();

    expect((new BookingPolicy)->view($customer, $booking))->toBeTrue();
    expect((new BookingPolicy)->cancel($customer, $booking))->toBeTrue();
    expect((new BookingPolicy)->reschedule($customer, $booking))->toBeTrue();
    expect((new BookingPolicy)->changeStatus($customer, $booking, BookingStatus::CONFIRMED))->toBeFalse();
});

it('denies other customers access to a booking', function () {
    [, , $booking] = policyBooking();
    $other = BookingFixtures::customer();

    expect((new BookingPolicy)->view($other, $booking))->toBeFalse();
    expect((new BookingPolicy)->cancel($other, $booking))->toBeFalse();
    expect((new BookingPolicy)->reschedule($other, $booking))->toBeFalse();
});

it('lets the owning provider manage bookings at their location', function () {
    [$provider, , $booking] = policyBooking();

    expect((new BookingPolicy)->view($provider, $booking))->toBeTrue();
    expect((new BookingPolicy)->cancel($provider, $booking))->toBeTrue();
    expect((new BookingPolicy)->reschedule($provider, $booking))->toBeTrue();
});

it('keeps providers from cancelling a booking through status change', function () {
    [$provider, , $booking] = policyBooking();

    expect((new BookingPolicy)->changeStatus($provider, $booking, BookingStatus::CONFIRMED))->toBeTrue();
    expect((new BookingPolicy)->changeStatus($provider, $booking, BookingStatus::CANCELLED))->toBeFalse();
});

it('denies unrelated providers access to a booking', function () {
    [, , $booking] = policyBooking();
    $otherProvider = BookingFixtures::provider();

    expect((new BookingPolicy)->view($otherProvider, $booking))->toBeFalse();
    expect((new BookingPolicy)->reschedule($otherProvider, $booking))->toBeFalse();
});
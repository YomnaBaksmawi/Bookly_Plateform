<?php

use App\Modules\Booking\Exceptions\BookingSlotUnavailableException;
use App\Modules\Booking\Services\BookingService;
use App\Modules\BookingStatusLog\Models\BookingStatusLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Support\BookingFixtures;

uses(TestCase::class, RefreshDatabase::class);

it('creates a pending booking with a price snapshot and an initial status log', function () {
    [, , $location, $service, $serviceLocation] = BookingFixtures::stack();
    $customer = BookingFixtures::customer();
    $slot = BookingFixtures::slot($serviceLocation, now()->addDays(2)->toDateString(), '10:00', 60);

    $booking = (new BookingService)->create($customer->id, $slot, 'Please confirm');

    expect($booking->customer_id)->toBe($customer->id);
    expect($booking->service_location_id)->toBe($serviceLocation->id);
    expect($booking->booking_slot_id)->toBe($slot->id);
    expect($booking->status)->toBe('pending');
    expect((float) $booking->price_at_booking)->toBe((float) $service->price);
    $expectedStart = Carbon::parse("{$slot->date->toDateString()} 10:00", $location->timezone);
    expect($booking->starts_at->equalTo($expectedStart))->toBeTrue();
    expect($booking->ends_at->equalTo($expectedStart->copy()->addMinutes($service->duration)))->toBeTrue();

    expect($slot->fresh()->status)->toBe('booked');

    $this->assertSame(1, BookingStatusLog::where('booking_id', $booking->id)->count());
    $log = BookingStatusLog::where('booking_id', $booking->id)->first();
    expect($log->from_status)->toBeNull();
    expect($log->to_status)->toBe('pending');
    expect($log->changed_by)->toBe($customer->id);
});

it('rejects booking an already booked slot', function () {
    [, , , , $serviceLocation] = BookingFixtures::stack();
    $customer = BookingFixtures::customer();
    $slot = BookingFixtures::slot($serviceLocation, now()->addDays(2)->toDateString(), '10:00', 60, 'booked');

    expect(fn () => (new BookingService)->create($customer->id, $slot))
        ->toThrow(BookingSlotUnavailableException::class);
});

it('rejects booking a slot whose service is inactive', function () {
    [, , , $service, $serviceLocation] = BookingFixtures::stack();
    $service->update(['status' => 'inactive']);
    $customer = BookingFixtures::customer();
    $slot = BookingFixtures::slot($serviceLocation, now()->addDays(2)->toDateString(), '10:00', 60);

    expect(fn () => (new BookingService)->create($customer->id, $slot))
        ->toThrow(BookingSlotUnavailableException::class);
});

it('rejects booking a slot whose location is inactive', function () {
    [$provider, , $location, , $serviceLocation] = BookingFixtures::stack();
    $location->update(['is_active' => false]);
    $customer = BookingFixtures::customer();
    $slot = BookingFixtures::slot($serviceLocation, now()->addDays(2)->toDateString(), '10:00', 60);

    expect(fn () => (new BookingService)->create($customer->id, $slot))
        ->toThrow(BookingSlotUnavailableException::class);
});
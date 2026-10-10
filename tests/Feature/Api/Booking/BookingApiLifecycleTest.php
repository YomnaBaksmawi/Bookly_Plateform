<?php

use App\Http\Middleware\VerifyApiCsrfToken;
use App\Modules\Auth\Actions\GenerateTokenAction;
use App\Modules\Booking\Models\Booking;
use App\Modules\BookingSlot\Models\BookingSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Support\BookingFixtures;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([VerifyApiCsrfToken::class]);
    Cache::flush();
});

function lifecycleAs($test, string $token)
{
    \Illuminate\Support\Facades\Auth::forgetGuards();

    return $test->withToken($token);
}

function createLifecycleBooking($test, string $customerToken): array
{
    [$provider, , , $service, $serviceLocation] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($serviceLocation->location);
    $customer = BookingFixtures::customer();
    $date = now()->addDays(3)->toDateString();
    $slot = BookingFixtures::slot($serviceLocation, $date, '10:00', 60);

    $response = lifecycleAs($test, $customerToken)
        ->withHeaders(['Idempotency-Key' => 'lc-'.Str::uuid()])
        ->postJson('/api/v1/bookings', ['booking_slot_id' => $slot->id]);

    return [$provider, $serviceLocation, $slot, $response];
}

it('lets the customer cancel a booking more than 24 hours ahead', function () {
    $customer = BookingFixtures::customer();
    $token = (new GenerateTokenAction)->execute($customer, 'auth_token');

    [, $serviceLocation, $slot, $response] = createLifecycleBooking($this, $token);
    $response->assertCreated();
    $bookingId = $response->json('data.id');

    lifecycleAs($this, $token)
        ->patchJson("/api/v1/bookings/{$bookingId}/cancel", ['reason' => 'Changed my mind'])
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled');

    $booking = Booking::findOrFail($bookingId);
    expect($booking->status)->toBe('cancelled');
    expect($booking->statusLogs()->get()->last()->to_status)->toBe('cancelled');
    expect($slot->fresh()->status)->toBe('available');
});

it('blocks a customer from cancelling inside the 24 hour window', function () {
    [, , $location, $service, $serviceLocation] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($location);
    $customer = BookingFixtures::customer();
    $token = (new GenerateTokenAction)->execute($customer, 'auth_token');
    $date = now()->addDays(2)->toDateString();
    $slot = BookingFixtures::slot($serviceLocation, $date, '10:00', 60);

    $booking = Booking::create([
        'customer_id' => $customer->id,
        'service_location_id' => $serviceLocation->id,
        'booking_slot_id' => $slot->id,
        'starts_at' => now()->addHour(),
        'ends_at' => now()->addHours(2),
        'status' => 'pending',
        'price_at_booking' => $service->price,
    ]);

    lifecycleAs($this, $token)
        ->patchJson("/api/v1/bookings/{$booking->id}/cancel")
        ->assertStatus(409)
        ->assertJsonPath('error_code', 'BOOKING_CANCELLATION_NOT_ALLOWED');
});

it('lets the customer reschedule to another available slot', function () {
    $customer = BookingFixtures::customer();
    $token = (new GenerateTokenAction)->execute($customer, 'auth_token');

    [, $serviceLocation, $slot, $response] = createLifecycleBooking($this, $token);
    $bookingId = $response->json('data.id');
    $date = $slot->date->toDateString();
    $newSlot = BookingFixtures::slot($serviceLocation, $date, '14:00', 60);

    lifecycleAs($this, $token)
        ->patchJson("/api/v1/bookings/{$bookingId}/reschedule", ['booking_slot_id' => $newSlot->id])
        ->assertOk()
        ->assertJsonPath('data.id', $bookingId);

    expect(Booking::findOrFail($bookingId)->booking_slot_id)->toBe($newSlot->id);
    expect($newSlot->fresh()->status)->toBe('booked');
    expect($slot->fresh()->status)->toBe('available');
});

it('blocks rescheduling to a slot that is already booked', function () {
    $customer = BookingFixtures::customer();
    $token = (new GenerateTokenAction)->execute($customer, 'auth_token');

    [, $serviceLocation, $slot, $response] = createLifecycleBooking($this, $token);
    $bookingId = $response->json('data.id');
    $blocked = BookingFixtures::slot($serviceLocation, $slot->date->toDateString(), '15:00', 60, 'booked');

    lifecycleAs($this, $token)
        ->patchJson("/api/v1/bookings/{$bookingId}/reschedule", ['booking_slot_id' => $blocked->id])
        ->assertStatus(409)
        ->assertJsonPath('error_code', 'BOOKING_SLOT_UNAVAILABLE');
});

it('rejects rescheduling to the slot the booking already occupies', function () {
    $customer = BookingFixtures::customer();
    $token = (new GenerateTokenAction)->execute($customer, 'auth_token');

    [, , $slot, $response] = createLifecycleBooking($this, $token);
    $bookingId = $response->json('data.id');

    // The occupied slot is no longer available, so availability is rejected first.
    lifecycleAs($this, $token)
        ->patchJson("/api/v1/bookings/{$bookingId}/reschedule", ['booking_slot_id' => $slot->id])
        ->assertStatus(409)
        ->assertJsonPath('error_code', 'BOOKING_SLOT_UNAVAILABLE');
});
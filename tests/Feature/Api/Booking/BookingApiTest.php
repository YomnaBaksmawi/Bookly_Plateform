<?php

use App\Http\Middleware\VerifyApiCsrfToken;
use App\Modules\Auth\Actions\GenerateTokenAction;
use App\Modules\Booking\Events\BookingCreated;
use App\Modules\Booking\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Support\BookingFixtures;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([VerifyApiCsrfToken::class]);
    Cache::flush();
});

function bookingAs($test, string $token)
{
    \Illuminate\Support\Facades\Auth::forgetGuards();

    return $test->withToken($token);
}

it('requires an idempotency key to create a booking', function () {
    [, , , $service, $serviceLocation] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($serviceLocation->location);
    $customer = BookingFixtures::customer();
    $token = (new GenerateTokenAction)->execute($customer, 'auth_token');
    $date = now()->addDays(3)->toDateString();

    $slot = BookingFixtures::slot($serviceLocation, $date, '10:00', 60);

    bookingAs($this, $token)->postJson('/api/v1/bookings', ['booking_slot_id' => $slot->id])
        ->assertStatus(422)
        ->assertJsonPath('error_code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['Idempotency-Key']]);
});

it('does not let a provider create customer bookings', function () {
    [, , , , $serviceLocation] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($serviceLocation->location);
    $provider = BookingFixtures::provider();
    $token = (new GenerateTokenAction)->execute($provider, 'auth_token');
    $date = now()->addDays(3)->toDateString();

    $slot = BookingFixtures::slot($serviceLocation, $date, '10:00', 60);

    bookingAs($this, $token)
        ->postJson('/api/v1/bookings', [
            'booking_slot_id' => $slot->id,
            'notes' => 'nope',
        ], ['Idempotency-Key' => 'bkey-provider'])
        ->assertStatus(403)
        ->assertJsonPath('error_code', 'FORBIDDEN');
});

it('creates a booking and dispatches the BookingCreated event', function () {
    Event::fake([BookingCreated::class]);

    [, , , $service, $serviceLocation, ] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($serviceLocation->location);
    $customer = BookingFixtures::customer();
    $token = (new GenerateTokenAction)->execute($customer, 'auth_token');
    $date = now()->addDays(3)->toDateString();

    $slot = BookingFixtures::slot($serviceLocation, $date, '10:00', 60);

    $response = bookingAs($this, $token)
        ->withHeaders(['Idempotency-Key' => 'bkey-create'])
        ->postJson('/api/v1/bookings', ['booking_slot_id' => $slot->id]);

    $response->assertCreated();
    $response->assertJsonPath('data.status', 'pending');
    $response->assertJsonPath('data.service.id', $service->id);
    $response->assertJsonPath('data.date', $date);

    $booking = Booking::findOrFail($response->json('data.id'));
    expect($booking->customer_id)->toBe($customer->id);
    expect($booking->booking_slot_id)->toBe($slot->id);
    expect((float) $booking->price_at_booking)->toBe((float) $service->price);
    expect($slot->fresh()->status)->toBe('booked');
    expect($booking->statusLogs()->count())->toBe(1);
    expect($booking->statusLogs()->first()->to_status)->toBe('pending');

    $this->assertTrue($booking->starts_at->isAfter(now()));

    Event::assertDispatched(BookingCreated::class);
});

it('rejects double booking of the same slot', function () {
    [, , , , $serviceLocation] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($serviceLocation->location);
    $customer = BookingFixtures::customer();
    $token = (new GenerateTokenAction)->execute($customer, 'auth_token');
    $date = now()->addDays(3)->toDateString();

    $slot = BookingFixtures::slot($serviceLocation, $date, '10:00', 60, 'booked');

    bookingAs($this, $token)
        ->withHeaders(['Idempotency-Key' => 'bkey-double'])
        ->postJson('/api/v1/bookings', ['booking_slot_id' => $slot->id])
        ->assertStatus(409)
        ->assertJsonPath('error_code', 'BOOKING_SLOT_UNAVAILABLE');
});

it('lets the owning provider confirm a booking', function () {
    [$provider, , , , $serviceLocation] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($serviceLocation->location);
    $customer = BookingFixtures::customer();
    $customerToken = (new GenerateTokenAction)->execute($customer, 'auth_token');
    $providerToken = (new GenerateTokenAction)->execute($provider, 'auth_token');
    $date = now()->addDays(3)->toDateString();

    $slot = BookingFixtures::slot($serviceLocation, $date, '10:00', 60);

    $bookingId = bookingAs($this, $customerToken)
        ->withHeaders(['Idempotency-Key' => 'bkey-confirm'])
        ->postJson('/api/v1/bookings', ['booking_slot_id' => $slot->id])
        ->assertCreated()->json('data.id');

    bookingAs($this, $providerToken)
        ->patchJson("/api/v1/bookings/{$bookingId}/status", ['status' => 'confirmed'])
        ->assertOk()
        ->assertJsonPath('data.status', 'confirmed');

    $booking = Booking::findOrFail($bookingId);
    expect($booking->status)->toBe('confirmed');
    expect($booking->statusLogs()->pluck('to_status')->toArray())->toBe(['pending', 'confirmed']);
});

it('forbids customers from changing booking status', function () {
    [, , , , $serviceLocation] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($serviceLocation->location);
    $customer = BookingFixtures::customer();
    $token = (new GenerateTokenAction)->execute($customer, 'auth_token');
    $date = now()->addDays(3)->toDateString();

    $slot = BookingFixtures::slot($serviceLocation, $date, '10:00', 60);

    $bookingId = bookingAs($this, $token)
        ->withHeaders(['Idempotency-Key' => 'bkey-owner'])
        ->postJson('/api/v1/bookings', ['booking_slot_id' => $slot->id])
        ->assertCreated()->json('data.id');

    bookingAs($this, $token)
        ->patchJson("/api/v1/bookings/{$bookingId}/status", ['status' => 'confirmed'])
        ->assertStatus(403)
        ->assertJsonPath('error_code', 'FORBIDDEN');
});

it('rejects an invalid booking status transition', function () {
    [$provider, , , , $serviceLocation] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($serviceLocation->location);
    $customer = BookingFixtures::customer();
    $customerToken = (new GenerateTokenAction)->execute($customer, 'auth_token');
    $providerToken = (new GenerateTokenAction)->execute($provider, 'auth_token');
    $date = now()->addDays(3)->toDateString();

    $slot = BookingFixtures::slot($serviceLocation, $date, '10:00', 60);

    $bookingId = bookingAs($this, $customerToken)
        ->withHeaders(['Idempotency-Key' => 'bkey-invalid'])
        ->postJson('/api/v1/bookings', ['booking_slot_id' => $slot->id])
        ->assertCreated()->json('data.id');

    bookingAs($this, $providerToken)
        ->patchJson("/api/v1/bookings/{$bookingId}/status", ['status' => 'confirmed'])
        ->assertOk();

    bookingAs($this, $providerToken)
        ->patchJson("/api/v1/bookings/{$bookingId}/status", ['status' => 'pending'])
        ->assertStatus(409)
        ->assertJsonPath('error_code', 'BOOKING_STATUS_TRANSITION_NOT_ALLOWED');
});

it('lets only the owner or the provider view a booking', function () {
    [$provider, , , , $serviceLocation] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($serviceLocation->location);
    $customer = BookingFixtures::customer();
    $otherCustomer = BookingFixtures::customer();
    $customerToken = (new GenerateTokenAction)->execute($customer, 'auth_token');
    $otherToken = (new GenerateTokenAction)->execute($otherCustomer, 'auth_token');
    $providerToken = (new GenerateTokenAction)->execute($provider, 'auth_token');
    $date = now()->addDays(3)->toDateString();

    $slot = BookingFixtures::slot($serviceLocation, $date, '10:00', 60);

    $bookingId = bookingAs($this, $customerToken)
        ->withHeaders(['Idempotency-Key' => 'bkey-show'])
        ->postJson('/api/v1/bookings', ['booking_slot_id' => $slot->id])
        ->assertCreated()->json('data.id');

    bookingAs($this, $customerToken)->getJson("/api/v1/bookings/{$bookingId}")
        ->assertOk()->assertJsonPath('data.id', $bookingId);

    bookingAs($this, $providerToken)->getJson("/api/v1/bookings/{$bookingId}")
        ->assertOk()->assertJsonPath('data.id', $bookingId);

    bookingAs($this, $otherToken)->getJson("/api/v1/bookings/{$bookingId}")
        ->assertStatus(403);
});
<?php

use App\Http\Middleware\VerifyApiCsrfToken;
use App\Modules\Auth\Actions\GenerateTokenAction;
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

function availabilityToken($user): string
{
    return (new GenerateTokenAction)->execute($user, 'auth_token');
}

it('requires authentication to check availability', function () {
    $this->getJson('/api/v1/availability?service_id=1&location_id=1&date=2026-10-10')
        ->assertStatus(401)
        ->assertJsonPath('error_code', 'UNAUTHENTICATED');
});

it('validates the availability query parameters', function () {
    $customer = BookingFixtures::customer();
    $token = availabilityToken($customer);

    $this->app['auth']->forgetGuards();

    $this->withToken($token)->getJson('/api/v1/availability')
        ->assertStatus(422)
        ->assertJsonPath('error_code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['service_id', 'location_id', 'date']]);
});

it('rejects an invalid date format', function () {
    [, , , $service, $serviceLocation] = BookingFixtures::stack();
    $customer = BookingFixtures::customer();
    $token = availabilityToken($customer);

    $this->app['auth']->forgetGuards();

    $this->withToken($token)->getJson('/api/v1/availability?service_id='.$service->id.'&location_id='.$serviceLocation->location_id.'&date=10-10-2026')
        ->assertStatus(422)
        ->assertJsonPath('error_code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['date']]);
});

it('returns no availability when the service is not offered at the location', function () {
    [, , , $service] = BookingFixtures::stack();
    $other = BookingFixtures::location(BookingFixtures::provider(), ['name' => 'Other Studio']);

    $customer = BookingFixtures::customer();
    $token = availabilityToken($customer);

    $this->app['auth']->forgetGuards();

    // The service is attached to a different location; $other has no link.
    $this->withToken($token)->getJson('/api/v1/availability?service_id='.$service->id.'&location_id='.$other->id.'&date='.now()->addDays(2)->toDateString())
        ->assertStatus(404)
        ->assertJsonPath('error_code', 'ROUTE_NOT_FOUND');
});

it('returns a slot and removes it once booked', function () {
    [, , , $service, $serviceLocation] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($serviceLocation->location);
    $customer = BookingFixtures::customer();
    $token = availabilityToken($customer);
    $date = now()->addDays(3)->toDateString();

    $this->app['auth']->forgetGuards();

    $response = $this->withToken($token)->getJson(
        '/api/v1/availability?service_id='.$service->id.'&location_id='.$serviceLocation->location_id.'&date='.$date
    );

    $response->assertOk();
    $response->assertJsonPath('data.date', $date);
    $slots = $response->json('data.slots');
    $this->assertNotEmpty($slots, 'Expected generated slots for an open day.');
    expect(count($slots))->toBe(8);

    $slotId = $slots[0]['id'];

    $bookingKey = 'avail-'.Str::uuid()->toString();
    $booking = $this->withToken($token)
        ->withHeaders(['Idempotency-Key' => $bookingKey])
        ->postJson('/api/v1/bookings', ['booking_slot_id' => $slotId]);

    $booking->assertCreated();

    $this->assertDatabaseHas('bookings', ['id' => $booking->json('data.id'), 'status' => 'pending']);
    $this->assertDatabaseHas('booking_status_logs', [
        'booking_id' => $booking->json('data.id'),
        'from_status' => null,
        'to_status' => 'pending',
    ]);

    $after = $this->withToken($token)->getJson(
        '/api/v1/availability?service_id='.$service->id.'&location_id='.$serviceLocation->location_id.'&date='.$date
    );

    $after->assertOk();
    expect(count($after->json('data.slots')))->toBe(7);
    expect(collect($after->json('data.slots'))->pluck('id'))->not->toContain($slotId);
});
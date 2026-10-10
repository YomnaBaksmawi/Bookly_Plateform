<?php

use App\Http\Middleware\VerifyApiCsrfToken;
use App\Modules\Auth\Actions\GenerateTokenAction;
use App\Modules\Service\Models\Service;
use App\Modules\WorkingHour\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Support\BookingFixtures;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([VerifyApiCsrfToken::class]);
    Cache::flush();
});

function providerAs($test, string $token)
{
    \Illuminate\Support\Facades\Auth::forgetGuards();

    return $test->withToken($token);
}

function providerTokenFor($user): string
{
    return (new GenerateTokenAction)->execute($user, 'auth_token');
}

it('requires a search term to search services', function () {
    $this->getJson('/api/v1/services/search')
        ->assertStatus(422)
        ->assertJsonPath('error_code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['q']]);
});

it('finds a service through the public search endpoint', function () {
    [, , , $service] = BookingFixtures::stack();

    $response = $this->getJson('/api/v1/services/search?q=massage')
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(collect($response->json('data'))->pluck('id'))->toContain($service->id);
});

it('lists services publicly', function () {
    [, , , $service] = BookingFixtures::stack();

    $this->getJson('/api/v1/services')
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('creates a service for the provider', function () {
    [$provider, $category, $location] = BookingFixtures::stack();
    $token = providerTokenFor($provider);

    providerAs($this, $token)->postJson('/api/v1/services', [
        'category_id' => $category->id,
        'name' => 'Hot Stone Therapy',
        'duration' => 90,
        'price' => 150,
        'status' => 'active',
        'location_ids' => [$location->id],
    ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Hot Stone Therapy')
        ->assertJsonPath('data.duration', 90);

    $this->assertDatabaseHas('services', ['provider_id' => $provider->id, 'name' => 'Hot Stone Therapy', 'price' => 150]);

    $created = Service::where('name', 'Hot Stone Therapy')->firstOrFail();
    expect($created->locations()->pluck('locations.id')->toArray())->toContain($location->id);
});

it('rejects a provider linking another providers location to their service', function () {
    $provider = BookingFixtures::provider();
    $category = BookingFixtures::category();
    $foreignLocation = BookingFixtures::location(BookingFixtures::provider());
    $token = providerTokenFor($provider);

    providerAs($this, $token)->postJson('/api/v1/services', [
        'category_id' => $category->id,
        'name' => 'Sneaky Service',
        'duration' => 60,
        'price' => 100,
        'location_ids' => [$foreignLocation->id],
    ])
        ->assertStatus(403)
        ->assertJsonPath('error_code', 'FORBIDDEN');
});

it('does not let a customer create services', function () {
    $customer = BookingFixtures::customer();
    $category = BookingFixtures::category();
    $provider = BookingFixtures::provider();
    $location = BookingFixtures::location($provider);
    $token = providerTokenFor($customer);

    providerAs($this, $token)->postJson('/api/v1/services', [
        'category_id' => $category->id,
        'name' => 'Customer Service',
        'duration' => 60,
        'price' => 100,
        'location_ids' => [$location->id],
    ])->assertStatus(403);
});

it('creates working hours for a provider location', function () {
    [$provider, , $location] = BookingFixtures::stack();
    $token = providerTokenFor($provider);

    providerAs($this, $token)->postJson('/api/v1/working-hours', [
        'location_id' => $location->id,
        'day_of_week' => 'monday',
        'start_time' => '09:00',
        'end_time' => '17:00',
    ])
        ->assertCreated()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('working_hours', [
        'location_id' => $location->id,
        'day_of_week' => 'monday',
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);
});

it('rejects overlapping working hours', function () {
    [$provider, , $location] = BookingFixtures::stack();
    WorkingHour::create([
        'location_id' => $location->id,
        'day_of_week' => 'monday',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
        'is_active' => true,
    ]);
    $token = providerTokenFor($provider);

    providerAs($this, $token)->postJson('/api/v1/working-hours', [
        'location_id' => $location->id,
        'day_of_week' => 'monday',
        'start_time' => '10:00',
        'end_time' => '14:00',
    ])
        ->assertStatus(409)
        ->assertJsonPath('error_code', 'WORKING_HOUR_OVERLAP');
});

it('rejects working hours for another providers location', function () {
    $provider = BookingFixtures::provider();
    $foreignLocation = BookingFixtures::location(BookingFixtures::provider());
    $token = providerTokenFor($provider);

    providerAs($this, $token)->postJson('/api/v1/working-hours', [
        'location_id' => $foreignLocation->id,
        'day_of_week' => 'tuesday',
        'start_time' => '09:00',
        'end_time' => '17:00',
    ])
        ->assertStatus(403)
        ->assertJsonPath('error_code', 'FORBIDDEN');
});

it('creates a schedule exception for a provider location', function () {
    [$provider, , $location] = BookingFixtures::stack();
    $token = providerTokenFor($provider);
    $date = now()->addDays(10)->toDateString();

    providerAs($this, $token)->postJson('/api/v1/schedule-exceptions', [
        'location_id' => $location->id,
        'date' => $date,
        'type' => 'custom_hours',
        'start_time' => '12:00',
        'end_time' => '14:00',
        'reason' => 'Special opening',
    ])
        ->assertCreated()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('schedule_exceptions', [
        'location_id' => $location->id,
        'date' => $date,
        'type' => 'custom_hours',
    ]);
});

it('rejects a duplicate schedule exception for the same location and date', function () {
    [$provider, , $location] = BookingFixtures::stack();
    $token = providerTokenFor($provider);
    $date = now()->addDays(11)->toDateString();

    providerAs($this, $token)->postJson('/api/v1/schedule-exceptions', [
        'location_id' => $location->id,
        'date' => $date,
        'type' => 'holiday',
    ])->assertCreated();

    providerAs($this, $token)->postJson('/api/v1/schedule-exceptions', [
        'location_id' => $location->id,
        'date' => $date,
        'type' => 'custom_hours',
        'start_time' => '10:00',
        'end_time' => '12:00',
    ])
        ->assertStatus(409)
        ->assertJsonPath('error_code', 'SCHEDULE_EXCEPTION_ALREADY_EXISTS');
});

it('requires a provider to scope a public holiday to their location', function () {
    $provider = BookingFixtures::provider();
    $token = providerTokenFor($provider);

    providerAs($this, $token)->postJson('/api/v1/public-holidays', [
        'name' => 'National Day',
        'date' => now()->addDays(20)->toDateString(),
        'country' => 'EG',
    ])
        ->assertStatus(403)
        ->assertJsonPath('error_code', 'FORBIDDEN');
});

it('creates a location-scoped public holiday', function () {
    [$provider, , $location] = BookingFixtures::stack();
    $token = providerTokenFor($provider);

    providerAs($this, $token)->postJson('/api/v1/public-holidays', [
        'name' => 'Studio Closed',
        'date' => now()->addDays(21)->toDateString(),
        'country' => 'EG',
        'location_id' => $location->id,
    ])
        ->assertCreated()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('public_holidays', [
        'location_id' => $location->id,
        'name' => 'Studio Closed',
    ]);
});

it('rejects a duplicate public holiday for the same date, country and location', function () {
    [$provider, , $location] = BookingFixtures::stack();
    $token = providerTokenFor($provider);
    $date = now()->addDays(22)->toDateString();

    providerAs($this, $token)->postJson('/api/v1/public-holidays', [
        'name' => 'Holiday One',
        'date' => $date,
        'country' => 'EG',
        'location_id' => $location->id,
    ])->assertCreated();

    providerAs($this, $token)->postJson('/api/v1/public-holidays', [
        'name' => 'Holiday Two',
        'date' => $date,
        'country' => 'EG',
        'location_id' => $location->id,
    ])
        ->assertStatus(409)
        ->assertJsonPath('error_code', 'PUBLIC_HOLIDAY_ALREADY_EXISTS');
});

it('creates a location for a provider', function () {
    $provider = BookingFixtures::provider();
    $token = providerTokenFor($provider);

    providerAs($this, $token)->postJson('/api/v1/locations', [
        'name' => 'New Studio',
        'address' => 'Nile Street 10',
        'city' => 'Cairo',
        'country' => 'EG',
        'latitude' => 30.0444,
        'longitude' => 31.2357,
        'timezone' => 'Africa/Cairo',
        'is_active' => true,
    ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'New Studio');
});

it('lets a provider create and view their profile', function () {
    $provider = BookingFixtures::provider();
    $token = providerTokenFor($provider);

    providerAs($this, $token)->postJson('/api/v1/provider/profile', [
        'business_name' => 'Downtown Wellness Co.',
        'bio' => 'Massage specialists.',
        'phone' => '+20100000000',
        'timezone' => 'Africa/Cairo',
    ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.business_name', 'Downtown Wellness Co.');

    providerAs($this, $token)->getJson('/api/v1/provider/profile')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.business_name', 'Downtown Wellness Co.');
});
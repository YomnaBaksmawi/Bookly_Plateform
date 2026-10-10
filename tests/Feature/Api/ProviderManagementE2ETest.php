<?php

use App\Http\Middleware\VerifyApiCsrfToken;
use App\Models\User;
use App\Modules\BookingSlot\Models\BookingSlot;
use App\Modules\Location\Models\Location;
use App\Modules\Service\Models\Service;
use App\Modules\ServiceLocation\Models\ServiceLocation;
use App\Modules\WorkingHour\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([VerifyApiCsrfToken::class]);
});

it('lets a provider register, configure a bookable service at a location, and expose available slots', function () {
    // ---------------------------------------------------------------
    // Step 1: Register the provider through the real auth API
    // ---------------------------------------------------------------
    $email = 'provider-'.Str::uuid()->toString().'@example.com';
    $password = 'Password123';

    $register = $this->postJson('/api/v1/auth/register', [
        'name' => 'Provider Owner',
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $password,
        'role' => 'provider',
    ]);

    $register->assertCreated();
    $register->assertJsonPath('user.email', $email);
    $register->assertJsonPath('user.role', 'provider');
    $register->assertJsonPath('user.status', 'active');
    $register->assertJsonPath('token_type', 'Bearer');

    $this->assertNotEmpty($register->json('access_token'));

    $providerId = $register->json('user.id');

    $this->assertDatabaseHas('users', [
        'id' => $providerId,
        'email' => $email,
        'role' => 'provider',
        'status' => 'active',
    ]);

    // ---------------------------------------------------------------
    // Step 2: Log in as the provider and keep using the bearer token
    // ---------------------------------------------------------------
    $login = $this->postJson('/api/v1/auth/login', [
        'email' => $email,
        'password' => $password,
    ]);

    $login->assertOk();
    $login->assertJsonPath('user.id', $providerId);
    $login->assertJsonPath('user.role', 'provider');

    $this->assertNotEmpty($login->json('access_token'));

    $token = $login->json('access_token');

    $this->app['auth']->guard('web')->logout();

    $this->withToken($token)->getJson('/api/v1/auth/profile')
        ->assertOk()
        ->assertJsonPath('user.id', $providerId)
        ->assertJsonPath('user.role', 'provider');
    $this->assertNotEmpty($login->json('access_token'));

    // ---------------------------------------------------------------
    // Step 3: The provider creates a category
    // ---------------------------------------------------------------
    $categorySlug = 'spa-'.Str::uuid()->toString();

    $createCategory = $this->withToken($token)
        ->postJson('/api/v1/categories', [
            'name' => 'Spa & Wellness',
            'slug' => $categorySlug,
            'description' => 'Spa and wellness services',
        ]);

    $createCategory->assertCreated();
    $createCategory->assertJsonPath('data.name', 'Spa & Wellness');
    $createCategory->assertJsonPath('data.slug', $categorySlug);

    $categoryId = $createCategory->json('data.id');

    $this->assertDatabaseHas('categories', [
        'id' => $categoryId,
        'slug' => $categorySlug,
    ]);

    // ---------------------------------------------------------------
    // Step 4: The provider creates a location
    // ---------------------------------------------------------------
    $createLocation = $this->withToken($token)
        ->postJson('/api/v1/locations', [
            'name' => 'Downtown Studio',
            'address' => 'Main Street 42',
            'city' => 'Cairo',
            'country' => 'EG',
            'lat' => 30.0444,
            'lng' => 31.2357,
            'timezone' => 'Africa/Cairo',
            'is_primary' => true,
            'is_active' => true,
        ]);

    $createLocation->assertCreated();
    $createLocation->assertJsonPath('data.provider_id', $providerId);
    $createLocation->assertJsonPath('data.name', 'Downtown Studio');
    $createLocation->assertJsonPath('data.city', 'Cairo');
    $createLocation->assertJsonPath('data.latitude', '30.0444000');
    $createLocation->assertJsonPath('data.longitude', '31.2357000');
    $createLocation->assertJsonPath('data.is_primary', true);
    $createLocation->assertJsonPath('data.is_active', true);

    $locationId = $createLocation->json('data.id');

    $this->assertDatabaseHas('locations', [
        'id' => $locationId,
        'provider_id' => $providerId,
        'is_primary' => true,
        'is_active' => true,
    ]);

    $this->assertSame('30.0444000', Location::findOrFail($locationId)->lat);
    $this->assertSame('31.2357000', Location::findOrFail($locationId)->lng);

    // ---------------------------------------------------------------
    // Step 5: The provider creates a service linked to the category
    // ---------------------------------------------------------------
    $createService = $this->withToken($token)
        ->postJson('/api/v1/services', [
            'category_id' => $categoryId,
            'name' => 'Deep Tissue Massage',
            'description' => 'Relaxing deep tissue massage session',
            'duration' => 60,
            'price' => 150,
            'status' => 'active',
            'location_ids' => [$locationId],
        ]);

    $createService->assertCreated();

    $service = $createService->json('data');

    $this->assertSame($providerId, $service['provider_id']);
    $this->assertSame($categoryId, $service['category_id']);
    $this->assertSame('Deep Tissue Massage', $service['name']);
    $this->assertSame(60, $service['duration']);
    $this->assertSame('150.00', $service['price']);
    $this->assertSame('active', $service['status']);
    $this->assertSame([$locationId], $service['location_ids']);

    $serviceId = $service['id'];

    $this->assertDatabaseHas('services', [
        'id' => $serviceId,
        'provider_id' => $providerId,
        'category_id' => $categoryId,
        'duration' => 60,
        'price' => 150,
        'status' => 'active',
    ]);

    // ---------------------------------------------------------------
    // Step 6: The service is attached to the location through the pivot
    // ---------------------------------------------------------------
    $serviceLocation = ServiceLocation::query()
        ->where('service_id', $serviceId)
        ->where('location_id', $locationId)
        ->firstOrFail();

    $this->assertDatabaseHas('service_locations', [
        'service_id' => $serviceId,
        'location_id' => $locationId,
    ]);

    $this->assertTrue(
        Service::findOrFail($serviceId)->locations()->where('locations.id', $locationId)->exists()
    );

    // ---------------------------------------------------------------
    // Step 7: The provider defines working hours for the location
    // ---------------------------------------------------------------
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    foreach ($days as $day) {
        $this->withToken($token)
            ->postJson('/api/v1/working-hours', [
                'location_id' => $locationId,
                'day_of_week' => $day,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.location_id', $locationId)
            ->assertJsonPath('data.day_of_week', $day)
            ->assertJsonPath('data.is_active', true);
    }

    $this->assertDatabaseCount('working_hours', 7);
    $this->assertSame(7, WorkingHour::where('location_id', $locationId)->count());

    // ---------------------------------------------------------------
    // Step 8: A future date yields bookable slots for service + location
    // ---------------------------------------------------------------
    $bookingDate = now()->utc()->addDays(2)->toDateString();

    $availability = $this->withToken($token)
        ->getJson('/api/v1/availability?service_id='.$serviceId.'&location_id='.$locationId.'&date='.$bookingDate);

    $availability->assertOk();
    $availability->assertJsonPath('data.service.id', $serviceId);
    $availability->assertJsonPath('data.service.name', 'Deep Tissue Massage');
    $availability->assertJsonPath('data.service.duration', 60);
    $availability->assertJsonPath('data.service.price', '150.00');
    $availability->assertJsonPath('data.location.id', $locationId);
    $availability->assertJsonPath('data.location.name', 'Downtown Studio');
    $availability->assertJsonPath('data.location.city', 'Cairo');
    $availability->assertJsonPath('data.date', $bookingDate);

    $slots = $availability->json('data.slots');

    $this->assertNotEmpty($slots, 'Expected at least one available slot.');

    $slotIds = collect($slots)->pluck('id')->all();

    foreach ($slotIds as $slotId) {
        $slot = BookingSlot::findOrFail($slotId);

        $this->assertSame('available', $slot->status);
        $this->assertSame($serviceLocation->id, $slot->service_location_id);
        $this->assertSame($bookingDate, $slot->date->toDateString());

        $duration = Carbon::parse($slot->start_time)->diffInMinutes(Carbon::parse($slot->end_time));
        $this->assertSame(60, (int) $duration);
    }

    $this->assertSame(BookingSlot::where('service_location_id', $serviceLocation->id)
        ->whereDate('date', $bookingDate)
        ->where('status', 'available')
        ->count(), count($slotIds));

    // ---------------------------------------------------------------
    // Step 9: Whole workflow is persisted and fully owned by this provider
    // ---------------------------------------------------------------
    $provider = User::findOrFail($providerId);
    $otherProvider = User::factory()->create([
        'role' => 'provider',
        'status' => 'active',
    ]);

    $persistedService = Service::with(['provider', 'category', 'locations'])->findOrFail($serviceId);
    $persistedLocation = Location::with('provider')->findOrFail($locationId);

    $this->assertTrue($persistedService->provider->is($provider));
    $this->assertSame($categoryId, $persistedService->category->id);
    $this->assertTrue($persistedService->locations->pluck('id')->contains($locationId));
    $this->assertTrue($persistedLocation->provider->is($provider));
    $this->assertSame(7, $persistedLocation->workingHours()->where('is_active', true)->count());

    $this->assertSame(1, Service::where('provider_id', $providerId)->count());
    $this->assertSame(1, Location::where('provider_id', $providerId)->count());
    $this->assertSame(1, ServiceLocation::where('service_id', $serviceId)->where('location_id', $locationId)->count());

    $this->assertSame(0, Service::where('provider_id', $otherProvider->id)->count());
    $this->assertSame(0, Location::where('provider_id', $otherProvider->id)->count());
});

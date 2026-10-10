<?php

use App\Http\Middleware\VerifyApiCsrfToken;
use App\Models\User;
use App\Modules\Auth\Actions\GenerateTokenAction;
use App\Modules\BookingSlot\Models\BookingSlot;
use App\Modules\Category\Models\Category;
use App\Modules\Location\Models\Location;
use App\Modules\Service\Models\Service;
use App\Modules\ServiceLocation\Models\ServiceLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([VerifyApiCsrfToken::class]);
});

it('checks public health and docs endpoints', function () {
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'status',
            'checks',
        ]);

    $this->getJson('/api/v1/docs')
        ->assertOk()
        ->assertJsonPath('version', 'v1');
});

it('registers and logs in a customer through the auth api', function () {
    $register = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
        'role' => 'customer',
    ]);

    $register->assertCreated();
    $register->assertJsonPath('user.email', 'customer@example.com');

    $login = $this->postJson('/api/v1/auth/login', [
        'email' => 'customer@example.com',
        'password' => 'Password123',
    ]);

    $login->assertOk();
    $login->assertJsonPath('user.email', 'customer@example.com');
    $this->assertNotEmpty($login->json('access_token'));
});

it('exposes categories through the api for authenticated users', function () {
    $provider = User::factory()->create([
        'role' => 'provider',
        'status' => 'active',
    ]);

    $token = (new GenerateTokenAction)->execute($provider, 'auth_token');

    $this->withToken($token);

    $create = $this->postJson('/api/v1/categories', [
        'name' => 'General',
        'slug' => 'general',
        'description' => 'General services',
    ]);

    $create->assertCreated();
    $create->assertJsonPath('data.name', 'General');

    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'General');
});

it('creates and lists services through the api', function () {
    $provider = User::factory()->create([
        'role' => 'provider',
        'status' => 'active',
    ]);
    $category = Category::create([
        'name' => 'Beauty',
        'slug' => 'beauty',
        'description' => 'Beauty services',
    ]);

    $locationA = Location::create([
        'provider_id' => $provider->id,
        'name' => 'Downtown',
        'address' => 'Main Street 1',
        'city' => 'Cairo',
        'country' => 'EG',
        'timezone' => 'Africa/Cairo',
        'is_primary' => true,
        'is_active' => true,
    ]);

    $locationB = Location::create([
        'provider_id' => $provider->id,
        'name' => 'City Center',
        'address' => 'Main Street 2',
        'city' => 'Cairo',
        'country' => 'EG',
        'timezone' => 'Africa/Cairo',
        'is_primary' => false,
        'is_active' => true,
    ]);

    $token = (new GenerateTokenAction)->execute($provider, 'auth_token');

    $this->withToken($token);

    $create = $this->postJson('/api/v1/services', [
        'category_id' => $category->id,
        'name' => 'Hair Cut',
        'description' => 'Classic haircut',
        'duration' => 60,
        'price' => 150,
        'status' => 'active',
        'location_ids' => [$locationA->id, $locationB->id],
    ]);

    $create->assertCreated();
    $create->assertJsonPath('data.name', 'Hair Cut');

    $this->getJson('/api/v1/services')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Hair Cut');
});

it('creates and lists locations through the api', function () {
    $provider = User::factory()->create([
        'role' => 'provider',
        'status' => 'active',
    ]);

    $token = $provider->createToken('test-token', [
        'service:read',
        'service:write',
        'location:read',
        'location:write',
        'booking:read',
        'booking:write',
        'category:read',
        'category:write',
    ])->plainTextToken;

    $this->withToken($token);
    $create = $this->postJson('/api/v1/locations', [
        'name' => 'Branch West',
        'address' => 'West Avenue 77',
        'city' => 'Alexandria',
        'country' => 'EG',
        'timezone' => 'Africa/Cairo',
        'is_primary' => true,
        'is_active' => true,
    ]);

    $create->assertCreated();
    $create->assertJsonPath('data.name', 'Branch West');

    $this->getJson('/api/v1/locations')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Branch West');
});

it('creates a booking for a customer via the api', function () {
    $provider = User::factory()->create(['role' => 'provider']);
    $customer = User::factory()->create(['role' => 'customer']);

    $category = Category::create([
        'name' => 'Spa',
        'slug' => 'spa',
        'description' => 'Spa services',
    ]);

    $location = Location::create([
        'provider_id' => $provider->id,
        'name' => 'Spa Center',
        'address' => 'Spa Street 12',
        'city' => 'Cairo',
        'country' => 'EG',
        'timezone' => 'Africa/Cairo',
        'is_primary' => true,
        'is_active' => true,
    ]);

    $service = Service::create([
        'provider_id' => $provider->id,
        'category_id' => $category->id,
        'name' => 'Massage Therapy',
        'description' => 'Full body massage',
        'duration' => 90,
        'price' => 300,
        'status' => 'active',
    ]);

    $serviceLocation = ServiceLocation::create([
        'service_id' => $service->id,
        'location_id' => $location->id,
    ]);

    $slot = BookingSlot::create([
        'service_location_id' => $serviceLocation->id,
        'date' => now()->addDay()->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '11:30:00',
        'status' => 'available',
    ]);

    $this->actingAs($customer, 'sanctum');

    $booking = $this->withHeaders([
        'Idempotency-Key' => 'booking-'.uniqid(),
    ])->postJson('/api/v1/bookings', [
        'booking_slot_id' => $slot->id,
        'notes' => 'Prefer afternoon slot',
    ]);

    $booking->assertCreated();
    $booking->assertJsonPath('data.customer_id', $customer->id);
    $this->assertDatabaseHas('bookings', ['customer_id' => $customer->id, 'booking_slot_id' => $slot->id]);
});

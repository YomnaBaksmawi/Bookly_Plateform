<?php

use App\Models\User;
use App\Modules\Location\Actions\CreateLocationAction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('customers cannot create locations through the action', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    expect(fn () => app(CreateLocationAction::class)->execute($customer, [
        'name' => 'Customer Location',
    ]))->toThrow(AuthorizationException::class);

    $this->assertDatabaseCount('locations', 0);
});

test('providers can create locations through the action', function () {
    $provider = User::factory()->create(['role' => 'provider']);

    $location = app(CreateLocationAction::class)->execute($provider, [
        'name' => 'Provider Location',
        'address' => 'Main Street 1',
        'city' => 'Cairo',
        'country' => 'EG',
        'timezone' => 'Africa/Cairo',
    ]);

    expect($location->provider_id)->toBe($provider->id);
    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
        'provider_id' => $provider->id,
        'name' => 'Provider Location',
    ]);
});

<?php

use App\Modules\Availability\Services\AvailabilityService;
use App\Modules\BookingSlot\Models\BookingSlot;
use App\Modules\ServiceLocation\Models\ServiceLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Support\BookingFixtures;

uses(TestCase::class, RefreshDatabase::class);

function availabilityCacheKey(ServiceLocation $serviceLocation, string $date): string
{
    return "availability:{$serviceLocation->service_id}:{$serviceLocation->location_id}:{$date}";
}

it('returns every generated slot on an open day', function () {
    config(['cache.default' => 'array']);
    [, , , $service, $serviceLocation] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($serviceLocation->location);
    $date = now()->addDays(2)->toDateString();

    $slots = app(AvailabilityService::class)->getSlots($service, $serviceLocation->location, $date);

    expect(count($slots))->toBe(8);
    expect($slots[0]['start_at'])->toBeString();
    expect($slots[0]['end_at'])->toBeString();
    expect($slots[0]['id'])->toBeInt();
});

it('hides booked slots from the available list', function () {
    config(['cache.default' => 'array']);
    [, , , $service, $serviceLocation] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($serviceLocation->location);
    $date = now()->addDays(2)->toDateString();

    $availability = app(AvailabilityService::class);
    $first = $availability->getSlots($service, $serviceLocation->location, $date);

    BookingSlot::findOrFail($first[3]['id'])->update(['status' => 'booked']);
    Cache::forget(availabilityCacheKey($serviceLocation, $date));

    $available = $availability->getSlots($service, $serviceLocation->location, $date);

    expect(count($available))->toBe(7);
    expect(collect($available)->pluck('id'))->not->toContain($first[3]['id']);
});

it('serves a cached payload until the availability cache is invalidated', function () {
    config(['cache.default' => 'array']);
    [, , , $service, $serviceLocation] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($serviceLocation->location);
    $date = now()->addDays(2)->toDateString();

    $availability = app(AvailabilityService::class);

    $first = $availability->getSlots($service, $serviceLocation->location, $date);
    expect(count($first))->toBe(8);

    // The slot is booked underneath, but the cached payload is still served.
    BookingSlot::findOrFail($first[3]['id'])->update(['status' => 'blocked']);
    $cached = $availability->getSlots($service, $serviceLocation->location, $date);
    expect(count($cached))->toBe(8);

    // Only an explicit invalidation exposes the change.
    Cache::forget(availabilityCacheKey($serviceLocation, $date));
    $fresh = $availability->getSlots($service, $serviceLocation->location, $date);
    expect(count($fresh))->toBe(7);
});
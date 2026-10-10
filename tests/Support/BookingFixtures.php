<?php

namespace Tests\Support;

use App\Models\User;
use App\Modules\BookingSlot\Models\BookingSlot;
use App\Modules\Category\Models\Category;
use App\Modules\Location\Models\Location;
use App\Modules\Service\Models\Service;
use App\Modules\ServiceLocation\Models\ServiceLocation;
use App\Modules\WorkingHour\Models\WorkingHour;
use Carbon\Carbon;

class BookingFixtures
{
    public static function customer(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'customer',
            'status' => 'active',
        ], $attributes));
    }

    public static function provider(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'provider',
            'status' => 'active',
        ], $attributes));
    }

    public static function category(): Category
    {
        return Category::create([
            'name' => 'Wellness',
            'slug' => 'wellness',
            'description' => 'Wellness services',
        ]);
    }

    public static function location(User $provider, array $attributes = []): Location
    {
        return Location::create(array_merge([
            'provider_id' => $provider->id,
            'name' => 'Downtown Studio',
            'address' => 'Main Street 42',
            'city' => 'Cairo',
            'country' => 'EG',
            'timezone' => 'UTC',
            'is_primary' => true,
            'is_active' => true,
        ], $attributes));
    }

    public static function service(User $provider, Category $category, array $attributes = []): Service
    {
        return Service::create(array_merge([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'name' => 'Deep Tissue Massage',
            'description' => 'A relaxing deep tissue massage session',
            'duration' => 60,
            'price' => 120,
            'status' => 'active',
        ], $attributes));
    }

    public static function serviceLocation(Service $service, Location $location): ServiceLocation
    {
        $serviceLocation = ServiceLocation::create([
            'service_id' => $service->id,
            'location_id' => $location->id,
        ]);

        $service->locations()->syncWithoutDetaching([$location->id]);

        return $serviceLocation;
    }

    public static function openEveryDay(Location $location, string $start = '09:00:00', string $end = '17:00:00'): void
    {
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            WorkingHour::create([
                'location_id' => $location->id,
                'day_of_week' => $day,
                'start_time' => $start,
                'end_time' => $end,
                'is_active' => true,
            ]);
        }
    }

    public static function slot(ServiceLocation $serviceLocation, string $date, string $start, int $duration, string $status = 'available'): BookingSlot
    {
        $startTime = Carbon::parse($start, 'UTC');

        return BookingSlot::create([
            'service_location_id' => $serviceLocation->id,
            'date' => $date,
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $startTime->copy()->addMinutes($duration)->format('H:i:s'),
            'status' => $status,
        ]);
    }

    /** @return array{0: User, 1: Category, 2: Location, 3: Service, 4: ServiceLocation} */
    public static function stack(): array
    {
        $provider = static::provider();
        $category = static::category();
        $location = static::location($provider);
        $service = static::service($provider, $category);
        $serviceLocation = static::serviceLocation($service, $location);

        return [$provider, $category, $location, $service, $serviceLocation];
    }
}
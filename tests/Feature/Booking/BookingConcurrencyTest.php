<?php

namespace Tests\Feature\Booking;

use App\Models\User;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Actions\CreateBookingAction;
use App\Modules\BookingSlot\Models\BookingSlot;
use App\Modules\Location\Models\Location;
use App\Modules\Service\Models\Service;
use App\Modules\ServiceLocation\Models\ServiceLocation;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Process\Process;
use App\Modules\Category\Models\Category;
use Tests\TestCase;

class BookingConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_two_customers_cannot_book_the_same_slot(): void
    {
        $customer1 = User::create([
            'name' => 'Customer One',
            'email' => 'customer1@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => 'active',
        ]);

        $customer2 = User::create([
            'name' => 'Customer Two',
            'email' => 'customer2@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => 'active',
        ]);

        $provider = User::create([
            'name' => 'Provider',
            'email' => 'provider@test.com',
            'password' => Hash::make('password'),
            'role' => 'provider',
            'status' => 'active',
        ]);

        $location = Location::create([
            'provider_id' => $provider->id,
            'name' => 'Test Location',
            'address' => 'Test Address',
            'city' => 'Test City',
            'country' => 'US',
            'timezone' => 'UTC',
            'is_primary' => true,
            'is_active' => true,
        ]);
$category = Category::create([
    'name' => 'Test Category',
    'slug' => 'test-category',
]);
        $service = Service::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'name' => 'Test Service',
            'description' => 'Concurrency Test',
            'duration' => 30,
            'price' => 20,
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
            'end_time' => '10:30:00',
            'status' => 'available',
        ]);

        $script = base_path('tests/Support/concurrent_booking.php');

        $process1 = new Process([
            PHP_BINARY,
            $script,
            $customer1->id,
            $slot->id,
        ], base_path());

        $process2 = new Process([
            PHP_BINARY,
            $script,
            $customer2->id,
            $slot->id,
        ], base_path());

        $process1->start();
        $process2->start();

        $process1->wait();
        $process2->wait();

        $this->assertTrue($process1->isSuccessful());
        $this->assertTrue($process2->isSuccessful());

        $this->assertSame(1, Booking::where('booking_slot_id', $slot->id)->count());

        $this->assertSame(  'booked', BookingSlot::find($slot->id)->status);
    }
}
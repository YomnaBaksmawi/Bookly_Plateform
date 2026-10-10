<?php

use App\Http\Middleware\VerifyApiCsrfToken;
use App\Models\User;
use App\Modules\Auth\Actions\GenerateTokenAction;
use App\Modules\Booking\Models\Booking;
use App\Modules\BookingSlot\Models\BookingSlot;
use App\Modules\BookingStatusLog\Models\BookingStatusLog;
use App\Modules\Category\Models\Category;
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

it('walks a booking through its full lifecycle with status logs and ownership rules', function () {
    // In tests the application container lives across requests, so the auth
    // guards retain the resolved user from previous requests. Reset the guards
    // before every request so the intended bearer token always wins.
    $as = function (string $token) {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token);
    };

    // ------------------------------------------------------------------
    // Fixtures: one provider, an unrelated provider, and two customers
    // ------------------------------------------------------------------
    $provider = User::factory()->create([
        'role' => 'provider',
        'status' => 'active',
    ]);

    $otherProvider = User::factory()->create([
        'role' => 'provider',
        'status' => 'active',
    ]);

    $category = Category::create([
        'name' => 'Wellness',
        'slug' => 'wellness',
        'description' => 'Wellness services',
    ]);

    $location = Location::create([
        'provider_id' => $provider->id,
        'name' => 'Downtown Studio',
        'address' => 'Main Street 42',
        'city' => 'Cairo',
        'country' => 'EG',
        'timezone' => 'UTC',
        'is_primary' => true,
        'is_active' => true,
    ]);

    $service = Service::create([
        'provider_id' => $provider->id,
        'category_id' => $category->id,
        'name' => 'Deep Tissue Massage',
        'description' => 'Relaxing deep tissue massage session',
        'duration' => 60,
        'price' => 150,
        'status' => 'active',
    ]);

    $serviceLocation = ServiceLocation::create([
        'service_id' => $service->id,
        'location_id' => $location->id,
    ]);

    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
        WorkingHour::create([
            'location_id' => $location->id,
            'day_of_week' => $day,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);
    }

    $providerToken = (new GenerateTokenAction)->execute($provider, 'auth_token');
    $otherProviderToken = (new GenerateTokenAction)->execute($otherProvider, 'auth_token');

    // ------------------------------------------------------------------
    // The main customer registers and logs in through the real auth flow
    // ------------------------------------------------------------------
    $email = 'customer-'.Str::uuid()->toString().'@example.com';
    $password = 'Password123';

    $register = $this->postJson('/api/v1/auth/register', [
        'name' => 'Lifecycle Customer',
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $password,
        'role' => 'customer',
    ]);

    $register->assertCreated();
    $register->assertJsonPath('user.role', 'customer');
    $register->assertJsonPath('user.status', 'active');
    $this->assertNotEmpty($register->json('access_token'));

    $customerId = $register->json('user.id');

    $login = $this->postJson('/api/v1/auth/login', [
        'email' => $email,
        'password' => $password,
    ]);

    $login->assertOk();
    $login->assertJsonPath('user.id', $customerId);
    $this->assertNotEmpty($login->json('access_token'));

    $customerToken = $login->json('access_token');

    $this->app['auth']->guard('web')->logout();

    $as($customerToken)->getJson('/api/v1/auth/profile')
        ->assertOk()
        ->assertJsonPath('user.id', $customerId);

    // ------------------------------------------------------------------
    // An unrelated customer used for the ownership checks
    // ------------------------------------------------------------------
    $otherCustomer = User::factory()->create([
        'role' => 'customer',
        'status' => 'active',
    ]);

    $otherCustomerToken = (new GenerateTokenAction)->execute($otherCustomer, 'auth_token');

    // ------------------------------------------------------------------
    // Generate real slots for two different future dates
    // ------------------------------------------------------------------
    $date1 = now()->utc()->addDays(2)->toDateString();
    $date2 = now()->utc()->addDays(3)->toDateString();

    $availabilityA = $as($customerToken)
        ->getJson('/api/v1/availability?service_id='.$service->id.'&location_id='.$location->id.'&date='.$date1);

    $availabilityA->assertOk();
    $this->assertNotEmpty($availabilityA->json('data.slots'));

    $availabilityB = $as($customerToken)
        ->getJson('/api/v1/availability?service_id='.$service->id.'&location_id='.$location->id.'&date='.$date2);

    $availabilityB->assertOk();
    $this->assertNotEmpty($availabilityB->json('data.slots'));

    $slotA = BookingSlot::findOrFail($availabilityA->json('data.slots.0.id'));
    $slotB = BookingSlot::findOrFail($availabilityB->json('data.slots.0.id'));

    $this->assertSame($serviceLocation->id, $slotA->service_location_id);
    $this->assertSame($serviceLocation->id, $slotB->service_location_id);

    // ------------------------------------------------------------------
    // Step 1: Customer creates the booking (pending)
    // ------------------------------------------------------------------
    $create = $as($customerToken)
        ->withHeaders(['Idempotency-Key' => 'booking-'.Str::uuid()->toString()])
        ->postJson('/api/v1/bookings', [
            'booking_slot_id' => $slotA->id,
            'notes' => 'Bring ID',
        ]);

    $create->assertCreated();
    $create->assertJsonPath('data.status', 'pending');
    $create->assertJsonPath('data.customer_id', $customerId);
    $create->assertJsonPath('data.price', '150.00');
    $create->assertJsonPath('data.service.id', $service->id);
    $create->assertJsonPath('data.location.id', $location->id);
    $create->assertJsonPath('data.date', $date1);
    $create->assertJsonPath('data.start_time', $slotA->start_time);
    $create->assertJsonPath('data.end_time', $slotA->end_time);

    $bookingId = $create->json('data.id');

    $this->assertDatabaseHas('bookings', [
        'id' => $bookingId,
        'customer_id' => $customerId,
        'service_location_id' => $serviceLocation->id,
        'booking_slot_id' => $slotA->id,
        'status' => 'pending',
    ]);

    $pending = Booking::findOrFail($bookingId);
    $this->assertTrue($pending->starts_at->equalTo(Carbon::parse("{$date1} {$slotA->start_time}", 'UTC')));
    $this->assertTrue($pending->ends_at->equalTo(Carbon::parse("{$date1} {$slotA->end_time}", 'UTC')));
    $this->assertSame('150.00', $pending->price_at_booking);
    $this->assertSame('Bring ID', $pending->notes);

    $this->assertSame('booked', BookingSlot::findOrFail($slotA->id)->status);

    // Booking creation records the initial status history (null -> pending).
    $this->assertDatabaseCount('booking_status_logs', 1);
    $this->assertDatabaseHas('booking_status_logs', [
        'booking_id' => $bookingId,
        'from_status' => null,
        'to_status' => 'pending',
        'changed_by' => $customerId,
        'reason' => null,
    ]);

    // ------------------------------------------------------------------
    // Step 2: Provider confirms the booking (pending -> confirmed)
    // ------------------------------------------------------------------
    $confirm = $as($providerToken)
        ->patchJson("/api/v1/bookings/{$bookingId}/status", [
            'status' => 'confirmed',
            'reason' => 'Provider confirmed the appointment.',
        ]);

    $confirm->assertOk();
    $confirm->assertJsonPath('data.status', 'confirmed');

    $this->assertDatabaseHas('bookings', [
        'id' => $bookingId,
        'status' => 'confirmed',
    ]);

    $this->assertSame('booked', BookingSlot::findOrFail($slotA->id)->status);

    $this->assertDatabaseCount('booking_status_logs', 2);
    $this->assertDatabaseHas('booking_status_logs', [
        'booking_id' => $bookingId,
        'from_status' => 'pending',
        'to_status' => 'confirmed',
        'changed_by' => $provider->id,
        'reason' => 'Provider confirmed the appointment.',
    ]);

    // ------------------------------------------------------------------
    // Step 3: An unrelated customer cannot read or mutate the booking
    // ------------------------------------------------------------------
    $as($otherCustomerToken)
        ->getJson("/api/v1/bookings/{$bookingId}")
        ->assertStatus(403)
        ->assertJsonPath('error_code', 'FORBIDDEN');

    $as($otherCustomerToken)
        ->patchJson("/api/v1/bookings/{$bookingId}/cancel", ['reason' => 'Not mine.'])
        ->assertStatus(403)
        ->assertJsonPath('error_code', 'FORBIDDEN');

    $as($otherCustomerToken)
        ->patchJson("/api/v1/bookings/{$bookingId}/status", ['status' => 'completed'])
        ->assertStatus(403)
        ->assertJsonPath('error_code', 'FORBIDDEN');

    // An unrelated provider cannot cancel either.
    $as($otherProviderToken)
        ->patchJson("/api/v1/bookings/{$bookingId}/cancel", ['reason' => 'Not my customer.'])
        ->assertStatus(403)
        ->assertJsonPath('error_code', 'FORBIDDEN');

    // The unrelated customer sees no bookings in their own list.
    $otherList = $as($otherCustomerToken)->getJson('/api/v1/bookings');

    $otherList->assertOk();
    $otherList->assertJsonPath('total', 0);

    // The owning provider still sees the booking.
    $providerList = $as($providerToken)->getJson('/api/v1/bookings');

    $providerList->assertOk();
    $providerList->assertJsonPath('total', 1);

    // ------------------------------------------------------------------
    // Step 4: Invalid transition confirmed -> pending is rejected
    // ------------------------------------------------------------------
    $invalidTransition = $as($providerToken)
        ->patchJson("/api/v1/bookings/{$bookingId}/status", ['status' => 'pending']);

    $invalidTransition->assertStatus(409);
    $invalidTransition->assertJsonPath('success', false);
    $invalidTransition->assertJsonPath('message', 'Cannot transition booking from confirmed to pending.');
    $invalidTransition->assertJsonPath('error_code', 'BOOKING_STATUS_TRANSITION_NOT_ALLOWED');
    $invalidTransition->assertJsonPath('errors', null);

    $this->assertDatabaseHas('bookings', [
        'id' => $bookingId,
        'status' => 'confirmed',
    ]);

    $this->assertDatabaseCount('booking_status_logs', 2);

    // ------------------------------------------------------------------
    // Step 5: Customer reschedules the confirmed booking to a new slot
    // ------------------------------------------------------------------
    $reschedule = $as($customerToken)
        ->patchJson("/api/v1/bookings/{$bookingId}/reschedule", [
            'booking_slot_id' => $slotB->id,
            'reason' => 'Prefer the later slot.',
        ]);

    $reschedule->assertOk();
    $reschedule->assertJsonPath('data.status', 'confirmed');
    $reschedule->assertJsonPath('data.date', $date2);
    $reschedule->assertJsonPath('data.start_time', $slotB->start_time);
    $reschedule->assertJsonPath('data.end_time', $slotB->end_time);

    $rescheduled = Booking::findOrFail($bookingId);
    $this->assertSame($slotB->id, $rescheduled->booking_slot_id);
    $this->assertSame($serviceLocation->id, $rescheduled->service_location_id);
    $this->assertTrue($rescheduled->starts_at->equalTo(Carbon::parse("{$date2} {$slotB->start_time}", 'UTC')));
    $this->assertTrue($rescheduled->ends_at->equalTo(Carbon::parse("{$date2} {$slotB->end_time}", 'UTC')));

    $this->assertSame('available', BookingSlot::findOrFail($slotA->id)->status);
    $this->assertSame('booked', BookingSlot::findOrFail($slotB->id)->status);

    // Rescheduling changes the appointment time but does not log a status change.
    $this->assertDatabaseCount('booking_status_logs', 2);

    // ------------------------------------------------------------------
    // Step 6: Rescheduling to an unavailable (already booked) slot is rejected
    // ------------------------------------------------------------------
    $unavailable = $as($customerToken)
        ->patchJson("/api/v1/bookings/{$bookingId}/reschedule", ['booking_slot_id' => $slotB->id]);

    $unavailable->assertStatus(409);
    $unavailable->assertJsonPath('error_code', 'BOOKING_SLOT_UNAVAILABLE');

    $this->assertDatabaseHas('bookings', [
        'id' => $bookingId,
        'booking_slot_id' => $slotB->id,
        'status' => 'confirmed',
    ]);

    $this->assertSame('booked', BookingSlot::findOrFail($slotB->id)->status);
    $this->assertDatabaseCount('booking_status_logs', 2);

    // ------------------------------------------------------------------
    // Step 7: Owner cancels the confirmed booking (confirmed -> cancelled)
    // ------------------------------------------------------------------
    $cancel = $as($customerToken)
        ->patchJson("/api/v1/bookings/{$bookingId}/cancel", ['reason' => 'Changed my plans.']);

    $cancel->assertOk();
    $cancel->assertJsonPath('data.status', 'cancelled');

    $this->assertDatabaseHas('bookings', [
        'id' => $bookingId,
        'status' => 'cancelled',
    ]);

    $this->assertSame('available', BookingSlot::findOrFail($slotB->id)->status);

    $this->assertDatabaseCount('booking_status_logs', 3);
    $this->assertDatabaseHas('booking_status_logs', [
        'booking_id' => $bookingId,
        'from_status' => 'confirmed',
        'to_status' => 'cancelled',
        'changed_by' => $customerId,
        'reason' => 'Changed my plans.',
    ]);

    // ------------------------------------------------------------------
    // Step 8: A cancelled booking is final and cannot be rescheduled
    // ------------------------------------------------------------------
    $finalBooking = $as($customerToken)
        ->patchJson("/api/v1/bookings/{$bookingId}/reschedule", ['booking_slot_id' => $slotA->id]);

    $finalBooking->assertStatus(409);
    $finalBooking->assertJsonPath('error_code', 'BOOKING_RESCHEDULING_NOT_ALLOWED');

    $this->assertDatabaseHas('bookings', [
        'id' => $bookingId,
        'booking_slot_id' => $slotB->id,
        'status' => 'cancelled',
    ]);

    $this->assertSame('available', BookingSlot::findOrFail($slotA->id)->status);
    $this->assertDatabaseCount('booking_status_logs', 3);

    // ------------------------------------------------------------------
    // Step 9: The status log history reflects each real operation
    // ------------------------------------------------------------------
    $logs = BookingStatusLog::query()
        ->where('booking_id', $bookingId)
        ->orderBy('id')
        ->get();

    $this->assertCount(3, $logs);

    $this->assertNull($logs[0]->from_status);
    $this->assertSame('pending', $logs[0]->to_status);
    $this->assertSame($customerId, $logs[0]->changed_by);
    $this->assertTrue($logs[0]->booking->is(Booking::findOrFail($bookingId)));
    $this->assertTrue($logs[0]->changedBy->is(User::findOrFail($customerId)));

    $this->assertSame('pending', $logs[1]->from_status);
    $this->assertSame('confirmed', $logs[1]->to_status);
    $this->assertSame($provider->id, $logs[1]->changed_by);
    $this->assertTrue($logs[1]->booking->is(Booking::findOrFail($bookingId)));
    $this->assertTrue($logs[1]->changedBy->is($provider));

    $this->assertSame('confirmed', $logs[2]->from_status);
    $this->assertSame('cancelled', $logs[2]->to_status);
    $this->assertSame($customerId, $logs[2]->changed_by);
    $this->assertTrue($logs[2]->booking->is(Booking::findOrFail($bookingId)));
    $this->assertTrue($logs[2]->changedBy->is(User::findOrFail($customerId)));
});

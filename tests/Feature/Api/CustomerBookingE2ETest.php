<?php

use App\Http\Middleware\VerifyApiCsrfToken;
use App\Models\User;
use App\Modules\Booking\Models\Booking;
use App\Modules\BookingSlot\Models\BookingSlot;
use App\Modules\Category\Models\Category;
use App\Modules\Location\Models\Location;
use App\Modules\Payment\Models\Payment;
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

    $this->webhookSecret = 'test-webhook-secret';
    config(['services.payment.webhook_secret' => $this->webhookSecret]);

    // Provider-side fixtures: an active provider, a category, an active location
    // and a bookable service linked to that location through the pivot table.
    $this->provider = User::factory()->create([
        'role' => 'provider',
        'status' => 'active',
    ]);

    $this->category = Category::create([
        'name' => 'Wellness',
        'slug' => 'wellness',
        'description' => 'Wellness services',
    ]);

    $this->location = Location::create([
        'provider_id' => $this->provider->id,
        'name' => 'Downtown Studio',
        'address' => 'Main Street 42',
        'city' => 'Cairo',
        'country' => 'EG',
        'timezone' => 'UTC',
        'is_primary' => true,
        'is_active' => true,
    ]);

    $this->service = Service::create([
        'provider_id' => $this->provider->id,
        'category_id' => $this->category->id,
        'name' => 'Deep Tissue Massage',
        'description' => 'A relaxing deep tissue massage session',
        'duration' => 60,
        'price' => 120,
        'status' => 'active',
    ]);

    $this->serviceLocation = ServiceLocation::create([
        'service_id' => $this->service->id,
        'location_id' => $this->location->id,
    ]);

    // Open the location every day so an available slot is generated for any chosen date.
    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
        WorkingHour::create([
            'location_id' => $this->location->id,
            'day_of_week' => $day,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);
    }

    // Booking date two days in the future (no schedule exceptions or public holidays exist).
    $this->bookingDate = now()->utc()->addDays(2)->toDateString();
});

it('completes a full customer booking and payment journey end to end', function () {
    // ---------------------------------------------------------------
    // Step 1: Customer registration
    // ---------------------------------------------------------------
    $email = 'e2e-'.Str::uuid()->toString().'@example.com';
    $password = 'Password123';

    $register = $this->postJson('/api/v1/auth/register', [
        'name' => 'E2E Customer',
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $password,
        'role' => 'customer',
    ]);

    $register->assertCreated();
    $register->assertJsonPath('user.email', $email);
    $register->assertJsonPath('user.role', 'customer');
    $register->assertJsonPath('user.status', 'active');
    $this->assertNotEmpty($register->json('access_token'));

    $this->assertDatabaseHas('users', [
        'email' => $email,
        'role' => 'customer',
        'status' => 'active',
    ]);

    $customerId = $register->json('user.id');

    // ---------------------------------------------------------------
    // Step 2: Customer login
    // ---------------------------------------------------------------
    $login = $this->postJson('/api/v1/auth/login', [
        'email' => $email,
        'password' => $password,
    ]);

    $login->assertOk();
    $login->assertJsonPath('user.id', $customerId);
    $login->assertJsonPath('user.email', $email);
    $this->assertNotEmpty($login->json('access_token'));

    $token = $login->json('access_token');

    // The app's login flow signs the customer into the default (web) guard.
    // In a stateless API context that never happens, but the in-process web
    // guard keeps the user for the whole test, forcing Sanctum to resolve a
    // TransientToken instead of the bearer token. Clearing it makes every
    // subsequent request authenticate purely through the API token.
    $this->app['auth']->guard('web')->logout();

    // The token must resolve to the registered customer.
    $this->withToken($token)->getJson('/api/v1/auth/profile')
        ->assertOk()
        ->assertJsonPath('user.id', $customerId)
        ->assertJsonPath('user.email', $email);

    // ---------------------------------------------------------------
    // Step 3: Search for a service
    // ---------------------------------------------------------------
    $search = $this->getJson('/api/v1/services/search?q=Massage&location_id='.$this->location->id);

    $search->assertOk();
    $search->assertJsonPath('success', true);

    $searchResults = collect($search->json('data'));
    $this->assertTrue($searchResults->contains('id', $this->service->id), 'The search did not return the bookable service.');

    $found = $searchResults->firstWhere('id', $this->service->id);
    $this->assertSame('active', $found['status']);
    $this->assertSame($this->provider->id, $found['provider_id']);

    // ---------------------------------------------------------------
    // Step 4: Select the service at its location
    // ---------------------------------------------------------------
    $serviceShow = $this->getJson('/api/v1/services/'.$this->service->id);

    $serviceShow->assertOk();
    $serviceShow->assertJsonPath('data.id', $this->service->id);
    $serviceShow->assertJsonPath('data.provider_id', $this->provider->id);
    $serviceShow->assertJsonPath('data.status', 'active');

    $locationShow = $this->getJson('/api/v1/locations/'.$this->location->id);

    $locationShow->assertOk();
    $locationShow->assertJsonPath('data.id', $this->location->id);
    $locationShow->assertJsonPath('data.provider_id', $this->provider->id);
    $locationShow->assertJsonPath('data.is_active', true);

    $this->assertTrue(
        $this->service->locations()->where('locations.id', $this->location->id)->exists(),
        'The service must be offered at the selected location.'
    );

    // ---------------------------------------------------------------
    // Step 5: Find an available slot
    // ---------------------------------------------------------------
    $availability = $this->withToken($token)
        ->getJson('/api/v1/availability?service_id='.$this->service->id.'&location_id='.$this->location->id.'&date='.$this->bookingDate);

    $availability->assertOk();
    $availability->assertJsonPath('data.service.id', $this->service->id);
    $availability->assertJsonPath('data.location.id', $this->location->id);
    $availability->assertJsonPath('data.date', $this->bookingDate);

    $slots = $availability->json('data.slots');
    $this->assertNotEmpty($slots, 'Expected at least one available slot');

    $slotId = $slots[0]['id'];

    $slot = BookingSlot::findOrFail($slotId);
    $this->assertSame('available', $slot->status);
    $this->assertSame($this->serviceLocation->id, $slot->service_location_id);
    $this->assertSame($this->bookingDate, $slot->date->toDateString());

    $slotDuration = Carbon::parse($slot->start_time)->diffInMinutes(Carbon::parse($slot->end_time));
    $this->assertSame($this->service->duration, (int) $slotDuration);

    // ---------------------------------------------------------------
    // Step 6: Create the booking
    // ---------------------------------------------------------------
    $bookingKey = 'booking-'.Str::uuid()->toString();

    $createBooking = $this->withToken($token)
        ->withHeaders(['Idempotency-Key' => $bookingKey])
        ->postJson('/api/v1/bookings', [
            'booking_slot_id' => $slotId,
            'notes' => 'Please confirm my appointment.',
        ]);

    $createBooking->assertCreated();
    $bookingId = $createBooking->json('data.id');
    $createBooking->assertJsonPath('data.status', 'pending');
    $createBooking->assertJsonPath('data.service.id', $this->service->id);
    $createBooking->assertJsonPath('data.location.id', $this->location->id);

    $this->assertDatabaseHas('bookings', [
        'id' => $bookingId,
        'customer_id' => $customerId,
        'booking_slot_id' => $slotId,
        'status' => 'pending',
    ]);

    $this->assertSame('booked', BookingSlot::findOrFail($slotId)->status);

    // ---------------------------------------------------------------
    // Step 7: Create / initiate the payment
    // ---------------------------------------------------------------
    $paymentKey = 'payment-'.Str::uuid()->toString();

    $createPayment = $this->withToken($token)
        ->withHeaders(['Idempotency-Key' => $paymentKey])
        ->postJson('/api/v1/payments', ['booking_id' => $bookingId]);

    $createPayment->assertCreated();
    $paymentId = $createPayment->json('data.id');
    $createPayment->assertJsonPath('data.booking_id', $bookingId);
    $this->assertSame('paid', $createPayment->json('data.status'));
    $this->assertNotEmpty($createPayment->json('data.transaction_id'));

    $transactionId = $createPayment->json('data.transaction_id');

    // ---------------------------------------------------------------
    // Step 8: Process the mock payment (provider webhook)
    // ---------------------------------------------------------------
    $eventId = 'evt_'.Str::uuid()->toString();

    $payload = json_encode([
        'id' => $eventId,
        'type' => 'payment.succeeded',
        'data' => [
            'transaction_id' => $transactionId,
        ],
    ], JSON_UNESCAPED_SLASHES);

    $signature = hash_hmac('sha256', $payload, $this->webhookSecret);

    $webhook = $this->call('POST', '/api/v1/payments/webhook', [], [], [], [
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_PAYMENT_SIGNATURE' => $signature,
    ], $payload);

    $webhook->assertOk();
    $webhook->assertJsonPath('success', true);

    $this->assertDatabaseHas('webhook_events', [
        'event_id' => $eventId,
        'provider' => 'mock',
        'event_type' => 'payment.succeeded',
    ]);

    // Replaying the same event must be idempotent.
    $replay = $this->call('POST', '/api/v1/payments/webhook', [], [], [], [
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_PAYMENT_SIGNATURE' => $signature,
    ], $payload);

    $replay->assertOk();
    $replay->assertJsonPath('success', true);
    $replay->assertJsonPath('message', 'Webhook already processed.');

    // ---------------------------------------------------------------
    // Step 9: Verify the payment
    // ---------------------------------------------------------------
    $this->assertDatabaseHas('payments', [
        'id' => $paymentId,
        'booking_id' => $bookingId,
        'status' => 'paid',
        'provider' => 'mock',
        'amount' => 120.0,
        'idempotency_key' => $paymentKey,
    ]);

    $payment = Payment::findOrFail($paymentId);
    $this->assertSame($bookingId, $payment->booking_id);
    $this->assertSame('paid', $payment->status);
    $this->assertNotEmpty($payment->transaction_id);
    $this->assertSame($transactionId, $payment->transaction_id);
    $this->assertEquals((float) $this->service->price, (float) $payment->amount);

    $booking = Booking::with('payment')->findOrFail($bookingId);
    $this->assertSame($paymentId, $booking->payment->id);

    // ---------------------------------------------------------------
    // Step 10: Verify the booking through the API
    // ---------------------------------------------------------------
    $showBooking = $this->withToken($token)->getJson('/api/v1/bookings/'.$bookingId);

    $showBooking->assertOk();
    $showBooking->assertJsonPath('data.id', $bookingId);
    $showBooking->assertJsonPath('data.status', 'pending');
    $showBooking->assertJsonPath('data.date', $this->bookingDate);
    $showBooking->assertJsonPath('data.service.id', $this->service->id);
    $showBooking->assertJsonPath('data.location.id', $this->location->id);

    // ---------------------------------------------------------------
    // Step 11: View the customer's bookings
    // ---------------------------------------------------------------
    $listBookings = $this->withToken($token)->getJson('/api/v1/bookings');

    $listBookings->assertOk();

    $customerBookings = collect($listBookings->json('data'));
    $this->assertTrue($customerBookings->contains('id', $bookingId));
    $this->assertTrue($customerBookings->contains('customer_id', $customerId));
    $this->assertSame(1, $listBookings->json('total'));
});

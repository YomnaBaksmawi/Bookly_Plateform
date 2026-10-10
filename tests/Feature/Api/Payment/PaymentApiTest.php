<?php

use App\Http\Middleware\VerifyApiCsrfToken;
use App\Modules\Auth\Actions\GenerateTokenAction;
use App\Modules\Booking\Models\Booking;
use App\Modules\Payment\Models\Payment;
use App\Modules\Payment\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Support\BookingFixtures;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([VerifyApiCsrfToken::class]);
    Cache::flush();
    config(['services.payment.webhook_secret' => 'test-webhook-secret']);
});

function paymentAs($test, string $token)
{
    \Illuminate\Support\Facades\Auth::forgetGuards();

    return $test->withToken($token);
}

function paymentBooking(): array
{
    [$provider, , , $service, $serviceLocation] = BookingFixtures::stack();
    BookingFixtures::openEveryDay($serviceLocation->location);
    $customer = BookingFixtures::customer();
    $date = now()->addDays(3)->toDateString();
    $slot = BookingFixtures::slot($serviceLocation, $date, '10:00', 60);

    $booking = Booking::create([
        'customer_id' => $customer->id,
        'service_location_id' => $serviceLocation->id,
        'booking_slot_id' => $slot->id,
        'starts_at' => $slot->date->toDateString().' '.$slot->start_time,
        'ends_at' => $slot->date->toDateString().' '.$slot->end_time,
        'status' => 'pending',
        'price_at_booking' => $service->price,
    ]);

    return [$customer, $provider, $booking];
}

function webhookSignedPayload(string $payload): string
{
    return hash_hmac('sha256', $payload, config('services.payment.webhook_secret'));
}

function webhookPost($test, string $payload): \Illuminate\Testing\TestResponse
{
    return $test->call('POST', '/api/v1/payments/webhook', [], [], [], [
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_PAYMENT_SIGNATURE' => webhookSignedPayload($payload),
    ], $payload);
}

it('requires an idempotency key to create a payment', function () {
    [$customer, , $booking] = paymentBooking();
    $token = (new GenerateTokenAction)->execute($customer, 'auth_token');

    paymentAs($this, $token)
        ->postJson('/api/v1/payments', ['booking_id' => $booking->id])
        ->assertStatus(422)
        ->assertJsonPath('error_code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['Idempotency-Key']]);
});

it('does not let a provider create a payment', function () {
    [, $provider, $booking] = paymentBooking();
    $token = (new GenerateTokenAction)->execute($provider, 'auth_token');

    paymentAs($this, $token)
        ->postJson('/api/v1/payments', ['booking_id' => $booking->id], ['Idempotency-Key' => 'paykey-provider'])
        ->assertStatus(403)
        ->assertJsonPath('error_code', 'FORBIDDEN');
});

it('rejects paying for a booking that does not belong to the customer', function () {
    $other = BookingFixtures::customer();
    [$customer, , $booking] = paymentBooking();
    $token = (new GenerateTokenAction)->execute($other, 'auth_token');

    paymentAs($this, $token)
        ->postJson('/api/v1/payments', ['booking_id' => $booking->id], ['Idempotency-Key' => 'paykey-other'])
        ->assertStatus(404)
        ->assertJsonPath('error_code', 'ROUTE_NOT_FOUND');
});

it('creates a paid payment for the booking price', function () {
    [$customer, , $booking] = paymentBooking();
    $token = (new GenerateTokenAction)->execute($customer, 'auth_token');

    $response = paymentAs($this, $token)
        ->postJson('/api/v1/payments', ['booking_id' => $booking->id], ['Idempotency-Key' => 'paykey-ok'])
        ->assertCreated();

    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.status', 'paid');
    $response->assertJsonPath('data.provider', 'mock');
    $response->assertJsonPath('data.booking_id', $booking->id);
    expect((float) $response->json('data.amount'))->toBe((float) $booking->price_at_booking);
    $this->assertNotEmpty($response->json('data.transaction_id'));

    $this->assertDatabaseHas('payments', [
        'id' => $response->json('data.id'),
        'idempotency_key' => 'paykey-ok',
        'status' => 'paid',
    ]);
});

it('replays the same idempotency key without creating a duplicate', function () {
    [$customer, , $booking] = paymentBooking();
    $token = (new GenerateTokenAction)->execute($customer, 'auth_token');

    $first = paymentAs($this, $token)
        ->postJson('/api/v1/payments', ['booking_id' => $booking->id], ['Idempotency-Key' => 'paykey-replay'])
        ->assertCreated();

    $paymentId = $first->json('data.id');

    $second = paymentAs($this, $token)
        ->postJson('/api/v1/payments', ['booking_id' => $booking->id], ['Idempotency-Key' => 'paykey-replay'])
        ->assertCreated();

    expect($second->json('data.id'))->toBe($paymentId);
    expect(Payment::where('idempotency_key', 'paykey-replay')->count())->toBe(1);
});

it('lets only the owning customer view a payment', function () {
    [$customer, , $booking] = paymentBooking();
    $other = BookingFixtures::customer();
    $token = (new GenerateTokenAction)->execute($customer, 'auth_token');

    $paymentId = paymentAs($this, $token)
        ->postJson('/api/v1/payments', ['booking_id' => $booking->id], ['Idempotency-Key' => 'paykey-show'])
        ->assertCreated()->json('data.id');

    paymentAs($this, $token)->getJson("/api/v1/payments/{$paymentId}")
        ->assertOk()->assertJsonPath('data.id', $paymentId);

    paymentAs($this, (new GenerateTokenAction)->execute($other, 'auth_token'))
        ->getJson("/api/v1/payments/{$paymentId}")
        ->assertStatus(404);
});

it('lists payments for a provider', function () {
    [, $provider, $booking] = paymentBooking();
    $customerToken = (new GenerateTokenAction)->execute($booking->customer, 'auth_token');

    paymentAs($this, $customerToken)
        ->postJson('/api/v1/payments', ['booking_id' => $booking->id], ['Idempotency-Key' => 'paykey-provider-list'])
        ->assertCreated();

    paymentAs($this, (new GenerateTokenAction)->execute($provider, 'auth_token'))
        ->getJson('/api/v1/provider/payments')
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('rejects a webhook with a bad signature', function () {
    $this->call('POST', '/api/v1/payments/webhook', [], [], [], [
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_PAYMENT_SIGNATURE' => 'wrong-signature',
    ], '{"id":"evt-1","type":"payment.succeeded","data":{"transaction_id":"txn-1"}}')
        ->assertStatus(401)
        ->assertJsonPath('error_code', 'INVALID_WEBHOOK_SIGNATURE');
});

it('marks a payment as paid from a signed webhook', function () {
    [$customer, , $booking] = paymentBooking();

    $payment = Payment::create([
        'booking_id' => $booking->id,
        'amount' => $booking->price_at_booking,
        'status' => 'pending',
        'provider' => 'mock',
        'transaction_id' => 'txn-payment-1',
        'idempotency_key' => 'webhook-paid-1',
    ]);

    $payload = json_encode([
        'id' => 'evt-payment-1',
        'type' => 'payment.succeeded',
        'data' => ['transaction_id' => 'txn-payment-1'],
    ]);

    webhookPost($this, $payload)->assertOk()->assertJsonPath('message', 'Webhook processed successfully.');

    expect(Payment::findOrFail($payment->id)->status)->toBe('paid');
    $this->assertDatabaseCount('webhook_events', 1);
    $this->assertDatabaseHas('webhook_events', ['event_id' => 'evt-payment-1', 'event_type' => 'payment.succeeded']);

    $replay = webhookPost($this, $payload)->assertOk();
    $replay->assertJsonPath('message', 'Webhook already processed.');
    $this->assertDatabaseCount('webhook_events', 1);
});

it('marks a payment as failed from a signed failed webhook', function () {
    [$customer, , $booking] = paymentBooking();

    $payment = Payment::create([
        'booking_id' => $booking->id,
        'amount' => $booking->price_at_booking,
        'status' => 'pending',
        'provider' => 'mock',
        'transaction_id' => 'txn-failed-1',
        'idempotency_key' => 'webhook-failed-1',
    ]);

    $payload = json_encode([
        'id' => 'evt-payment-2',
        'type' => 'payment.failed',
        'data' => ['transaction_id' => 'txn-failed-1'],
    ]);

    webhookPost($this, $payload)->assertOk();
    expect(Payment::findOrFail($payment->id)->status)->toBe('failed');
});

it('returns 404 from a webhook for an unknown transaction', function () {
    $payload = json_encode([
        'id' => 'evt-payment-3',
        'type' => 'payment.succeeded',
        'data' => ['transaction_id' => 'txn-does-not-exist'],
    ]);

    webhookPost($this, $payload)
        ->assertStatus(404)
        ->assertJsonPath('error_code', 'PAYMENT_NOT_FOUND');
});

it('rejects an unsupported webhook event type', function () {
    [$customer, , $booking] = paymentBooking();
    Payment::create([
        'booking_id' => $booking->id,
        'amount' => $booking->price_at_booking,
        'status' => 'pending',
        'provider' => 'mock',
        'transaction_id' => 'txn-weird',
        'idempotency_key' => 'webhook-weird-1',
    ]);

    $payload = json_encode([
        'id' => 'evt-payment-4',
        'type' => 'payment.disputed',
        'data' => ['transaction_id' => 'txn-weird'],
    ]);

    webhookPost($this, $payload)
        ->assertStatus(422)
        ->assertJsonPath('error_code', 'UNSUPPORTED_WEBHOOK_EVENT');
});

it('rejects a webhook payload missing required fields', function () {
    webhookPost($this, '{"id":"evt-payment-5"}')
        ->assertStatus(422)
        ->assertJsonPath('error_code', 'INVALID_WEBHOOK_PAYLOAD');
});

it('hides the test signature generator outside local environment', function () {
    $this->post('/api/v1/payments/webhook/test-signature', [], ['CONTENT_TYPE' => 'application/json'])
        ->assertStatus(404);
});
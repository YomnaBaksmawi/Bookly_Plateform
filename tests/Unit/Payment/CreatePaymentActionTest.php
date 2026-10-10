<?php

use App\Modules\Booking\Services\BookingService;
use App\Modules\Payment\Actions\CreatePaymentAction;
use App\Modules\Payment\Contracts\PaymentGateway;
use App\Modules\Payment\Data\PaymentResult;
use App\Modules\Payment\Models\Payment;
use App\Modules\ServiceLocation\Models\ServiceLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Support\BookingFixtures;

uses(TestCase::class, RefreshDatabase::class);

function createBookedPayment(ServiceLocation $serviceLocation, int $duration = 60)
{
    $customer = BookingFixtures::customer();
    $slot = BookingFixtures::slot($serviceLocation, now()->addDays(2)->toDateString(), '10:00', $duration);

    return (new BookingService)->create($customer->id, $slot);
}

it('charges the mock gateway and records a paid payment', function () {
    [, , , $service, $serviceLocation] = BookingFixtures::stack();
    $booking = createBookedPayment($serviceLocation);

    $payment = app(CreatePaymentAction::class)->execute($booking, 'pay-key-1');

    expect($payment->booking_id)->toBe($booking->id);
    expect($payment->status)->toBe('paid');
    expect($payment->provider)->toBe('mock');
    expect($payment->idempotency_key)->toBe('pay-key-1');
    expect((float) $payment->amount)->toBe((float) $service->price);
    expect($payment->transaction_id)->not->toBeNull();
});

it('returns the existing payment when the same idempotency key is replayed', function () {
    [, , , , $serviceLocation] = BookingFixtures::stack();
    $booking = createBookedPayment($serviceLocation);
    $action = app(CreatePaymentAction::class);

    $first = $action->execute($booking, 'pay-key-2');
    $second = $action->execute($booking, 'pay-key-2');

    expect($second->id)->toBe($first->id);
    expect(Payment::where('idempotency_key', 'pay-key-2')->count())->toBe(1);
});

it('records a failed payment when the gateway declines', function () {
    $this->app->bind(PaymentGateway::class, function () {
        return new class implements PaymentGateway
        {
            public function charge(int $amount, string $currency, string $reference): PaymentResult
            {
                return new PaymentResult(false, null, 'Gate declined.');
            }
        };
    });

    [, , , , $serviceLocation] = BookingFixtures::stack();
    $booking = createBookedPayment($serviceLocation);

    $payment = app(CreatePaymentAction::class)->execute($booking, 'pay-key-3');

    expect($payment->status)->toBe('failed');
    expect($payment->transaction_id)->toBeNull();
});

it('binds the mock gateway as the default payment provider', function () {
    $gateway = app(PaymentGateway::class);

    expect($gateway)->toBeInstanceOf(PaymentGateway::class);
});
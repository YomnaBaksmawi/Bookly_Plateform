<?php

namespace App\Modules\Payment\Actions;

use App\Modules\Booking\Models\Booking;
use App\Modules\Payment\Contracts\PaymentGateway;
use App\Modules\Payment\Enums\PaymentStatus;
use App\Modules\Payment\Models\Payment;
use Illuminate\Support\Facades\DB;

class CreatePaymentAction
{
    public function __construct(private PaymentGateway $gateway)
    {
    }

    public function execute(Booking $booking, string $idempotencyKey): Payment
    {
        return DB::transaction(function () use ($booking, $idempotencyKey) {
            $existing = Payment::where('idempotency_key', $idempotencyKey)->first();

            if ($existing) {
                return $existing;
            }

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->price_at_booking,
                'status' => PaymentStatus::PENDING->value,
                'provider' => 'mock',
                'idempotency_key' => $idempotencyKey,
            ]);

            $result = $this->gateway->charge(  (int) round($booking->price_at_booking * 100),
                'USD',
                "booking_{$booking->id}"
            );

            $payment->update([
                'status' => $result->success ? PaymentStatus::PAID->value : PaymentStatus::FAILED->value,
                'transaction_id' => $result->transactionId,
                'payload' => $result->payload,
            ]);

            return $payment->fresh();
        });
    }
}
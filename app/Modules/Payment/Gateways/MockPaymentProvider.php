<?php

namespace App\Modules\Payment\Gateways;

use App\Modules\Payment\Contracts\PaymentGateway;
use App\Modules\Payment\Data\PaymentResult;
use Illuminate\Support\Str;

class MockPaymentProvider implements PaymentGateway
{
    public function charge(int $amount, string $currency, string $reference): PaymentResult
    {
        if ($amount <= 0) {
            return new PaymentResult(false, null, 'Invalid payment amount.');
        }

        return new PaymentResult(
            true,
            'mock_' . Str::uuid(),
            'Payment successful.',
            [
                'provider' => 'mock',
                'reference' => $reference,
                'amount' => $amount,
                'currency' => $currency,
            ]
        );
    }
}
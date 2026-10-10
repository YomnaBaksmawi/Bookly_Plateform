<?php

namespace App\Modules\Payment\Contracts;

use App\Modules\Payment\Data\PaymentResult;

interface PaymentGateway
{
    public function charge(int $amount, string $currency, string $reference): PaymentResult;
}
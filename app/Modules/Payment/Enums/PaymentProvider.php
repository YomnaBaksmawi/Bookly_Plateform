<?php

namespace App\Modules\Payment\Enums;

enum PaymentProvider: string
{
    case MOCK = 'mock';
    case STRIPE = 'stripe';
    case PAYPAL = 'paypal';
    case MADA = 'mada';
    case APPLE_PAY = 'apple_pay';

    public function label(): string
    {
        return match ($this) {
            self::MOCK => 'Mock Provider',
            self::STRIPE => 'Stripe',
            self::PAYPAL => 'PayPal',
            self::MADA => 'Mada',
            self::APPLE_PAY => 'Apple Pay',
        };
    }

    public function isOnline(): bool
    {
        return ! in_array($this, [self::MOCK]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

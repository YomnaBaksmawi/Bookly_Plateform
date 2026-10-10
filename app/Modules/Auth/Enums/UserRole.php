<?php

namespace App\Modules\Auth\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case PROVIDER = 'provider';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::PROVIDER => 'Service Provider',
            self::CUSTOMER => 'Customer',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

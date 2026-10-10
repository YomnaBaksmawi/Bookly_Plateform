<?php

namespace App\Modules\Auth\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

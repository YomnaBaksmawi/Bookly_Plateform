<?php

namespace App\Modules\Booking\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case NO_SHOW = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::CANCELLED => 'Cancelled',
            self::COMPLETED => 'Completed',
            self::NO_SHOW => 'No Show',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::CONFIRMED => 'blue',
            self::CANCELLED => 'red',
            self::COMPLETED => 'green',
            self::NO_SHOW => 'gray',
        };
    }
public function isFinal(): bool
{
    return in_array($this, [
        self::CANCELLED,
        self::COMPLETED,
        self::NO_SHOW,
    ]);
}
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED]);
    }
}

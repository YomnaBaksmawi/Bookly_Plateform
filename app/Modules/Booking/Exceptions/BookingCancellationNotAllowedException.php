<?php

namespace App\Modules\Booking\Exceptions;

use RuntimeException;

class BookingCancellationNotAllowedException extends RuntimeException
{
    public function __construct(string $message = 'This booking cannot be cancelled.')
    {
        parent::__construct($message);
    }
}
<?php

namespace App\Modules\Booking\Exceptions;

use RuntimeException;

class BookingReschedulingNotAllowedException extends RuntimeException
{
    public function __construct(string $message = 'This booking cannot be rescheduled.')
    {
        parent::__construct($message);
    }
}
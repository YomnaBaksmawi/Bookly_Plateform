<?php

namespace App\Modules\Booking\Exceptions;

use Exception;

class BookingSlotUnavailableException extends Exception
{
    protected $code = 409;

    public function __construct()
    {
        parent::__construct('The selected booking slot is no longer available.');
    }
}
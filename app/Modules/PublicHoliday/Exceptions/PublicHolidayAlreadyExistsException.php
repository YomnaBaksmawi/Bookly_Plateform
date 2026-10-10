<?php

namespace App\Modules\PublicHoliday\Exceptions;

use Exception;

class PublicHolidayAlreadyExistsException extends Exception
{
    protected $code = 409;

    public function __construct()
    {
        parent::__construct(
            'A public holiday already exists for this date, country, and location.'
        );
    }
}
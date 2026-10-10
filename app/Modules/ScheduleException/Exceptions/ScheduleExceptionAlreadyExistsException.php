<?php

namespace App\Modules\ScheduleException\Exceptions;

use Exception;

class ScheduleExceptionAlreadyExistsException extends Exception
{
    protected $code = 409;

    public function __construct()
    {
        parent::__construct(
            'A schedule exception already exists for this location and date.'
        );
    }
}
<?php

namespace App\Modules\Auth\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    public function __construct(string $message = 'Invalid credentials provided.')
    {
        parent::__construct($message, 401);
    }
}

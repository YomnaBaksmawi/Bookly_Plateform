<?php

namespace App\Modules\Auth\Exceptions;

use Exception;

class InvalidCurrentPasswordException extends Exception
{
    public function __construct(string $message = 'Current password is incorrect.')
    {
        parent::__construct($message, 422);
    }
}
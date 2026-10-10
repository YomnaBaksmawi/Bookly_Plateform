<?php

namespace App\Modules\Auth\Exceptions;

use Exception;

class UserSuspendedException extends Exception
{
    public function __construct(string $message = 'Your account is suspended or inactive.')
    {
        parent::__construct($message, 403);
    }
}

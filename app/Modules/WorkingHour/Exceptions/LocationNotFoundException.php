<?php

namespace App\Modules\WorkingHour\Exceptions;

use Exception;

class LocationNotFoundException extends Exception
{
    protected $code = 404;

    public function __construct()
    {
        parent::__construct('Location not found.');
    }
}
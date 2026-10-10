<?php

namespace App\Modules\WorkingHour\Exceptions;

use Exception;

class WorkingHourOverlapException  extends Exception
{
    protected $code = 409;

    public function __construct()
    {
        parent::__construct('Working hours already exist for this day.');
    }
}
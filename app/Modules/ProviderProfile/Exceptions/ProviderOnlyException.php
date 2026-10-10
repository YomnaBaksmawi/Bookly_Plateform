<?php

namespace App\Modules\ProviderProfile\Exceptions;

use Exception;

class ProviderOnlyException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Only providers can perform this action.',
            403
        );
    }
}
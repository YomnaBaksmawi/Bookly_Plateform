<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;

class FirebaseService
{
    public function __construct(
        private Messaging $messaging
    ) {
    }

    public function messaging(): Messaging
    {
        return $this->messaging;
    }
}
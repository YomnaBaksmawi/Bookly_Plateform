<?php

namespace App\Modules\Location\Actions;

use App\Modules\Location\Models\Location;

class DeleteLocationAction
{
    public function execute(Location $location): void
    {
        $location->delete();
    }
}
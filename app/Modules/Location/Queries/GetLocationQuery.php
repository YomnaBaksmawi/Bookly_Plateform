<?php

namespace App\Modules\Location\Queries;

use App\Modules\Location\Models\Location;

class GetLocationQuery
{
    public function execute(Location $location): Location
    {
        return $location;
    }
}
<?php

namespace App\Modules\Location\Actions;

use App\Modules\Location\Models\Location;

class UpdateLocationAction
{
    public function execute(Location $location, array $data): Location
    {
        $location->update($data);

        return $location->fresh();
    }
}
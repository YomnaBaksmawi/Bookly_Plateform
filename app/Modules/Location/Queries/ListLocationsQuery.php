<?php

namespace App\Modules\Location\Queries;

use App\Modules\Location\Models\Location;
use Illuminate\Database\Eloquent\Collection;

class ListLocationsQuery
{
    public function execute(): Collection
    {
        return Location::latest()->get();
    }
}
<?php

namespace App\Modules\Service\Queries;

use App\Modules\Service\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class ListServicesQuery
{
    public function execute(): Collection
    {
        return Service::with('locations')->latest()->get();
    }
}
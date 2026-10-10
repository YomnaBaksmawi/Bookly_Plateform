<?php

namespace App\Modules\Service\Queries;

use App\Modules\Service\Models\Service;

class GetServiceQuery
{
    public function execute(Service $service): Service
    {
return Service::with('locations')->findOrFail($service->id);    }
}
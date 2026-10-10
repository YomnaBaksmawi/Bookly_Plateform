<?php

namespace App\Modules\Availability\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Availability\Actions\GetAvailabilityAction;
use App\Modules\Availability\Http\Requests\GetAvailabilityRequest;
use App\Modules\Availability\Http\Resources\AvailabilityResource;

class AvailabilityController extends Controller
{
    public function index( GetAvailabilityRequest $request, GetAvailabilityAction $action ): AvailabilityResource {
        $result = $action->execute(
            $request->integer('service_id'),
            $request->integer('location_id'),
            $request->string('date')->toString()
        );

        return new AvailabilityResource($result);
    }
}
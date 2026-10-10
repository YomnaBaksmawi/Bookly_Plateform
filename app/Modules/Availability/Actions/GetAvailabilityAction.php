<?php

namespace App\Modules\Availability\Actions;

use App\Modules\Availability\Services\AvailabilityService;
use App\Modules\Location\Models\Location;
use App\Modules\Service\Models\Service;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetAvailabilityAction
{
    public function __construct(
        private AvailabilityService $availabilityService
    ) {
    }

    public function execute(int $serviceId, int $locationId, string $date ): array {
        $service = Service::findOrFail($serviceId);

        $location = Location::findOrFail($locationId);

        $serviceExistsAtLocation = $service->locations()
            ->where('locations.id', $location->id)
            ->exists();

        if (! $serviceExistsAtLocation) {
            throw (new ModelNotFoundException) ->setModel(Service::class, [$serviceId]);
        }

        $slots = $this->availabilityService->getSlots( $service, $location, $date  );

        return [
            'service' => $service,
            'location' => $location,
            'date' => $date,
            'slots' => $slots,
        ];
    }
}
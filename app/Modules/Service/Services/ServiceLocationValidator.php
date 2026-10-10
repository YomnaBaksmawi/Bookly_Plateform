<?php

namespace App\Modules\Service\Services;

use App\Models\User;
use App\Modules\Location\Models\Location;

class ServiceLocationValidator
{
   public function validate(User $user, array $locationIds): void
{
    $locationIds = array_unique($locationIds);

    $locations = Location::whereIn('id', $locationIds)
        ->get(['id', 'provider_id']);

    if (array_diff($locationIds, $locations->pluck('id')->all())) {
        abort(404, 'One or more locations were not found.');
    }

    if (
        $user->role !== 'admin' &&
        $locations->contains(
            fn (Location $location) => $location->provider_id !== $user->id
        )
    ) {
        abort(403, 'You can only use your own locations.');
    }
}
}
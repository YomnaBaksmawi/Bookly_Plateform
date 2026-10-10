<?php

namespace App\Modules\PublicHoliday\Actions;

use App\Models\User;
use App\Modules\Location\Models\Location;
use App\Modules\PublicHoliday\Exceptions\PublicHolidayAlreadyExistsException;
use App\Modules\PublicHoliday\Models\PublicHoliday;
use Illuminate\Auth\Access\AuthorizationException;

class CreatePublicHolidayAction
{
    public function execute(User $user, array $data): PublicHoliday
    {
        if (! $user->can('create', PublicHoliday::class)) {
            throw new AuthorizationException;
        }
if ($user->role !== 'admin' && empty($data['location_id'])) {
    throw new AuthorizationException(
        'Please specify a location for the public holiday.'
    );
}
      

        if (isset($data['location_id'])) {
            $location = Location::find($data['location_id']);
           
        }

        $exists = PublicHoliday::where('date', $data['date'])
            ->where('country', $data['country'])
            ->where('location_id', $data['location_id'] ?? null)
            ->exists();

        if ($exists) {
            throw new PublicHolidayAlreadyExistsException;
        }

        return PublicHoliday::create($data);
    }
}
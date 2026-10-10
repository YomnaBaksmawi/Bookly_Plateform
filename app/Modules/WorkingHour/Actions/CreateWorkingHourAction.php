<?php

namespace App\Modules\WorkingHour\Actions;

use App\Models\User;
use App\Modules\Location\Models\Location;
use App\Modules\WorkingHour\Exceptions\LocationNotFoundException;
use App\Modules\WorkingHour\Models\WorkingHour;
use Illuminate\Auth\Access\AuthorizationException;
use App\Modules\WorkingHour\Exceptions\WorkingHourOverlapException;
class CreateWorkingHourAction
{
    public function execute(User $user, array $data): WorkingHour
    {
        if (! $user->can('create', WorkingHour::class)) {
            throw new AuthorizationException;
        }

        $location = Location::find($data['location_id']);

        if (! $location) {
            throw new LocationNotFoundException;
        }

        if ($user->role !== 'admin' && $location->provider_id !== $user->id) {
            throw new AuthorizationException('You can only use your own locations.');
        }

        $overlapExists = WorkingHour::where('location_id', $location->id)
            ->where('day_of_week', $data['day_of_week'])
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->exists();

        if ($overlapExists) {
            throw new WorkingHourOverlapException;
        }

        return WorkingHour::create($data);
    }
}
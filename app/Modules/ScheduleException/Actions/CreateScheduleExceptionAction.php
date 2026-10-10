<?php

namespace App\Modules\ScheduleException\Actions;

use App\Models\User;
use App\Modules\Location\Models\Location;
use App\Modules\ScheduleException\Exceptions\ScheduleExceptionAlreadyExistsException;
use App\Modules\ScheduleException\Models\ScheduleException;
use Illuminate\Auth\Access\AuthorizationException;

class CreateScheduleExceptionAction
{
    public function execute(User $user, array $data): ScheduleException
    {
        if (! $user->can('create', ScheduleException::class)) {
            throw new AuthorizationException;
        }

        $location = Location::find($data['location_id']);

        if (  $user->role !== 'admin'  && $location->provider_id !== $user->id ) {
            throw new AuthorizationException( 'You can only use your own locations.' );
        }

        $exists = ScheduleException::where('location_id', $location->id)
            ->where('date', $data['date'])
            ->exists();

        if ($exists) {
            throw new ScheduleExceptionAlreadyExistsException;
        }

        if ($data['type'] === 'holiday') {
            $data['start_time'] = null;
            $data['end_time'] = null;
        }

        return ScheduleException::create($data);
    }
}
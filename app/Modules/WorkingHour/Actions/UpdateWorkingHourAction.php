<?php

namespace App\Modules\WorkingHour\Actions;

use App\Models\User;
use App\Modules\WorkingHour\Exceptions\WorkingHourOverlapException;
use App\Modules\WorkingHour\Models\WorkingHour;
use Illuminate\Auth\Access\AuthorizationException;

class UpdateWorkingHourAction
{
    public function execute(User $user, WorkingHour $workingHour, array $data): WorkingHour
    {
        if (! $user->can('update', $workingHour)) {
            throw new AuthorizationException;
        }

        $location = $workingHour->location;

        if ($user->role !== 'admin' && $location->provider_id !== $user->id) {
            throw new AuthorizationException('You can only use your own locations.');
        }

        $dayOfWeek = $data['day_of_week'] ?? $workingHour->day_of_week;
        $startTime = $data['start_time'] ?? $workingHour->start_time;
        $endTime = $data['end_time'] ?? $workingHour->end_time;

        $overlapExists = WorkingHour::where('location_id', $location->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('id', '!=', $workingHour->id)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($overlapExists) {
            throw new WorkingHourOverlapException;
        }

        $workingHour->update($data);

        return $workingHour->fresh();
    }
}
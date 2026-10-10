<?php

namespace App\Modules\ScheduleException\Queries;

use App\Models\User;
use App\Modules\ScheduleException\Models\ScheduleException;

class ScheduleExceptionQuery
{
    public function index(User $user)
    {
        return ScheduleException::query()
            ->with('location')
            ->when( $user->role !== 'admin', fn ($query) => $query->whereHas(  'location',
                    fn ($q) => $q->where('provider_id', $user->id)
                )
                      )  ->orderBy('date')
            ->get();
    }

    public function forLocation(int $locationId)
    {
        return ScheduleException::query()->where('location_id', $locationId)
            ->orderBy('date')
            ->get();
    }
}
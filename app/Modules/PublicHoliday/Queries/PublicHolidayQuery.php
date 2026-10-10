<?php

namespace App\Modules\PublicHoliday\Queries;

use App\Models\User;
use App\Modules\PublicHoliday\Models\PublicHoliday;

class PublicHolidayQuery
{
    public function index(User $user)
    {
        return PublicHoliday::query()->with('location')->when($user->role !== 'admin',
                fn ($query) => $query->where(function ($q) use ($user) {
                    $q->whereNull('location_id')
                        ->orWhereHas('location',fn ($location) => $location->where('provider_id', $user->id)  );
                })
            ) ->orderBy('date')  ->get();
    }

    public function forLocation(int $locationId)
    {
        return PublicHoliday::query()
            ->where(function ($query) use ($locationId) {
                $query->whereNull('location_id')
                    ->orWhere('location_id', $locationId);
            }) ->orderBy('date')->get();
    }
}
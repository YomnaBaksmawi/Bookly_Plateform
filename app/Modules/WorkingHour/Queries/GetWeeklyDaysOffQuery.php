<?php

namespace App\Modules\WorkingHour\Queries;

use App\Models\User;
use App\Modules\WorkingHour\Models\WorkingHour;

class GetWeeklyDaysOffQuery
{
    private const DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    public function execute(User $user, int $locationId): array
    {
        $workingDays = WorkingHour::query()
            ->where('location_id', $locationId)
            ->whereHas(
                'location',
                fn ($query) => $query->where('provider_id', $user->id)
            )
            ->where('is_active', true)
            ->pluck('day_of_week')
            ->map(fn ($day) => strtolower($day))
            ->unique()
            ->values()
            ->all();

        return array_values(
            array_diff(self::DAYS, $workingDays)
        );
    }
}
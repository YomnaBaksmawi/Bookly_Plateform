<?php

namespace App\Modules\PublicHoliday\Policies;

use App\Models\User;
use App\Modules\PublicHoliday\Models\PublicHoliday;

class PublicHolidayPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'provider']);
    }

    public function update(User $user, PublicHoliday $holiday): bool
    {
        return $user->role === 'admin'
            || $holiday->location?->provider_id === $user->id;
    }

    public function delete(User $user, PublicHoliday $holiday): bool
    {
        return $user->role === 'admin'
            || $holiday->location?->provider_id === $user->id;
    }
}
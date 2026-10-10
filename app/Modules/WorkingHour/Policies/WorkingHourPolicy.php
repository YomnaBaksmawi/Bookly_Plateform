<?php

namespace App\Modules\WorkingHour\Policies;

use App\Models\User;
use App\Modules\WorkingHour\Models\WorkingHour;

class WorkingHourPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'provider']);
    }

    public function update(User $user, WorkingHour $workingHour): bool
    {
        return $user->role === 'admin'
            || $workingHour->location->provider_id === $user->id;
    }

    public function delete(User $user, WorkingHour $workingHour): bool
    {
        return $user->role === 'admin'
            || $workingHour->location->provider_id === $user->id;
    }
}
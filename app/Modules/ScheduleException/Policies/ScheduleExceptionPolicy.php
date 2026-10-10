<?php

namespace App\Modules\ScheduleException\Policies;

use App\Models\User;
use App\Modules\ScheduleException\Models\ScheduleException;

class ScheduleExceptionPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'provider']);
    }

    public function update(User $user, ScheduleException $exception): bool
    {
        return $user->role === 'admin'
            || $exception->location->provider_id === $user->id;
    }

    public function delete(User $user, ScheduleException $exception): bool
    {
        return $user->role === 'admin'
            || $exception->location->provider_id === $user->id;
    }
}
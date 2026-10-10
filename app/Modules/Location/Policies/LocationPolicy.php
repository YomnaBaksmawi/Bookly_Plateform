<?php

namespace App\Modules\Location\Policies;

use App\Models\User;
use App\Modules\Location\Models\Location;

class LocationPolicy
{
     public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'provider']);
    }
    public function update(User $user, Location $location): bool
    {
        return $user->role === 'admin'
            || $location->provider_id === $user->id;
    }

    public function delete(User $user, Location $location): bool
    {
        return $user->role === 'admin'
            || $location->provider_id === $user->id;
    }
}
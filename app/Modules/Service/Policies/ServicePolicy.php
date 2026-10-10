<?php

namespace App\Modules\Service\Policies;

use App\Models\User;
use App\Modules\Service\Models\Service;

class ServicePolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'provider']);
    }

    public function update(User $user, Service $service): bool
    {
        return $user->role === 'admin'
            || $service->provider_id === $user->id;
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->role === 'admin'
            || $service->provider_id === $user->id;
    }
}
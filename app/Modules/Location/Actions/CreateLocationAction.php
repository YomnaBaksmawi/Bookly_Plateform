<?php

namespace App\Modules\Location\Actions;

use App\Models\User;
use App\Modules\Location\Models\Location;
use Illuminate\Auth\Access\AuthorizationException;

class CreateLocationAction
{
    public function execute(User $user, array $data): Location
    {
        if (! $user->can('create', Location::class)) {
            throw new AuthorizationException;
        }

        return Location::create([
            'provider_id' => $user->id,
            ...$data,
        ]);
    }
}

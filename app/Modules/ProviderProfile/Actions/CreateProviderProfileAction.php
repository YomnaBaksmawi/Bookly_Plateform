<?php

namespace App\Modules\ProviderProfile\Actions;

use App\Models\User;
use App\Modules\ProviderProfile\Models\ProviderProfile;
use App\Modules\ProviderProfile\Exceptions\ProviderOnlyException;

class CreateProviderProfileAction
{
    public function execute(User $user, array $data): ProviderProfile
    {
        if ($user->role !== 'provider') {
            throw new ProviderOnlyException();
        }

        return ProviderProfile::create([
            'user_id' => $user->id,
            ...$data,
        ]);
    }
}
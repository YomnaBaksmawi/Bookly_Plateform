<?php

namespace App\Modules\ProviderProfile\Actions;

use App\Models\User;
use App\Modules\ProviderProfile\Models\ProviderProfile;

class UpdateProviderProfileAction
{
    public function execute(User $user, array $data): ProviderProfile
    {
        $profile = $user->providerProfile()->firstOrFail();

        $profile->update($data);

        return $profile->fresh();
    }
}
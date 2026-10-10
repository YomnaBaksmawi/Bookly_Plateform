<?php

namespace App\Modules\ProviderProfile\Queries;

use App\Models\User;
use App\Modules\ProviderProfile\Models\ProviderProfile;

class GetProviderProfileQuery
{
    public function execute(User $user): ProviderProfile
    {
        return ProviderProfile::where('user_id', $user->id)->firstOrFail();
    }
}
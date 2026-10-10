<?php

namespace App\Modules\PublicHoliday\Actions;

use App\Models\User;
use App\Modules\PublicHoliday\Models\PublicHoliday;
use Illuminate\Auth\Access\AuthorizationException;

class UpdatePublicHolidayAction
{
    public function execute( User $user, PublicHoliday $holiday, array $data): PublicHoliday {
        if (! $user->can('update', $holiday)) {
            throw new AuthorizationException;
        }

        $holiday->update($data);

        return $holiday->fresh();
    }
}
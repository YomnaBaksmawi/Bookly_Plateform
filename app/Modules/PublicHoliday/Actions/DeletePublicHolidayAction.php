<?php

namespace App\Modules\PublicHoliday\Actions;

use App\Models\User;
use App\Modules\PublicHoliday\Models\PublicHoliday;
use Illuminate\Auth\Access\AuthorizationException;

class DeletePublicHolidayAction
{
    public function execute( User $user, PublicHoliday $holiday ): void {
        if (! $user->can('delete', $holiday)) {
            throw new AuthorizationException;
        }

        $holiday->delete();
    }
}
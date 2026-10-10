<?php

namespace App\Modules\WorkingHour\Actions;

use App\Models\User;
use App\Modules\WorkingHour\Models\WorkingHour;
use Illuminate\Auth\Access\AuthorizationException;

class DeleteWorkingHourAction
{
    public function execute(User $user, WorkingHour $workingHour): void
    {
        if (! $user->can('delete', $workingHour)) {
            throw new AuthorizationException;
        }

        $workingHour->delete();
    }
}
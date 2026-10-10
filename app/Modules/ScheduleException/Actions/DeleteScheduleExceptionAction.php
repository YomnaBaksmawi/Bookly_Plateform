<?php

namespace App\Modules\ScheduleException\Actions;

use App\Models\User;
use App\Modules\ScheduleException\Models\ScheduleException;
use Illuminate\Auth\Access\AuthorizationException;

class DeleteScheduleExceptionAction
{
    public function execute(
        User $user,
        ScheduleException $exception
    ): void {
        if (! $user->can('delete', $exception)) {
            throw new AuthorizationException;
        }

        $exception->delete();
    }
}
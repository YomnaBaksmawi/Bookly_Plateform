<?php

namespace App\Modules\ScheduleException\Actions;

use App\Models\User;
use App\Modules\ScheduleException\Exceptions\ScheduleExceptionAlreadyExistsException;
use App\Modules\ScheduleException\Models\ScheduleException;
use Illuminate\Auth\Access\AuthorizationException;

class UpdateScheduleExceptionAction
{
    public function execute(User $user,ScheduleException $exception, array $data ): ScheduleException {
        if (! $user->can('update', $exception)) {
            throw new AuthorizationException;
        }

        $location = $exception->location;

        if (  $user->role !== 'admin'  && $location->provider_id !== $user->id ) {
            throw new AuthorizationException('You can only modify your own locations.' );
        }

        $type = $data['type'] ?? $exception->type;

        if ($type === 'holiday') {
            $data['start_time'] = null;
            $data['end_time'] = null;
        }

        return tap($exception)->update($data);
    }
}
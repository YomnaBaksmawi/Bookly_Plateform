<?php

namespace App\Modules\Service\Actions;

use App\Modules\Service\Models\Service;

class DeleteServiceAction
{
    public function execute(User $user, Service $service): void
    {
             $user->can('delete', $service)
            || abort(403);
        $service->delete();
    }
}
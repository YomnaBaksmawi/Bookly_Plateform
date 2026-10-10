<?php

namespace App\Modules\Service\Actions;

use App\Models\User;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Services\ServiceLocationValidator;
use Illuminate\Support\Facades\DB;

class UpdateServiceAction
{
    public function __construct(
        private ServiceLocationValidator $locationValidator
    ) {}

    public function execute(User $user, Service $service, array $data): Service
    {
        $user->can('update', $service) || abort(403);

        $locationIds = $data['location_ids'] ?? null;
        unset($data['location_ids']);

        if ($locationIds !== null) {
            $this->locationValidator->validate($user, $locationIds);
        }

        return DB::transaction(function () use ($service, $data, $locationIds) {
            $service->update($data);

            if ($locationIds !== null) {
                $service->locations()->sync($locationIds);
            }

            return $service->fresh()->load('locations');
        });
    }
}
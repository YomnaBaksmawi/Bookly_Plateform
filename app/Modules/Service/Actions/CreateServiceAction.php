<?php

namespace App\Modules\Service\Actions;

use App\Models\User;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Services\ServiceLocationValidator;
use Illuminate\Support\Facades\DB;

class CreateServiceAction
{
    public function __construct(
        private ServiceLocationValidator $locationValidator
    ) {}

    public function execute(User $user, array $data): Service
    {
        $user->can('create', Service::class) || abort(403);

        $locationIds = $data['location_ids'];
        unset($data['location_ids']);

        $this->locationValidator->validate($user, $locationIds);

        return DB::transaction(function () use ($user, $data, $locationIds) {
            $service = Service::create([
                'provider_id' => $user->id,
                ...$data,
            ]);

            $service->locations()->attach($locationIds);

            return $service->load('locations');
        });
    }
}
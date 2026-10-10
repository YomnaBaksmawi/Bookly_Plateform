<?php

namespace App\Modules\ProviderProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ProviderProfile\Actions\CreateProviderProfileAction;
use App\Modules\ProviderProfile\Http\Requests\StoreProviderProfileRequest;
use App\Modules\ProviderProfile\Http\Resources\ProviderProfileResource;
use App\Modules\ProviderProfile\Queries\GetProviderProfileQuery;
use App\Modules\ProviderProfile\Http\Requests\UpdateProviderProfileRequest;
use App\Modules\ProviderProfile\Actions\UpdateProviderProfileAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class ProviderProfileController extends Controller
{
    public function __construct(
        private readonly CreateProviderProfileAction $createProviderProfileAction,
          private readonly GetProviderProfileQuery $getProviderProfileQuery,
    private readonly UpdateProviderProfileAction $updateProviderProfileAction,
    ) {}

    public function store(StoreProviderProfileRequest $request): JsonResponse
    {
        $profile = $this->createProviderProfileAction->execute(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Provider profile created successfully.',
            'data' => new ProviderProfileResource($profile),
        ], 201);
    }
    public function show(Request $request): JsonResponse
{
    $profile = $this->getProviderProfileQuery->execute($request->user());

    return response()->json([
        'success' => true,
        'data' => new ProviderProfileResource($profile),
    ]);
}

public function update(UpdateProviderProfileRequest $request): JsonResponse
{
    $profile = $this->updateProviderProfileAction->execute(
        $request->user(),
        $request->validated()
    );

    return response()->json([
        'success' => true,
        'message' => 'Provider profile updated successfully.',
        'data' => new ProviderProfileResource($profile),
    ]);
}
}
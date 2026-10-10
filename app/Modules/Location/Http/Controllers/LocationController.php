<?php

namespace App\Modules\Location\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Location\Actions\CreateLocationAction;
use App\Modules\Location\Actions\DeleteLocationAction;
use App\Modules\Location\Actions\UpdateLocationAction;
use App\Modules\Location\Http\Requests\StoreLocationRequest;
use App\Modules\Location\Http\Requests\UpdateLocationRequest;
use App\Modules\Location\Http\Resources\LocationResource;
use App\Modules\Location\Models\Location;
use App\Modules\Location\Queries\GetLocationQuery;
use App\Modules\Location\Queries\ListLocationsQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Location\Policies\LocationPolicy;
use Illuminate\Support\Facades\Gate;


class LocationController extends Controller
{
    public function __construct(
        private readonly CreateLocationAction $createLocationAction,
        private readonly UpdateLocationAction $updateLocationAction,
        private readonly DeleteLocationAction $deleteLocationAction,
        private readonly ListLocationsQuery $listLocationsQuery,
        private readonly GetLocationQuery $getLocationQuery,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => LocationResource::collection(
                $this->listLocationsQuery->execute()
            ),
        ]);
    }

    public function store(StoreLocationRequest $request): JsonResponse
    {

        $location = $this->createLocationAction->execute($request->user(), $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Location created successfully.',
            'data' => new LocationResource($location),
        ], 201);
    }

    public function show(Location $location): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new LocationResource(   $this->getLocationQuery->execute($location)  ),
        ]);
    }

    public function update( UpdateLocationRequest $request,  Location $location ): JsonResponse {
        Gate::authorize('update', $location);
        $location = $this->updateLocationAction->execute(  $location, $request->validated() );

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully.',
            'data' => new LocationResource($location),
        ]);
    }

    public function destroy( Request $request, Location $location ): JsonResponse {
Gate::authorize('delete', $location);
        $this->deleteLocationAction->execute($location);

        return response()->json([
            'success' => true,
            'message' => 'Location deleted successfully.',
        ]);
    }
    public function trush(){
        $location=Location::onlyTrashed()->get();
        return response()->json([
            'success' => true,
            'data' => LocationResource::collection($location),
        ]);

    }
    public function restore( $id){
$location = Location::onlyTrashed()
        ->where('id', $id)
        ->firstOrFail();
                $location->restore();
   return response()->json([
            'success' => true,
            'message' => 'Location restore successfully.',
            'data' => new LocationResource($location),
        ]);
    }
}
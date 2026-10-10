<?php

namespace App\Modules\Service\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Service\Actions\CreateServiceAction;
use App\Modules\Service\Actions\DeleteServiceAction;
use App\Modules\Service\Actions\UpdateServiceAction;
use App\Modules\Service\Http\Requests\StoreServiceRequest;
use App\Modules\Service\Http\Requests\UpdateServiceRequest;
use App\Modules\Service\Http\Resources\ServiceResource;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Queries\GetServiceQuery;
use App\Modules\Service\Queries\ListServicesQuery;
use Illuminate\Http\JsonResponse;
use App\Modules\Service\Queries\SearchServicesQuery;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(
        private readonly CreateServiceAction $createServiceAction,
        private readonly UpdateServiceAction $updateServiceAction,
        private readonly DeleteServiceAction $deleteServiceAction,
        private readonly ListServicesQuery $listServicesQuery,
        private readonly GetServiceQuery $getServiceQuery,
        private readonly SearchServicesQuery $searchServicesQuery,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ServiceResource::collection( $this->listServicesQuery->execute() ),
        ]);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = $this->createServiceAction->execute(   $request->user(),  $request->validated() );

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully.',
            'data' => new ServiceResource($service),
        ], 201);
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new ServiceResource( $this->getServiceQuery->execute($service)),
        ]);
    }

    public function update(  UpdateServiceRequest $request,Service $service  ): JsonResponse {
        $service = $this->updateServiceAction->execute(
             $request->user(),
            $service,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully.',
            'data' => new ServiceResource($service),
        ]);
    }

    public function destroy(Service $service): JsonResponse
    {
        $this->deleteServiceAction->execute($service);

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully.',
        ]);
    }
    public function search(Request $request): JsonResponse
{
    $validated = $request->validate([
        'q' => ['required', 'string', 'min:2', 'max:100'],
        'location_id' => ['nullable', 'integer', 'exists:locations,id'],
    ]);

    $services = $this->searchServicesQuery->execute(
        $validated['q'],
        $validated['location_id'] ?? null
    );

    return response()->json([
        'success' => true,
        'data' => ServiceResource::collection($services),
    ]);
}
  public function trush(){
        $service=Service::onlyTrashed()->get();
        return response()->json([
            'success' => true,
            'data' => ServiceResource::collection($service),
        ]);

    }
    public function restore($id){
$service = Service::onlyTrashed()
        ->where('id', $id)
        ->firstOrFail();
                $service->restore();
   return response()->json([
            'success' => true,
            'message' => 'Location restore successfully.',
            'data' => new ServiceResource($service),
        ]);
    }
}
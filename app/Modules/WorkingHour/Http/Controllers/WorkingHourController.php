<?php
namespace App\Modules\WorkingHour\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WorkingHour\Actions\CreateWorkingHourAction;
use App\Modules\WorkingHour\Actions\DeleteWorkingHourAction;
use App\Modules\WorkingHour\Actions\UpdateWorkingHourAction;
use App\Modules\WorkingHour\Http\Requests\StoreWorkingHourRequest;
use App\Modules\WorkingHour\Http\Requests\UpdateWorkingHourRequest;
use App\Modules\WorkingHour\Http\Resources\WorkingHourResource;
use App\Modules\WorkingHour\Models\WorkingHour;
use App\Modules\WorkingHour\Queries\WorkingHourQuery;
use App\Modules\WorkingHour\Queries\GetWeeklyDaysOffQuery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkingHourController extends Controller
{
    public function __construct(
        private CreateWorkingHourAction $createWorkingHourAction,
        private UpdateWorkingHourAction $updateWorkingHourAction,
        private DeleteWorkingHourAction $deleteWorkingHourAction,
        private WorkingHourQuery $workingHourQuery,
    ) {
    }

    public function index()
    {
        $workingHours = $this->workingHourQuery->index(
            auth()->user()
        );

        return WorkingHourResource::collection($workingHours);
    }

    public function show(WorkingHour $workingHour)
    {
        return new WorkingHourResource( $workingHour->load('location') );
    }

    public function store(StoreWorkingHourRequest $request)
    {
        $workingHour = $this->createWorkingHourAction->execute( auth()->user(), $request->validated()  );

        return (new WorkingHourResource($workingHour->load('location') ))->additional([
            'success' => true,
            'message' => 'Working hour created successfully.',
        ]);
    }

    public function update(
        UpdateWorkingHourRequest $request,
        WorkingHour $workingHour
    ) {
        $workingHour = $this->updateWorkingHourAction->execute(
            auth()->user(),
            $workingHour,
            $request->validated()
        );

        return (new WorkingHourResource(
            $workingHour->load('location')
        ))->additional([
            'success' => true,
            'message' => 'Working hour updated successfully.',
        ]);
    }

    public function destroy(WorkingHour $workingHour): JsonResponse
    {
        $this->deleteWorkingHourAction->execute(
            auth()->user(),
            $workingHour
        );

        return response()->json([
            'success' => true,
            'message' => 'Working hour deleted successfully.',
            'errors' => null,
        ]);
    }
    public function byLocation(int $locationId)
{
    $workingHours = $this->workingHourQuery->forLocation($locationId);

    return WorkingHourResource::collection($workingHours);
}
public function byService(int $serviceId)
{
    $workingHours = $this->workingHourQuery->forService($serviceId);

    return WorkingHourResource::collection($workingHours);
}
public function daysOff(
    Request $request,
    int $locationId,
    GetWeeklyDaysOffQuery $query
): JsonResponse {
    return response()->json([
        'success' => true,
        'message' => 'Weekly days off retrieved successfully.',
        'data' => [
            'days_off' => $query->execute(
                $request->user(),
                $locationId
            ),
        ],
    ]);
}
}


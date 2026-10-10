<?php

namespace App\Modules\ScheduleException\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ScheduleException\Actions\CreateScheduleExceptionAction;
use App\Modules\ScheduleException\Actions\DeleteScheduleExceptionAction;
use App\Modules\ScheduleException\Actions\UpdateScheduleExceptionAction;
use App\Modules\ScheduleException\Http\Requests\StoreScheduleExceptionRequest;
use App\Modules\ScheduleException\Http\Requests\UpdateScheduleExceptionRequest;
use App\Modules\ScheduleException\Http\Resources\ScheduleExceptionResource;
use App\Modules\ScheduleException\Models\ScheduleException;
use App\Modules\ScheduleException\Queries\ScheduleExceptionQuery;
use Illuminate\Http\JsonResponse;

class ScheduleExceptionController extends Controller
{
    public function __construct(
        private CreateScheduleExceptionAction $createScheduleExceptionAction,
        private UpdateScheduleExceptionAction $updateScheduleExceptionAction,
        private DeleteScheduleExceptionAction $deleteScheduleExceptionAction,
        private ScheduleExceptionQuery $scheduleExceptionQuery,
    ) {}

    public function index()
    {
        $exceptions = $this->scheduleExceptionQuery->index( auth()->user()  );

        return ScheduleExceptionResource::collection($exceptions);
    }

    public function show(ScheduleException $scheduleException)
    {
        return new ScheduleExceptionResource( $scheduleException->load('location') );
    }

    public function byLocation(int $locationId)
    {
        $exceptions = $this->scheduleExceptionQuery->forLocation( $locationId );

        return ScheduleExceptionResource::collection($exceptions);
    }

    public function store(StoreScheduleExceptionRequest $request)
    {
        $exception = $this->createScheduleExceptionAction->execute( auth()->user(), $request->validated()  );

        return (new ScheduleExceptionResource($exception->load('location')  ))->additional([
            'success' => true,
            'message' => 'Schedule exception created successfully.',
        ]);
    }

    public function update( UpdateScheduleExceptionRequest $request, ScheduleException $scheduleException ) {
        $exception = $this->updateScheduleExceptionAction->execute(auth()->user(),   $scheduleException,  $request->validated()
        );

        return (new ScheduleExceptionResource(  $exception->load('location')  ))->additional([
            'success' => true,
            'message' => 'Schedule exception updated successfully.',
        ]);
    }

    public function destroy(  ScheduleException $scheduleException  ): JsonResponse {
        $this->deleteScheduleExceptionAction->execute(  auth()->user(), $scheduleException );

        return response()->json([
            'success' => true,
            'message' => 'Schedule exception deleted successfully.',
            'errors' => null,
        ]);
    }
}
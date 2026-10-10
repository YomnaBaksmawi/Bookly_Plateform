<?php
namespace App\Modules\PublicHoliday\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicHoliday\Actions\CreatePublicHolidayAction;
use App\Modules\PublicHoliday\Actions\DeletePublicHolidayAction;
use App\Modules\PublicHoliday\Actions\UpdatePublicHolidayAction;
use App\Modules\PublicHoliday\Http\Requests\StorePublicHolidayRequest;
use App\Modules\PublicHoliday\Http\Requests\UpdatePublicHolidayRequest;
use App\Modules\PublicHoliday\Http\Resources\PublicHolidayResource;
use App\Modules\PublicHoliday\Models\PublicHoliday;
use App\Modules\PublicHoliday\Queries\PublicHolidayQuery;
use Illuminate\Http\JsonResponse;

class PublicHolidayController extends Controller
{
    public function __construct(
        private CreatePublicHolidayAction $createPublicHolidayAction,
        private UpdatePublicHolidayAction $updatePublicHolidayAction,
        private DeletePublicHolidayAction $deletePublicHolidayAction,
        private PublicHolidayQuery $publicHolidayQuery,
    ) {}

    public function index()
    {
        return PublicHolidayResource::collection(
            $this->publicHolidayQuery->index(auth()->user())
        );
    }

    public function show(PublicHoliday $publicHoliday)
    {
        return new PublicHolidayResource(
            $publicHoliday->load('location')
        );
    }

    public function byLocation(int $locationId)
    {
        return PublicHolidayResource::collection(
            $this->publicHolidayQuery->forLocation($locationId)
        );
    }

    public function store(StorePublicHolidayRequest $request)
    {
        $holiday = $this->createPublicHolidayAction->execute(
            auth()->user(),
            $request->validated()
        );

        return (new PublicHolidayResource(
            $holiday->load('location')
        ))->additional([
            'success' => true,
            'message' => 'Public holiday created successfully.',
        ]);
    }

    public function update(
        UpdatePublicHolidayRequest $request,
        PublicHoliday $publicHoliday
    ) {
        $holiday = $this->updatePublicHolidayAction->execute(
            auth()->user(),
            $publicHoliday,
            $request->validated()
        );

        return (new PublicHolidayResource(
            $holiday->load('location')
        ))->additional([
            'success' => true,
            'message' => 'Public holiday updated successfully.',
        ]);
    }

    public function destroy(PublicHoliday $publicHoliday): JsonResponse
    {
        $this->deletePublicHolidayAction->execute(
            auth()->user(),
            $publicHoliday
        );

        return response()->json([
            'success' => true,
            'message' => 'Public holiday deleted successfully.',
            'errors' => null,
        ]);
    }
}


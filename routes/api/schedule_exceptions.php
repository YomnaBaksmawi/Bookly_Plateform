<?php

use App\Modules\ScheduleException\Http\Controllers\ScheduleExceptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('ability:schedule_exception:read')->group(function () {

        Route::get('schedule-exceptions', [ScheduleExceptionController::class, 'index'])
            ->middleware('throttle:60,1');

        Route::get('schedule-exceptions/{scheduleException}', [ScheduleExceptionController::class, 'show'])
            ->middleware('throttle:60,1');

        Route::get('locations/{locationId}/schedule-exceptions', [ScheduleExceptionController::class, 'byLocation'])
            ->middleware('throttle:60,1');
    });

    Route::middleware('ability:schedule_exception:write')->group(function () {

        Route::post('schedule-exceptions', [ScheduleExceptionController::class, 'store'])
            ->middleware('throttle:20,1');

        Route::put('schedule-exceptions/{scheduleException}', [ScheduleExceptionController::class, 'update'])
            ->middleware('throttle:20,1');

        Route::delete('schedule-exceptions/{scheduleException}', [ScheduleExceptionController::class, 'destroy'])
            ->middleware('throttle:20,1');
    });
});
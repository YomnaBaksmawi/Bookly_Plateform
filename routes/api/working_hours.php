<?php

use App\Modules\WorkingHour\Http\Controllers\WorkingHourController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('ability:working_hour:read')->group(function () {

        Route::get('working-hours', [WorkingHourController::class, 'index'])
            ->middleware('throttle:60,1');

        Route::get('working-hours/{workingHour}', [WorkingHourController::class, 'show'])
            ->middleware('throttle:60,1');

        Route::get('locations/{locationId}/working-hours', [WorkingHourController::class, 'byLocation'])
            ->middleware('throttle:60,1');

        Route::get('services/{serviceId}/working-hours', [WorkingHourController::class, 'byService'])
            ->middleware('throttle:60,1');
                Route::get('/provider/locations/{locationId}/working-hours/days-off', [WorkingHourController::class, 'daysOff'])
        ->middleware('throttle:30,1')
        ->name('provider.working-hours.days-off');
    });

    Route::middleware('ability:working_hour:write')->group(function () {

        Route::post('working-hours', [WorkingHourController::class, 'store'])
            ->middleware('throttle:20,1');

        Route::put('working-hours/{workingHour}', [WorkingHourController::class, 'update'])
            ->middleware('throttle:20,1');

        Route::delete('working-hours/{workingHour}', [WorkingHourController::class, 'destroy'])
            ->middleware('throttle:20,1');
    });

});

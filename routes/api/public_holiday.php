<?php

use App\Modules\PublicHoliday\Http\Controllers\PublicHolidayController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('ability:public_holiday:read')->group(function () {

        Route::get('public-holidays', [PublicHolidayController::class, 'index'])
            ->middleware('throttle:60,1');

        Route::get('public-holidays/{publicHoliday}', [PublicHolidayController::class, 'show'])
            ->middleware('throttle:60,1');

        Route::get('locations/{locationId}/public-holidays', [PublicHolidayController::class, 'byLocation'])
            ->middleware('throttle:60,1');
    });

    Route::middleware('ability:public_holiday:write')->group(function () {

        Route::post('public-holidays', [PublicHolidayController::class, 'store'])
            ->middleware('throttle:20,1');

        Route::put('public-holidays/{publicHoliday}', [PublicHolidayController::class, 'update'])
            ->middleware('throttle:20,1');

        Route::delete('public-holidays/{publicHoliday}', [PublicHolidayController::class, 'destroy'])
            ->middleware('throttle:20,1');
    });
});

Route::get('country/{country}', [PublicHolidayController::class, 'byCountry'])
    ->middleware('throttle:60,1')
    ->name('by-country');

Route::get('year/{year}', [PublicHolidayController::class, 'byYear'])
    ->middleware('throttle:60,1')
    ->name('by-year');
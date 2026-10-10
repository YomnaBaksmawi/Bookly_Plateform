<?php

use App\Modules\Service\Http\Controllers\ServiceController;
use App\Modules\ServiceLocation\Http\Controllers\ServiceLocationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Service Routes
|--------------------------------------------------------------------------
*/

Route::prefix('services')->name('services.')->group(function () {

    // Expensive public search
    Route::get('search', [ServiceController::class, 'search'])
        ->middleware('throttle:30,1')
        ->name('search');

    Route::get('category/{category}', [ServiceController::class, 'byCategory'])
        ->middleware('throttle:60,1')
        ->name('by-category');

    Route::get('provider/{provider}', [ServiceController::class, 'byProvider'])
        ->middleware('throttle:60,1')
        ->name('by-provider');

    Route::get('location/{location}', [ServiceController::class, 'byLocation'])
        ->middleware('throttle:60,1')
        ->name('by-location');

    Route::get('/', [ServiceController::class, 'index'])
        ->middleware('throttle:60,1')
        ->name('index');

    Route::get('{service}', [ServiceController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('show');
});


/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Service Management
    |--------------------------------------------------------------------------
    */

    Route::prefix('services')
        ->name('services.')
        ->middleware('role:provider,admin')
        ->group(function () {

            Route::post('/', [ServiceController::class, 'store'])
                ->middleware('throttle:20,1')
                ->name('store');

            Route::put('{service}', [ServiceController::class, 'update'])
                ->middleware('throttle:20,1')
                ->name('update');

            Route::delete('{service}', [ServiceController::class, 'destroy'])
                ->middleware('throttle:20,1')
                ->name('destroy');
        });


    /*
    |--------------------------------------------------------------------------
    | Service-Location Management
    |--------------------------------------------------------------------------
    */

    Route::prefix('service-locations')
        ->name('service-locations.')
        ->middleware('role:provider,admin')
        ->group(function () {

            Route::get('/', [ServiceLocationController::class, 'index'])
                ->middleware('throttle:60,1')
                ->name('index');

            Route::post('/', [ServiceLocationController::class, 'store'])
                ->middleware('throttle:20,1')
                ->name('store');

            Route::get('service/{service}', [ServiceLocationController::class, 'byService'])
                ->middleware('throttle:60,1')
                ->name('by-service');

            Route::get('location/{location}', [ServiceLocationController::class, 'byLocation'])
                ->middleware('throttle:60,1')
                ->name('by-location');

            Route::delete('{serviceLocation}', [ServiceLocationController::class, 'destroy'])
                ->middleware('throttle:20,1')
                ->name('destroy');
        });
});
<?php

use App\Modules\Location\Http\Controllers\LocationController;
use App\Modules\Location\Http\Controllers\ScheduleExceptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Location API Routes
|--------------------------------------------------------------------------
| Routes for location management, working hours, and schedule exceptions
*/

// Public routes (no authentication required)
Route::prefix('locations')->name('locations.')->group(function () {

    // Standard read operations
    Route::middleware('throttle:60,1')->group(function () {

        Route::get('/', [LocationController::class, 'index'])
            ->name('index');

        Route::get('{location}', [LocationController::class, 'show'])
            ->name('show');

        Route::get('provider/{provider}', [LocationController::class, 'byProvider'])
            ->name('by-provider');

        Route::get('city/{city}', [LocationController::class, 'byCity'])
            ->name('by-city');

        Route::get('country/{country}', [LocationController::class, 'byCountry'])
            ->name('by-country');
    });

    // Potentially expensive public queries
    Route::middleware('throttle:30,1')->group(function () {

        Route::get('nearby', [LocationController::class, 'nearby'])
            ->name('nearby');

        Route::get('search', [LocationController::class, 'search'])
            ->name('search');
    });
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Location management (for providers and admins)
    Route::prefix('locations')
        ->name('locations.')
        ->middleware([
            'role:provider,admin',
            'throttle:20,1',
        ])
        ->group(function () {

            Route::post('/', [LocationController::class, 'store'])
                ->name('store');

            Route::put('{location}', [LocationController::class, 'update'])
                ->name('update');

            Route::delete('{location}', [LocationController::class, 'destroy'])
                ->name('destroy');

            // Location actions
            Route::post('{location}/activate', [LocationController::class, 'activate'])
                ->name('activate');

            Route::post('{location}/deactivate', [LocationController::class, 'deactivate'])
                ->name('deactivate');

            Route::post('{location}/set-primary', [LocationController::class, 'setPrimary'])
                ->name('set-primary');

            // Location images
            Route::post('{location}/images', [LocationController::class, 'uploadImages'])
                ->middleware('throttle:10,1')
                ->name('upload-images');

            Route::delete('{location}/images/{image}', [LocationController::class, 'deleteImage'])
                ->middleware('throttle:10,1')
                ->name('delete-image');
        });
});
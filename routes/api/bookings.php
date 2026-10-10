<?php

use App\Modules\Availability\Http\Controllers\AvailabilityController;
use App\Modules\Booking\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IdempotencyMiddleware;

/*
|--------------------------------------------------------------------------
| Booking API Routes
|--------------------------------------------------------------------------
| Routes for booking management and availability checking
*/

Route::middleware('auth:sanctum')->group(function () {

    // Booking management
    Route::prefix('bookings')->name('bookings.')->group(function () {

        // Read operations
        Route::middleware('throttle:60,1')->group(function () {

            Route::get('/', [BookingController::class, 'index'])
                ->name('index');

            Route::get('/{booking}', [BookingController::class, 'show'])
                ->middleware('ability:booking:read');

            Route::get('customer/{customer}', [BookingController::class, 'customerBookings'])
                ->name('customer');

            Route::get('provider/{provider}', [BookingController::class, 'providerBookings'])
                ->name('provider');

            Route::get('status/{status}', [BookingController::class, 'byStatus'])
                ->name('by-status');

            Route::get('date-range', [BookingController::class, 'byDateRange'])
                ->name('date-range');
        });

        // Create booking
        Route::post('/', [BookingController::class, 'store'])
            ->middleware([
                'throttle:20,1',
                IdempotencyMiddleware::class,
            ])
            ->name('store');

        // Booking actions
        Route::middleware('throttle:20,1')->group(function () {

            Route::patch('{booking}/status', [BookingController::class, 'changeStatus']);

            Route::patch('{booking}/cancel', [BookingController::class, 'cancel']);

            Route::patch('{booking}/reschedule', [BookingController::class, 'reschedule']);
        });
    });

    // Availability
    Route::get('/availability', [AvailabilityController::class, 'index'])
        ->middleware('throttle:60,1');
});
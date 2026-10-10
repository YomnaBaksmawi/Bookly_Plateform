<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ApiCsrfTokenController;


Route::prefix('v1')
    ->middleware('csrf.api')
    ->group(function () {

        require __DIR__ . '/api/auth.php';
        require __DIR__ . '/api/providers.php';
        require __DIR__ . '/api/categories.php';
        require __DIR__ . '/api/services.php';
        require __DIR__ . '/api/locations.php';
        require __DIR__ . '/api/working_hours.php';
        require __DIR__ . '/api/schedule_exceptions.php';
        require __DIR__ . '/api/public_holiday.php';
        require __DIR__ . '/api/bookings.php';
        require __DIR__ . '/api/payments.php';
        require __DIR__ . '/api/admin.php';

        Route::get('health', HealthController::class)
            ->middleware('throttle:60,1')
            ->name('health');

        Route::get('docs', function () {
            return response()->json([
                'message' => 'Bookly API Documentation',
                'version' => 'v1',
                'available_endpoints' => [
                    'auth' => '/api/v1/auth/*',
                    'provider' => '/api/v1/provider/*',
                    'category' => '/api/v1/categories/*',
                    'service' => '/api/v1/services/*',
                    'working_hour' => '/api/v1/working_hours/*',
                    'schedule_exception' => '/api/v1/schedule_exceptions/*',
                    'public_holiday' => '/api/v1/public_holidays/*',
                    'location' => '/api/v1/locations/*',
                    'booking' => '/api/v1/bookings/*',
                    'health' => '/api/v1/health',
                    'docs' => '/api/v1/docs',
                ],
            ]);
        })->name('docs');
    });

Route::prefix('v1')->group(function () {

  Route::middleware('auth:sanctum')->group(function () {
    Route::get('/csrf-token', ApiCsrfTokenController::class);
});

});
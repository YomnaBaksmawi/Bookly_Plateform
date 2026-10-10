<?php

use App\Modules\AuditLog\Http\Controllers\AuditLogController;
use App\Modules\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Modules\Location\Http\Controllers\LocationController;
use App\Modules\Service\Http\Controllers\ServiceController;
use App\Modules\Notification\Controllers\NotificationController;
/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
| Routes for admin panel and system management
*/

Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // User Management
        Route::prefix('users')
            ->name('users.')
            ->group(function () {

                // Read operations
                Route::middleware('throttle:60,1')->group(function () {
                    Route::get('/', [AdminController::class, 'index'])
                        ->name('index');

                    Route::get('{user}', [AdminController::class, 'show'])
                        ->name('show');
                });

                // Write / state-changing operations
                Route::middleware('throttle:20,1')->group(function () {
                    Route::post('/', [AdminController::class, 'store'])
                        ->name('store');

                    Route::put('{user}', [AdminController::class, 'update'])
                        ->name('update');

                    Route::delete('{user}', [AdminController::class, 'destroy'])
                        ->name('destroy');

                    Route::post('{user}/suspend', [AdminController::class, 'suspend'])
                        ->name('suspend');

                    Route::post('{user}/activate', [AdminController::class, 'activate'])
                        ->name('activate');
                });
                
            });

        // Audit Logs
        Route::prefix('audit-logs')
            ->name('audit-logs.')
            ->middleware('throttle:30,1')
            ->group(function () {

                Route::get('/', [AuditLogController::class, 'index'])
                    ->name('index');

                Route::get('{auditLog}', [AuditLogController::class, 'show'])
                    ->name('show');
            });

        // Reports
        Route::prefix('reports')
            ->name('reports.')
            ->middleware('throttle:20,1')
            ->group(function () {

                Route::get('bookings', [AdminController::class, 'bookingReport'])
                    ->name('bookings');

                Route::get('revenue', [AdminController::class, 'revenue'])
                    ->name('revenue');

                Route::get('providers', [AdminController::class, 'providerReport'])
                    ->name('providers');

                Route::get('services', [AdminController::class, 'serviceReport'])
                    ->name('services');

                Route::get('locations', [AdminController::class, 'locations'])
                    ->name('locations');

                Route::get('most-booked-services', [AdminController::class, 'mostBookedServices'])
                    ->name('most-booked-services');
            });
    });
    
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('locations/trash', [LocationController::class, 'trush']);
    Route::post('locations/{location}/restore', [LocationController::class, 'restore']);
        Route::get('services/trash', [ServiceController::class, 'trush']);
    Route::post('services/{service}/restore', [ServiceController::class, 'restore']);



    Route::get('notifications', [  NotificationController::class,'index',]);

Route::get('notifications/unread-count', [ NotificationController::class,'unreadCount',]);

Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead',]);
Route::post( 'device-tokens', [NotificationController::class, 'storeDeviceToken']
);
});
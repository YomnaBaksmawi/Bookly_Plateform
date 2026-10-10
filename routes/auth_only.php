<?php

use App\Modules\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Only API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
    // Auth Routes
    Route::prefix('auth')->name('auth.')->group(function () {
        // Public routes
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login', [AuthController::class, 'login'])->name('login');
        
        // Protected routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('profile', [AuthController::class, 'profile'])->name('profile');
            Route::put('profile', [AuthController::class, 'updateProfile'])->name('update-profile');
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('logout-all');
        });
    });
    
    // Health check
    Route::get('health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
        ]);
    })->name('health');
    
    // API docs
    Route::get('docs', function () {
        return response()->json([
            'message' => 'Bookly API - Auth Module Only',
            'version' => 'v1',
            'endpoints' => [
                'POST /api/v1/auth/register',
                'POST /api/v1/auth/login', 
                'GET /api/v1/auth/profile',
                'PUT /api/v1/auth/profile',
                'POST /api/v1/auth/logout',
                'POST /api/v1/auth/logout-all',
                'GET /api/v1/health',
                'GET /api/v1/docs',
            ],
        ]);
    })->name('docs');
});
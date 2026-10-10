<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Category\Http\Controllers\CategoryController;

Route::middleware('auth:sanctum')->group(function () {

    // Read operations
    Route::middleware('throttle:60,1')->group(function () {

        Route::get('categories', [CategoryController::class, 'index']);

        Route::get('categories/{category}', [CategoryController::class, 'show']);
    });

    // Write operations
    Route::middleware(['role:admin,provider','throttle:20,1', ])->group(function () {

        Route::post('categories', [CategoryController::class, 'store']);

        Route::put('categories/{category}', [CategoryController::class, 'update']);

        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
    });

});
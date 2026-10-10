<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ProviderProfile\Http\Controllers\ProviderProfileController;

Route::prefix('provider')
    ->middleware('auth:sanctum')
    ->group(function () {

        Route::post('profile', [ProviderProfileController::class, 'store'])
            ->middleware('throttle:20,1')
            ->name('provider.profile.store');

        Route::get('profile', [ProviderProfileController::class, 'show'])
            ->middleware('throttle:60,1')
            ->name('provider.profile.show');

        Route::put('profile', [ProviderProfileController::class, 'update'])
            ->middleware('throttle:20,1')
            ->name('provider.profile.update');
    });
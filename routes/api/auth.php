<?php

use App\Modules\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

Route::get('email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        return response()->json([
            'message' => 'Invalid verification link'
        ], 403);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json([
            'message' => 'Email already verified'
        ]);
    }

    $user->markEmailAsVerified();

    return response()->json([
        'message' => 'Email verified successfully'
    ]);
})->middleware('signed')->name('verification.verify');


Route::prefix('auth')->name('auth.')->group(function () {

    // Public routes
    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login');


    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('profile', [AuthController::class, 'profile'])
            ->name('profile');

        Route::put('profile', [AuthController::class, 'updateProfile'])
            ->name('update-profile');

        Route::post('logout', [AuthController::class, 'logout'])
            ->name('logout');

        Route::post('logout-all', [AuthController::class, 'logoutAll'])
            ->name('logout-all');

        Route::post('verify-email', [AuthController::class, 'verifyEmail'])
            ->name('verify-email');

        Route::post('resend-verification', [AuthController::class, 'resendVerification'])
            ->middleware('throttle:5,1')
            ->name('resend-verification');

        Route::post('change-password', [AuthController::class, 'changePassword'])
            ->middleware('throttle:5,1')
            ->name('change-password');
    });
});
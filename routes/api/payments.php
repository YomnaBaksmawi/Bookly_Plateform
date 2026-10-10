<?php

use App\Modules\Payment\Http\Controllers\PaymentController;
use App\Modules\Payment\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Payment Webhook
|--------------------------------------------------------------------------
| The payment provider calls this endpoint directly.
| It must not require Sanctum authentication.
*/
Route::post('/payments/webhook/test-signature', [
    PaymentWebhookController::class,
    'generateTestSignature',
])->middleware('throttle:30,1');
Route::post('/payments/webhook', [PaymentWebhookController::class, 'handle'])
    ->middleware('throttle:30,1')
    ->name('payments.webhook');


/*
|--------------------------------------------------------------------------
| Authenticated Payment Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('payments')
        ->name('payments.')
        ->group(function () {
            //get payments
Route::get('/', [PaymentController::class, 'index'])
    ->middleware('throttle:30,1')
    ->name('index');
            // Create payment
            Route::post('/', [PaymentController::class, 'store'])
                ->middleware('throttle:10,1')
                ->name('store');

            // Check payment status
            Route::get('{payment}', [PaymentController::class, 'show'])
                ->middleware('throttle:30,1')
                ->name('show');
        });
});
Route::middleware([
    'auth:sanctum',
    'role:provider',
])->prefix('provider')->group(function () {
    Route::get('payments', [PaymentController::class, 'providerIndex'])
        ->middleware('throttle:30,1')
        ->name('provider.payments.index');
});
    // Refund management
    // Route::prefix('refunds')->name('refunds.')->group(function () {
    //     Route::get('/', [RefundController::class, 'index'])->name('index');
    //     Route::post('/', [RefundController::class, 'store'])->name('store');
    //     Route::get('{refund}', [RefundController::class, 'show'])->name('show');
    //     Route::post('{refund}/approve', [RefundController::class, 'approve'])->name('approve');
    //     Route::post('{refund}/reject', [RefundController::class, 'reject'])->name('reject');

    //     Route::get('payment/{payment}', [RefundController::class, 'byPayment'])->name('by-payment');
    //     Route::get('booking/{booking}', [RefundController::class, 'byBooking'])->name('by-booking');
    // });

//     // Payment reports (admin and providers)
//     Route::prefix('payment-reports')->name('payment-reports.')->middleware('role:provider,admin')->group(function () {
//         Route::get('summary', [PaymentController::class, 'summary'])->name('summary');
//         Route::get('daily', [PaymentController::class, 'dailyReport'])->name('daily');
//         Route::get('monthly', [PaymentController::class, 'monthlyReport'])->name('monthly');
//         Route::get('provider-earnings', [PaymentController::class, 'providerEarnings'])->name('provider-earnings');
//         Route::get('export', [PaymentController::class, 'export'])->name('export');
//     });
// });




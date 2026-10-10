<?php

use App\Modules\Notification\Http\Controllers\NotificationController;
use App\Modules\Notification\Http\Controllers\NotificationSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Notification API Routes
|--------------------------------------------------------------------------
| Routes for managing notifications and notification settings
*/

Route::middleware('auth:sanctum')->prefix('notifications')->name('notifications.')->group(function () {

    // User notifications
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('unread', [NotificationController::class, 'unread'])->name('unread');
    Route::get('unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::get('{notification}', [NotificationController::class, 'show'])->name('show');

    // Mark notifications
    Route::post('{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('{notification}', [NotificationController::class, 'delete'])->name('delete');
    Route::delete('clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');

    // Notification filters
    Route::get('type/{type}', [NotificationController::class, 'byType'])->name('by-type');
    Route::get('date-range', [NotificationController::class, 'byDateRange'])->name('date-range');

    // Notification settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [NotificationSettingsController::class, 'index'])->name('index');
        Route::put('/', [NotificationSettingsController::class, 'update'])->name('update');
        Route::get('types', [NotificationSettingsController::class, 'availableTypes'])->name('types');
        Route::post('test', [NotificationSettingsController::class, 'test'])->name('test');
    });

    // Push notification management
    Route::prefix('push')->name('push.')->group(function () {
        Route::post('subscribe', [NotificationController::class, 'subscribeToPush'])->name('subscribe');
        Route::delete('unsubscribe', [NotificationController::class, 'unsubscribeFromPush'])->name('unsubscribe');
        Route::get('subscription', [NotificationController::class, 'getPushSubscription'])->name('subscription');
    });
});

// Admin notification management
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/notifications')->name('admin.notifications.')->group(function () {
    Route::post('broadcast', [NotificationController::class, 'broadcast'])->name('broadcast');
    Route::post('send-to-role', [NotificationController::class, 'sendToRole'])->name('send-to-role');
    Route::post('send-to-users', [NotificationController::class, 'sendToUsers'])->name('send-to-users');
    Route::get('templates', [NotificationController::class, 'templates'])->name('templates');
    Route::post('templates', [NotificationController::class, 'createTemplate'])->name('create-template');
});

<?php

namespace App\Modules\Notification\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Notification\Requests\StoreDeviceTokenRequest;
use App\Models\UserDeviceToken;
class NotificationController
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Notifications retrieved successfully.',
            'data' => $notifications,
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()
            ->unreadNotifications()
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Unread notification count retrieved successfully.',
            'data' => [
                'count' => $count,
            ],
        ]);
    }

    public function markAsRead(
        Request $request,
        string $notification
    ): JsonResponse {
        $userNotification = $request->user()
            ->notifications()
            ->findOrFail($notification);

        $userNotification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'data' => [
                'id' => $userNotification->id,
                'read_at' => $userNotification->read_at,
            ],
        ]);
    }
    public function storeDeviceToken(StoreDeviceTokenRequest $request): JsonResponse {
    $deviceToken = UserDeviceToken::updateOrCreate(
        [
            'user_id' => $request->user()->id,
            'token' => $request->validated('token'),
        ],
        [
            'device_type' => $request->validated('device_type'),
            'last_used_at' => now(),
        ]
    );
    return response()->json([
        'success' => true,
        'message' => 'Device token registered successfully.',
        'data' => [
            'id' => $deviceToken->id,
            'device_type' => $deviceToken->device_type,
            'last_used_at' => $deviceToken->last_used_at,
        ],
    ], 201);
}
}
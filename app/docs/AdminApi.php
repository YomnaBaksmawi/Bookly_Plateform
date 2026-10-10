<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class AdminApi
{
    // ─────────────────────────────────────────────
    // Users
    // ─────────────────────────────────────────────

    #[OA\Get(
        path: '/admin/users',
        summary: 'List users',
        description: 'Returns users for admin management. Requires admin role.',
        tags: ['Admin - Users'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Users retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
        ]
    )]
    public function usersIndex(): void
    {
    }

    #[OA\Get(
        path: '/admin/users/{user}',
        summary: 'Get user',
        description: 'Returns a specific user. Requires admin role.',
        tags: ['Admin - Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'User ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    public function usersShow(): void
    {
    }

    #[OA\Post(
        path: '/admin/users',
        summary: 'Create user',
        description: 'Creates a user from the admin panel. Requires admin role.',
        tags: ['Admin - Users'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'User created successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function usersStore(): void
    {
    }

    #[OA\Put(
        path: '/admin/users/{user}',
        summary: 'Update user',
        description: 'Updates a user. Requires admin role.',
        tags: ['Admin - Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'User ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User updated successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function usersUpdate(): void
    {
    }

    #[OA\Delete(
        path: '/admin/users/{user}',
        summary: 'Delete user',
        description: 'Deletes a user. Requires admin role.',
        tags: ['Admin - Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'User ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    public function usersDestroy(): void
    {
    }

    #[OA\Post(
        path: '/admin/users/{user}/suspend',
        summary: 'Suspend user',
        description: 'Suspends a user account. Requires admin role.',
        tags: ['Admin - Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'User ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User suspended successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    public function usersSuspend(): void
    {
    }

    #[OA\Post(
        path: '/admin/users/{user}/activate',
        summary: 'Activate user',
        description: 'Activates a suspended user account. Requires admin role.',
        tags: ['Admin - Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'User ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User activated successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    public function usersActivate(): void
    {
    }

    // ─────────────────────────────────────────────
    // Audit Logs
    // ─────────────────────────────────────────────

    #[OA\Get(
        path: '/admin/audit-logs',
        summary: 'List audit logs',
        description: 'Returns audit logs for system activity. Requires admin role.',
        tags: ['Admin - Audit Logs'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Audit logs retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
        ]
    )]
    public function auditLogsIndex(): void
    {
    }

    #[OA\Get(
        path: '/admin/audit-logs/{auditLog}',
        summary: 'Get audit log',
        description: 'Returns a specific audit log. Requires admin role.',
        tags: ['Admin - Audit Logs'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'auditLog',
                in: 'path',
                required: true,
                description: 'Audit log ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Audit log retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
            new OA\Response(response: 404, description: 'Audit log not found'),
        ]
    )]
    public function auditLogsShow(): void
    {
    }

    // ─────────────────────────────────────────────
    // Reports
    // ─────────────────────────────────────────────

    #[OA\Get(
        path: '/admin/reports/bookings',
        summary: 'Booking report',
        description: 'Returns booking statistics and metrics for administrators.',
        tags: ['Admin - Reports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Booking report retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
        ]
    )]
    public function bookingReport(): void
    {
    }

    #[OA\Get(
        path: '/admin/reports/revenue',
        summary: 'Revenue report',
        description: 'Returns revenue statistics for administrators.',
        tags: ['Admin - Reports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Revenue report retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
        ]
    )]
    public function revenueReport(): void
    {
    }

    #[OA\Get(
        path: '/admin/reports/providers',
        summary: 'Provider report',
        description: 'Returns provider statistics for administrators.',
        tags: ['Admin - Reports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Provider report retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
        ]
    )]
    public function providerReport(): void
    {
    }

    #[OA\Get(
        path: '/admin/reports/services',
        summary: 'Service report',
        description: 'Returns service statistics for administrators.',
        tags: ['Admin - Reports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Service report retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
        ]
    )]
    public function serviceReport(): void
    {
    }

    #[OA\Get(
        path: '/admin/reports/locations',
        summary: 'Location report',
        description: 'Returns location statistics for administrators.',
        tags: ['Admin - Reports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Location report retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
        ]
    )]
    public function locationReport(): void
    {
    }

    #[OA\Get(
        path: '/admin/reports/most-booked-services',
        summary: 'Most booked services report',
        description: 'Returns services ordered by booking count.',
        tags: ['Admin - Reports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Most booked services retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
        ]
    )]
    public function mostBookedServices(): void
    {
    }
    // ─────────────────────────────────────────────
    // Notifications
    // 
    

    // ─────────────────────────────────────────────
    // Device Tokens
    // ─────────────────────────────────────────────

    #[OA\Post(
        path: '/admin/device-tokens',
        summary: 'Register device token',
        description: 'Registers or updates an FCM device token for the authenticated user.',
        tags: ['Admin - Notifications'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token'],
                properties: [
                    new OA\Property(
                        property: 'token',
                        type: 'string',
                        maxLength: 2000,
                        example: 'fcm_device_token_example'
                    ),
                    new OA\Property(
                        property: 'device_type',
                        type: 'string',
                        enum: ['web', 'android', 'ios'],
                        nullable: true,
                        example: 'web'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Device token registered successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function storeDeviceToken(): void
    {
    }
    #[OA\Get(
        path: '/admin/notifications',
        summary: 'List admin notifications',
        description: 'Returns notifications for the authenticated admin. Requires admin role.',
        tags: ['Admin - Notifications'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notifications retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Admin access required'
            ),
        ]
    )]
    public function notificationsIndex(): void
    {
    }

    #[OA\Get(
        path: '/admin/notifications/unread-count',
        summary: 'Get unread notification count',
        description: 'Returns the number of unread notifications for the authenticated admin.',
        tags: ['Admin - Notifications'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Unread notification count retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Admin access required'
            ),
        ]
    )]
    public function notificationsUnreadCount(): void
    {
    }

    #[OA\Patch(
        path: '/admin/notifications/{notification}/read',
        summary: 'Mark notification as read',
        description: 'Marks an admin notification as read. Requires admin role.',
        tags: ['Admin - Notifications'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'notification',
                in: 'path',
                required: true,
                description: 'Notification UUID',
                schema: new OA\Schema(
                    type: 'string',
                    format: 'uuid'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification marked as read'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Admin access required'
            ),
            new OA\Response(
                response: 404,
                description: 'Notification not found'
            ),
        ]
    )]
    public function notificationsMarkAsRead(): void
    {
    }

    // ─────────────────────────────────────────────
    // Trash & Restore
    // ─────────────────────────────────────────────

    #[OA\Get(
        path: '/admin/locations/trash',
        summary: 'List trashed locations',
        description: 'Returns all soft-deleted locations. Requires admin role.',
        tags: ['Admin - Locations'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Trashed locations retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
        ]
    )]
    public function locationsTrash(): void
    {
    }

    #[OA\Post(
        path: '/admin/locations/{location}/restore',
        summary: 'Restore a trashed location',
        description: 'Restores a soft-deleted location. Requires admin role.',
        tags: ['Admin - Locations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'location',
                in: 'path',
                required: true,
                description: 'Location ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Location restored successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
            new OA\Response(response: 404, description: 'Location not found'),
        ]
    )]
    public function locationsRestore(): void
    {
    }

    #[OA\Get(
        path: '/admin/services/trash',
        summary: 'List trashed services',
        description: 'Returns all soft-deleted services. Requires admin role.',
        tags: ['Admin - Services'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Trashed services retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
        ]
    )]
    public function servicesTrash(): void
    {
    }

    #[OA\Post(
        path: '/admin/services/{service}/restore',
        summary: 'Restore a trashed service',
        description: 'Restores a soft-deleted service. Requires admin role.',
        tags: ['Admin - Services'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'service',
                in: 'path',
                required: true,
                description: 'Service ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Service restored successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Admin access required'),
            new OA\Response(response: 404, description: 'Service not found'),
        ]
    )]
    public function servicesRestore(): void
    {
    }

}
<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class BookingApi
{
    /*
    |--------------------------------------------------------------------------
    | Bookings
    |--------------------------------------------------------------------------
    */

    // GET /bookings
    #[OA\Get(
        path: '/bookings',
        tags: ['Bookings'],
        summary: 'List bookings',
        security: [
            ['sanctum' => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Bookings retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
    public function index() {}

    // GET /bookings/{booking}
    #[OA\Get(
        path: '/bookings/{booking}',
        tags: ['Bookings'],
        summary: 'Get booking',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'booking',
                in: 'path',
                required: true,
                description: 'Booking ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Booking retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 404,
                description: 'Booking not found'
            ),
        ]
    )]
    public function show() {}

 

    // POST /bookings
 #[OA\Post(
    path: '/bookings',
    tags: ['Bookings'],
    summary: 'Create a booking',
    description: 'Creates a booking using a booking slot and an idempotency key to prevent duplicate bookings.',
    security: [
        ['sanctum' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'Idempotency-Key',
            in: 'header',
            required: true,
            description: 'Unique key for safely retrying the same booking request.',
            schema: new OA\Schema(
                type: 'string',
                example: 'booking-9f7c2d4a-20260916'
            )
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: [
                'booking_slot_id',
            ],
            properties: [
                new OA\Property(
                    property: 'booking_slot_id',
                    type: 'integer',
                    example: 9
                ),
                new OA\Property(
                    property: 'notes',
                    type: 'string',
                    nullable: true,
                    example: 'Test booking'
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Booking created successfully'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
        new OA\Response(
            response: 409,
            description: 'Booking conflict or duplicate idempotency request'
        ),
        new OA\Response(
            response: 422,
            description: 'Validation or availability error'
        ),
    ]
)]
public function store() {}

    // PATCH /bookings/{booking}/status
    #[OA\Patch(
        path: '/bookings/{booking}/status',
        tags: ['Bookings'],
        summary: 'Change booking status',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'booking',
                in: 'path',
                required: true,
                description: 'Booking ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: [
                            'pending',
                            'confirmed',
                            'cancelled',
                            'completed',
                            'no_show',
                        ],
                        example: 'confirmed'
                    ),
                    new OA\Property(
                        property: 'reason',
                        type: 'string',
                        nullable: true,
                        example: 'Provider confirmed the appointment.'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Booking status changed successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 409,
                description: 'Invalid booking state transition'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function changeStatus() {}

    // PATCH /bookings/{booking}/cancel
    #[OA\Patch(
        path: '/bookings/{booking}/cancel',
        tags: ['Bookings'],
        summary: 'Cancel booking',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'booking',
                in: 'path',
                required: true,
                description: 'Booking ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'reason',
                        type: 'string',
                        nullable: true,
                        example: 'Customer requested cancellation.'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Booking cancelled successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 409,
                description: 'Booking cannot be cancelled'
            ),
        ]
    )]
    public function cancel() {}

    // PATCH /bookings/{booking}/reschedule
 #[OA\Patch(
    path: '/bookings/{booking}/reschedule',
    tags: ['Bookings'],
    summary: 'Reschedule booking',
    security: [
        ['sanctum' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'booking',
            in: 'path',
            required: true,
            description: 'Booking ID',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['booking_slot_id'],
            properties: [
                new OA\Property(
                    property: 'booking_slot_id',
                    type: 'integer',
                    example: 25,
                    description: 'ID of the available booking slot to move the booking to.'
                ),
                new OA\Property(
                    property: 'reason',
                    type: 'string',
                    nullable: true,
                    example: 'Customer requested a different time.'
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Booking rescheduled successfully'
        ),
        new OA\Response(
            response: 403,
            description: 'Unauthorized'
        ),
        new OA\Response(
            response: 404,
            description: 'Booking or booking slot not found'
        ),
        new OA\Response(
            response: 409,
            description: 'Requested booking slot is unavailable'
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error'
        ),
    ]
)]
public function reschedule() {}
    /*
    |--------------------------------------------------------------------------
    | Availability
    |--------------------------------------------------------------------------
    */

    // GET /availability
   #[OA\Get(
    path: '/availability',
    tags: ['Availability'],
    summary: 'Get available booking slots',
    description: 'Returns available time slots for a service, location, and date.',
    security: [
        ['sanctum' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'service_id',
            in: 'query',
            required: true,
            description: 'Service ID',
            schema: new OA\Schema(
                type: 'integer',
                example: 1
            )
        ),

        new OA\Parameter(
            name: 'location_id',
            in: 'query',
            required: true,
            description: 'Location ID where the service is available.',
            schema: new OA\Schema(
                type: 'integer',
                example: 1
            )
        ),

        new OA\Parameter(
            name: 'date',
            in: 'query',
            required: true,
            description: 'Date for which availability is requested.',
            schema: new OA\Schema(
                type: 'string',
                format: 'date',
                example: '2026-09-20'
            )
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Availability retrieved successfully'
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error'
        ),
    ]
)]
public function availability() {}
}
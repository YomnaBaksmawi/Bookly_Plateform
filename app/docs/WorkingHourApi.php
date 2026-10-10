<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class WorkingHourApi
{
    // GET /working-hours
    #[OA\Get(
        path: '/working-hours',
        tags: ['Working Hours'],
        summary: 'List working hours',
        description: 'Requires the working_hour:read ability.',
        security: [
            ['sanctum' => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Working hours retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing working_hour:read ability'
            ),
        ]
    )]
    public function index() {}

    // GET /working-hours/{workingHour}
    #[OA\Get(
        path: '/working-hours/{workingHour}',
        tags: ['Working Hours'],
        summary: 'Get a working hour',
        description: 'Requires the working_hour:read ability.',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'workingHour',
                in: 'path',
                required: true,
                description: 'Working hour ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Working hour retrieved successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing working_hour:read ability'
            ),
            new OA\Response(
                response: 404,
                description: 'Working hour not found'
            ),
        ]
    )]
    public function show() {}

    // GET /locations/{locationId}/working-hours
    #[OA\Get(
        path: '/locations/{locationId}/working-hours',
        tags: ['Working Hours'],
        summary: 'Get working hours by location',
        description: 'Returns working hours configured for a specific location.',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'locationId',
                in: 'path',
                required: true,
                description: 'Location ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Location working hours retrieved successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing working_hour:read ability'
            ),
        ]
    )]
    public function byLocation() {}

    // GET /services/{serviceId}/working-hours
    #[OA\Get(
        path: '/services/{serviceId}/working-hours',
        tags: ['Working Hours'],
        summary: 'Get working hours by service',
        description: 'Returns working hours associated with the locations where the service is available.',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'serviceId',
                in: 'path',
                required: true,
                description: 'Service ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Service working hours retrieved successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing working_hour:read ability'
            ),
        ]
    )]
    public function byService() {}

    // POST /working-hours
    #[OA\Post(
        path: '/working-hours',
        tags: ['Working Hours'],
        summary: 'Create a working hour',
        description: 'Creates a working-hour schedule. Requires the working_hour:write ability.',
        security: [
            ['sanctum' => []]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'location_id',
                    'day_of_week',
                    'start_time',
                    'end_time',
                ],
                properties: [
                    new OA\Property(
                        property: 'location_id',
                        type: 'integer',
                        example: 1
                    ),
                    new OA\Property(
                        property: 'day_of_week',
                        type: 'string',
                        enum: [
                            'monday',
                            'tuesday',
                            'wednesday',
                            'thursday',
                            'friday',
                            'saturday',
                            'sunday',
                        ],
                        example: 'monday'
                    ),
                    new OA\Property(
                        property: 'start_time',
                        type: 'string',
                        format: 'time',
                        example: '09:00:00'
                    ),
                    new OA\Property(
                        property: 'end_time',
                        type: 'string',
                        format: 'time',
                        example: '17:00:00'
                    ),
                    new OA\Property(
                        property: 'is_active',
                        type: 'boolean',
                        example: true
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Working hour created successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing working_hour:write ability'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function store() {}

    // PUT /working-hours/{workingHour}
    #[OA\Put(
        path: '/working-hours/{workingHour}',
        tags: ['Working Hours'],
        summary: 'Update a working hour',
        description: 'Updates an existing working-hour schedule. Requires the working_hour:write ability.',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'workingHour',
                in: 'path',
                required: true,
                description: 'Working hour ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'day_of_week',
                        type: 'string',
                        enum: [
                            'monday',
                            'tuesday',
                            'wednesday',
                            'thursday',
                            'friday',
                            'saturday',
                            'sunday',
                        ],
                        example: 'monday'
                    ),
                    new OA\Property(
                        property: 'start_time',
                        type: 'string',
                        format: 'time',
                        example: '10:00'
                    ),
                    new OA\Property(
                        property: 'end_time',
                        type: 'string',
                        format: 'time',
                        example: '18:00'
                    ),
                    new OA\Property(
                        property: 'is_active',
                        type: 'boolean',
                        example: true
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Working hour updated successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing working_hour:write ability'
            ),
            new OA\Response(
                response: 404,
                description: 'Working hour not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function update() {}

    // DELETE /working-hours/{workingHour}
    #[OA\Delete(
        path: '/working-hours/{workingHour}',
        tags: ['Working Hours'],
        summary: 'Delete a working hour',
        description: 'Deletes a working-hour schedule. Requires the working_hour:write ability.',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'workingHour',
                in: 'path',
                required: true,
                description: 'Working hour ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Working hour deleted successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing working_hour:write ability'
            ),
            new OA\Response(
                response: 404,
                description: 'Working hour not found'
            ),
        ]
    )]
    public function destroy() {}
#[OA\Get(
    path: '/provider/locations/{locationId}/working-hours/days-off',
    summary: 'Get weekly days off for a location',
    description: 'Returns the days of the week on which the specified location has no active working hours. The location must belong to the authenticated provider.',
    tags: ['Working Hours'],
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'locationId',
            in: 'path',
            required: true,
            description: 'Location ID',
            schema: new OA\Schema(
                type: 'integer',
                example: 1
            )
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Weekly days off retrieved successfully'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
        new OA\Response(
            response: 403,
            description: 'Location does not belong to the authenticated provider'
        ),
        new OA\Response(
            response: 404,
            description: 'Location not found'
        ),
    ]
)]
public function daysOff(): void
{
}
}
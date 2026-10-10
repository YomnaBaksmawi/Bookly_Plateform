<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class ScheduleExceptionApi
{
    // GET /schedule-exceptions
    #[OA\Get(
        path: '/schedule-exceptions',
        tags: ['Schedule Exceptions'],
        summary: 'List schedule exceptions',
        description: 'Requires the schedule_exception:read ability.',
        security: [
            ['sanctum' => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Schedule exceptions retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing schedule_exception:read ability'
            ),
        ]
    )]
    public function index() {}

    // GET /schedule-exceptions/{scheduleException}
    #[OA\Get(
        path: '/schedule-exceptions/{scheduleException}',
        tags: ['Schedule Exceptions'],
        summary: 'Get a schedule exception',
        description: 'Requires the schedule_exception:read ability.',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'scheduleException',
                in: 'path',
                required: true,
                description: 'Schedule exception ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Schedule exception retrieved successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing schedule_exception:read ability'
            ),
            new OA\Response(
                response: 404,
                description: 'Schedule exception not found'
            ),
        ]
    )]
    public function show() {}

    // GET /locations/{locationId}/schedule-exceptions
    #[OA\Get(
        path: '/locations/{locationId}/schedule-exceptions',
        tags: ['Schedule Exceptions'],
        summary: 'Get schedule exceptions by location',
        description: 'Returns schedule exceptions configured for a specific location.',
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
                description: 'Location schedule exceptions retrieved successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing schedule_exception:read ability'
            ),
        ]
    )]
    public function byLocation() {}

    // POST /schedule-exceptions
    #[OA\Post(
        path: '/schedule-exceptions',
        tags: ['Schedule Exceptions'],
        summary: 'Create a schedule exception',
        description: 'Creates a schedule exception for a location. Requires the schedule_exception:write ability.',
        security: [
            ['sanctum' => []]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'location_id',
                    'date',
                    'type',
                ],
                properties: [
                    new OA\Property(
                        property: 'location_id',
                        type: 'integer',
                        example: 1,
                        description: 'ID of the location.'
                    ),
                    new OA\Property(
                        property: 'date',
                        type: 'string',
                        format: 'date',
                        example: '2026-09-25',
                        description: 'Date of the schedule exception.'
                    ),
                    new OA\Property(
                        property: 'type',
                        type: 'string',
                        enum: ['holiday', 'custom_hours'],
                        example: 'holiday',
                        description: 'Type of schedule exception.'
                    ),
                    new OA\Property(
                        property: 'start_time',
                        type: 'string',
                        nullable: true,
                        pattern: '^([01][0-9]|2[0-3]):[0-5][0-9]$',
                        example: '09:00',
                        description: 'Start time in H:i format.'
                    ),
                    new OA\Property(
                        property: 'end_time',
                        type: 'string',
                        nullable: true,
                        pattern: '^([01][0-9]|2[0-3]):[0-5][0-9]$',
                        example: '13:00',
                        description: 'End time in H:i format. Must be after start_time.'
                    ),
                    new OA\Property(
                        property: 'reason',
                        type: 'string',
                        nullable: true,
                        maxLength: 500,
                        example: 'Public holiday',
                        description: 'Optional reason for the exception.'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Schedule exception created successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing schedule_exception:write ability'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function store() {}

    // PUT /schedule-exceptions/{scheduleException}
    #[OA\Put(
        path: '/schedule-exceptions/{scheduleException}',
        tags: ['Schedule Exceptions'],
        summary: 'Update a schedule exception',
        description: 'Updates an existing schedule exception. Requires the schedule_exception:write ability.',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'scheduleException',
                in: 'path',
                required: true,
                description: 'Schedule exception ID',
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
                        property: 'type',
                        type: 'string',
                        enum: ['holiday', 'custom_hours'],
                        example: 'custom_hours',
                        description: 'Type of schedule exception.'
                    ),
                    new OA\Property(
                        property: 'start_time',
                        type: 'string',
                        nullable: true,
                        pattern: '^([01][0-9]|2[0-3]):[0-5][0-9]$',
                        example: '10:00',
                        description: 'Start time in H:i format.'
                    ),
                    new OA\Property(
                        property: 'end_time',
                        type: 'string',
                        nullable: true,
                        pattern: '^([01][0-9]|2[0-3]):[0-5][0-9]$',
                        example: '14:00',
                        description: 'End time in H:i format. Must be after start_time.'
                    ),
                    new OA\Property(
                        property: 'reason',
                        type: 'string',
                        nullable: true,
                        maxLength: 500,
                        example: 'Modified working hours',
                        description: 'Optional reason for the exception.'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Schedule exception updated successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing schedule_exception:write ability'
            ),
            new OA\Response(
                response: 404,
                description: 'Schedule exception not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function update() {}

    // DELETE /schedule-exceptions/{scheduleException}
    #[OA\Delete(
        path: '/schedule-exceptions/{scheduleException}',
        tags: ['Schedule Exceptions'],
        summary: 'Delete a schedule exception',
        description: 'Deletes a schedule exception. Requires the schedule_exception:write ability.',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'scheduleException',
                in: 'path',
                required: true,
                description: 'Schedule exception ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Schedule exception deleted successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing schedule_exception:write ability'
            ),
            new OA\Response(
                response: 404,
                description: 'Schedule exception not found'
            ),
        ]
    )]
    public function destroy() {}
}
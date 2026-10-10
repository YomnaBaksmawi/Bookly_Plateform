<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class PublicHolidayApi
{
    #[OA\Get(
        path: '/public-holidays',
        summary: 'List public holidays',
        description: 'Returns a paginated list of public holidays. Requires public_holiday:read ability.',
        tags: ['Public Holidays'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Public holidays retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing required ability'
            )
        ]
    )]
    public function index(): void
    {
    }

    #[OA\Get(
        path: '/public-holidays/{publicHoliday}',
        summary: 'Get a public holiday',
        description: 'Returns a specific public holiday. Requires public_holiday:read ability.',
        tags: ['Public Holidays'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'publicHoliday',
                in: 'path',
                required: true,
                description: 'Public holiday ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Public holiday retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing required ability'
            ),
            new OA\Response(
                response: 404,
                description: 'Public holiday not found'
            )
        ]
    )]
    public function show(): void
    {
    }

    #[OA\Get(
        path: '/locations/{locationId}/public-holidays',
        summary: 'Get public holidays by location',
        description: 'Returns public holidays associated with a specific location. Requires public_holiday:read ability.',
        tags: ['Public Holidays'],
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
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Public holidays retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing required ability'
            ),
            new OA\Response(
                response: 404,
                description: 'Location not found'
            )
        ]
    )]
    public function byLocation(): void
    {
    }

    #[OA\Post(
        path: '/public-holidays',
        summary: 'Create a public holiday',
        description: 'Creates a new public holiday. Requires public_holiday:write ability.',
        tags: ['Public Holidays'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'name',
                    'date',
                    'country',
                ],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        maxLength: 255,
                        example: 'Christmas Day',
                        description: 'Name of the public holiday.'
                    ),
                    new OA\Property(
                        property: 'date',
                        type: 'string',
                        format: 'date',
                        example: '2026-12-25',
                        description: 'Date of the public holiday.'
                    ),
                    new OA\Property(
                        property: 'country',
                        type: 'string',
                        maxLength: 100,
                        example: 'USA',
                        description: 'Country where the public holiday applies.'
                    ),
                    new OA\Property(
                        property: 'location_id',
                        type: 'integer',
                        nullable: true,
                        example: 1,
                        description: 'Optional location ID.'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Christmas holiday',
                        description: 'Optional description of the public holiday.'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Public holiday created successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing required ability'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            )
        ]
    )]
    public function store(): void
    {
    }

    #[OA\Put(
        path: '/public-holidays/{publicHoliday}',
        summary: 'Update a public holiday',
        description: 'Updates an existing public holiday. Requires public_holiday:write ability.',
        tags: ['Public Holidays'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'publicHoliday',
                in: 'path',
                required: true,
                description: 'Public holiday ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        maxLength: 255,
                        example: 'Christmas Day',
                        description: 'Optional holiday name.'
                    ),
                    new OA\Property(
                        property: 'date',
                        type: 'string',
                        format: 'date',
                        example: '2026-12-25',
                        description: 'Optional holiday date.'
                    ),
                    new OA\Property(
                        property: 'country',
                        type: 'string',
                        maxLength: 100,
                        example: 'USA',
                        description: 'Optional country.'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Updated holiday description',
                        description: 'Optional description.'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Public holiday updated successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing required ability'
            ),
            new OA\Response(
                response: 404,
                description: 'Public holiday not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            )
        ]
    )]
    public function update(): void
    {
    }

    #[OA\Delete(
        path: '/public-holidays/{publicHoliday}',
        summary: 'Delete a public holiday',
        description: 'Deletes a public holiday. Requires public_holiday:write ability.',
        tags: ['Public Holidays'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'publicHoliday',
                in: 'path',
                required: true,
                description: 'Public holiday ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Public holiday deleted successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Missing required ability'
            ),
            new OA\Response(
                response: 404,
                description: 'Public holiday not found'
            )
        ]
    )]
    public function destroy(): void
    {
    }
}
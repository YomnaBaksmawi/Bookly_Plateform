<?php
namespace App\Docs;

use OpenApi\Attributes as OA;

class LocationApi
{
    // GET /locations
    #[OA\Get(
        path: '/locations',
        tags: ['Locations'],
        summary: 'List locations',
        description: 'Returns a paginated list of active locations.',
        security: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Locations retrieved successfully'
            ),
        ]
    )]
    public function index() {}

    // GET /locations/{location}
    #[OA\Get(
        path: '/locations/{location}',
        tags: ['Locations'],
        summary: 'Get location',
        security: [],
        parameters: [
            new OA\Parameter(
                name: 'location',
                in: 'path',
                required: true,
                description: 'Location ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Location retrieved successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Location not found'
            ),
        ]
    )]
    public function show() {}

 
   

    // POST /locations
    #[OA\Post(
        path: '/locations',
        tags: ['Locations'],
        summary: 'Create a location',
        description: 'Create a new location. Available to providers and administrators.',
        security: [
            ['sanctum' => []]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'name',
                    'address',
                    'city',
                    'country',
                    'timezone',
                ],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Main Branch'
                    ),
                    new OA\Property(
                        property: 'address',
                        type: 'string',
                        example: '123 Main Street'
                    ),
                    new OA\Property(
                        property: 'city',
                        type: 'string',
                        example: 'New York'
                    ),
                    new OA\Property(
                        property: 'country',
                        type: 'string',
                        example: 'USA'
                    ),
                    new OA\Property(
                        property: 'latitude',
                        type: 'number',
                        format: 'float',
                        nullable: true,
                        example: 40.7128
                    ),
                    new OA\Property(
                        property: 'longitude',
                        type: 'number',
                        format: 'float',
                        nullable: true,
                        example: -74.0060
                    ),
                    new OA\Property(
                        property: 'is_primary',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'is_active',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'timezone',
                        type: 'string',
                        example: 'America/New_York'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Location created successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function store() {}

    // PUT /locations/{location}
    #[OA\Put(
        path: '/locations/{location}',
        tags: ['Locations'],
        summary: 'Update a location',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'location',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Downtown Branch'
                    ),
                    new OA\Property(
                        property: 'address',
                        type: 'string',
                        example: '456 Main Street'
                    ),
                    new OA\Property(
                        property: 'city',
                        type: 'string',
                        example: 'New York'
                    ),
                    new OA\Property(
                        property: 'country',
                        type: 'string',
                        example: 'USA'
                    ),
                    new OA\Property(
                        property: 'latitude',
                        type: 'number',
                        format: 'float',
                        nullable: true,
                        example: 40.7128
                    ),
                    new OA\Property(
                        property: 'longitude',
                        type: 'number',
                        format: 'float',
                        nullable: true,
                        example: -74.0060
                    ),
                    new OA\Property(
                        property: 'timezone',
                        type: 'string',
                        example: 'America/New_York'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Location updated successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Location not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function update() {}

    // DELETE /locations/{location}
    #[OA\Delete(
        path: '/locations/{location}',
        tags: ['Locations'],
        summary: 'Delete a location',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'location',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Location deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Location not found'
            ),
        ]
    )]
    public function destroy() {}

}

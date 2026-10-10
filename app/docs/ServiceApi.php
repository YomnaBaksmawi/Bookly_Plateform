<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class ServiceApi
{
    // GET /services/search
#[OA\Get(
    path: '/services/search',
    tags: ['Services'],
    summary: 'Search services',
    description: 'Search services by keyword and optionally filter by location.',
    security: [],
    parameters: [
        new OA\Parameter(
            name: 'q',
            in: 'query',
            required: true,
            description: 'Search term. Minimum 2 characters, maximum 100 characters.',
            schema: new OA\Schema(
                type: 'string',
                minLength: 2,
                maxLength: 100,
                example: 'Haircut'
            )
        ),
        new OA\Parameter(
            name: 'location_id',
            in: 'query',
            required: false,
            description: 'Optional location ID to filter services by location.',
            schema: new OA\Schema(
                type: 'integer',
                example: 1
            )
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Services found successfully'
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error'
        ),
    ]
)]
public function search() {}


    // // GET /services/category/{category}
    // #[OA\Get(
    //     path: '/services/category/{category}',
    //     tags: ['Services'],
    //     summary: 'Get services by category',
    //     security: [],
    //     parameters: [
    //         new OA\Parameter(
    //             name: 'category',
    //             in: 'path',
    //             required: true,
    //             description: 'Category ID',
    //             schema: new OA\Schema(type: 'integer')
    //         ),
    //     ],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: 'Services retrieved successfully'
    //         ),
    //     ]
    // )]
    // public function byCategory() {}

    // // GET /services/provider/{provider}
    // #[OA\Get(
    //     path: '/services/provider/{provider}',
    //     tags: ['Services'],
    //     summary: 'Get services by provider',
    //     security: [],
    //     parameters: [
    //         new OA\Parameter(
    //             name: 'provider',
    //             in: 'path',
    //             required: true,
    //             description: 'Provider user ID',
    //             schema: new OA\Schema(type: 'integer')
    //         ),
    //     ],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: 'Services retrieved successfully'
    //         ),
    //     ]
    // )]
    // public function byProvider() {}

    // // GET /services/location/{location}
    // #[OA\Get(
    //     path: '/services/location/{location}',
    //     tags: ['Services'],
    //     summary: 'Get services by location',
    //     security: [],
    //     parameters: [
    //         new OA\Parameter(
    //             name: 'location',
    //             in: 'path',
    //             required: true,
    //             description: 'Location ID',
    //             schema: new OA\Schema(type: 'integer')
    //         ),
    //     ],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: 'Services retrieved successfully'
    //         ),
    //     ]
    // )]
    // public function byLocation() {}

    // GET /services
    #[OA\Get(
        path: '/services',
        tags: ['Services'],
        summary: 'List services',
        security: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Services retrieved successfully'
            ),
        ]
    )]



    public function index() {}

    // GET /services/{service}
    #[OA\Get(
        path: '/services/{service}',
        tags: ['Services'],
        summary: 'Get service',
        security: [],
        parameters: [
            new OA\Parameter(
                name: 'service',
                in: 'path',
                required: true,
                description: 'Service ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Service retrieved successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Service not found'
            ),
        ]
    )]
    public function show() {}

    // POST /services
    #[OA\Post(
        path: '/services',
        tags: ['Services'],
        summary: 'Create a service',
        description: 'Create a service. Available to providers and administrators.',
        security: [
            ['sanctum' => []]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'category_id',
                    'name',
                    'duration',
                    'price',
                ],
                properties: [
                    new OA\Property(
                        property: 'category_id',
                        type: 'integer',
                        example: 1
                    ),
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Premium Haircut'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Professional haircut service.'
                    ),
                    new OA\Property(
                        property: 'duration',
                        type: 'integer',
                        description: 'Service duration in minutes.',
                        example: 60
                    ),
                    new OA\Property(
                        property: 'price',
                        type: 'number',
                        format: 'float',
                        example: 35.00
                    ),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'inactive'],
                        example: 'active'
                    ),
                       new OA\Property(
                    property: 'location_ids',
                    type: 'array',
                    description: 'IDs of the locations where this service is available.',
                    items: new OA\Items(
                        type: 'integer'
                    ),
                    example: [1, 2]
                ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Service created successfully'
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

    // PUT /services/{service}
    #[OA\Put(
        path: '/services/{service}',
        tags: ['Services'],
        summary: 'Update a service',
        description: 'Update a service. Available to providers and administrators.',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'service',
                in: 'path',
                required: true,
                description: 'Service ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'category_id',
                        type: 'integer',
                        example: 1
                    ),
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Premium Haircut'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Updated service description.'
                    ),
                    new OA\Property(
                        property: 'duration',
                        type: 'integer',
                        example: 90
                    ),
                    new OA\Property(
                        property: 'price',
                        type: 'number',
                        format: 'float',
                        example: 45.00
                    ),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'inactive'],
                        example: 'active'
                    ),
                       new OA\Property(
                    property: 'location_ids',
                    type: 'array',
                    description: 'IDs of the locations where this service is available.',
                    items: new OA\Items(
                        type: 'integer'
                    ),
                    example: [1, 2]
                ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Service updated successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Service not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function update() {}

    // DELETE /services/{service}
    #[OA\Delete(
        path: '/services/{service}',
        tags: ['Services'],
        summary: 'Delete a service',
        description: 'Soft delete a service.',
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'service',
                in: 'path',
                required: true,
                description: 'Service ID',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Service deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Service not found'
            ),
        ]
    )]
    public function destroy() {}

    // /*
    // |--------------------------------------------------------------------------
    // | Service Locations
    // |--------------------------------------------------------------------------
    // */

    // // GET /service-locations
    // #[OA\Get(
    //     path: '/service-locations',
    //     tags: ['Service Locations'],
    //     summary: 'List service locations',
    //     description: 'Returns the service-location associations available to the authenticated provider or administrator.',
    //     security: [
    //         ['sanctum' => []]
    //     ],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: 'Service locations retrieved successfully'
    //         ),
    //         new OA\Response(
    //             response: 401,
    //             description: 'Unauthenticated'
    //         ),
    //         new OA\Response(
    //             response: 403,
    //             description: 'Unauthorized'
    //         ),
    //     ]
    // )]
    // public function serviceLocationIndex() {}

    // // POST /service-locations
    // #[OA\Post(
    //     path: '/service-locations',
    //     tags: ['Service Locations'],
    //     summary: 'Attach a service to a location',
    //     description: 'Creates a service-location association.',
    //     security: [
    //         ['sanctum' => []]
    //     ],
    //     requestBody: new OA\RequestBody(
    //         required: true,
    //         content: new OA\JsonContent(
    //             required: [
    //                 'service_id',
    //                 'location_id',
    //             ],
    //             properties: [
    //                 new OA\Property(
    //                     property: 'service_id',
    //                     type: 'integer',
    //                     example: 1
    //                 ),
    //                 new OA\Property(
    //                     property: 'location_id',
    //                     type: 'integer',
    //                     example: 1
    //                 ),
    //             ]
    //         )
    //     ),
    //     responses: [
    //         new OA\Response(
    //             response: 201,
    //             description: 'Service location created successfully'
    //         ),
    //         new OA\Response(
    //             response: 403,
    //             description: 'Unauthorized'
    //         ),
    //         new OA\Response(
    //             response: 422,
    //             description: 'Validation error'
    //         ),
    //     ]
    // )]
    // public function serviceLocationStore() {}

    // // GET /service-locations/service/{service}
    // #[OA\Get(
    //     path: '/service-locations/service/{service}',
    //     tags: ['Service Locations'],
    //     summary: 'Get locations for a service',
    //     security: [
    //         ['sanctum' => []]
    //     ],
    //     parameters: [
    //         new OA\Parameter(
    //             name: 'service',
    //             in: 'path',
    //             required: true,
    //             description: 'Service ID',
    //             schema: new OA\Schema(type: 'integer')
    //         ),
    //     ],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: 'Service locations retrieved successfully'
    //         ),
    //     ]
    // )]
    // public function byService() {}

    // // GET /service-locations/location/{location}
    // #[OA\Get(
    //     path: '/service-locations/location/{location}',
    //     tags: ['Service Locations'],
    //     summary: 'Get services available at a location',
    //     security: [
    //         ['sanctum' => []]
    //     ],
    //     parameters: [
    //         new OA\Parameter(
    //             name: 'location',
    //             in: 'path',
    //             required: true,
    //             description: 'Location ID',
    //             schema: new OA\Schema(type: 'integer')
    //         ),
    //     ],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: 'Location services retrieved successfully'
    //         ),
    //     ]
    // )]
    // public function serviceLocationByLocation() {}

    // // DELETE /service-locations/{serviceLocation}
    // #[OA\Delete(
    //     path: '/service-locations/{serviceLocation}',
    //     tags: ['Service Locations'],
    //     summary: 'Remove a service from a location',
    //     description: 'Deletes the service-location association.',
    //     security: [
    //         ['sanctum' => []]
    //     ],
    //     parameters: [
    //         new OA\Parameter(
    //             name: 'serviceLocation',
    //             in: 'path',
    //             required: true,
    //             description: 'Service-location ID',
    //             schema: new OA\Schema(type: 'integer')
    //         ),
    //     ],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: 'Service location deleted successfully'
    //         ),
    //         new OA\Response(
    //             response: 404,
    //             description: 'Service location not found'
    //         ),
    //     ]
    // )]
    // public function serviceLocationDestroy() {}
}
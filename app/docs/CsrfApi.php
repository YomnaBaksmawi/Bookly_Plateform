<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/csrf-token',
    summary: 'Get API CSRF token',
    description: 'Generates a CSRF token for the authenticated user and sets it in the XSRF-TOKEN cookie. The token must be sent in the X-CSRF-TOKEN header for state-changing requests.',
    tags: ['CSRF'],
    security: [
        ['sanctum' => []]
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'CSRF token generated successfully',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(
                                property: 'csrf_token',
                                type: 'string',
                                example: 'abc123xyz...'
                            ),
                        ]
                    ),
                ]
            )
        ),

        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),

        new OA\Response(
            response: 429,
            description: 'Too many requests'
        ),
    ]
)]
class CsrfApi
{
}
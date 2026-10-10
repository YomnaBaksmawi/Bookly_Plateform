<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class ProviderProfileApi
{
    #[OA\Post(
        path: '/provider/profile',
        summary: 'Create provider profile',
        description: 'Create the profile for the authenticated provider.',
        tags: ['Provider Profile'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'business_name',
                        type: 'string',
                        example: 'John Dental Clinic'
                    ),
                    new OA\Property(
                        property: 'bio',
                        type: 'string',
                        example: 'Professional dental care services.'
                    ),
                    new OA\Property(
                        property: 'phone',
                        type: 'string',
                        example: '+1234567890'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Provider profile created successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'User is not authorized to create a provider profile'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests'
            ),
        ]
    )]
    public function store(): void
    {
    }

    #[OA\Get(
        path: '/provider/profile',
        summary: 'Get provider profile',
        description: 'Retrieve the authenticated provider profile.',
        tags: ['Provider Profile'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Provider profile retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'User is not authorized to access the provider profile'
            ),
            new OA\Response(
                response: 404,
                description: 'Provider profile not found'
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests'
            ),
        ]
    )]
    public function show(): void
    {
    }

    #[OA\Put(
        path: '/provider/profile',
        summary: 'Update provider profile',
        description: 'Update the authenticated provider profile.',
        tags: ['Provider Profile'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'business_name',
                        type: 'string',
                        example: 'John Dental Clinic'
                    ),
                    new OA\Property(
                        property: 'bio',
                        type: 'string',
                        example: 'Updated description.'
                    ),
                    new OA\Property(
                        property: 'phone',
                        type: 'string',
                        example: '+1234567890'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Provider profile updated successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'User is not authorized to update the provider profile'
            ),
            new OA\Response(
                response: 404,
                description: 'Provider profile not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests'
            ),
        ]
    )]
    public function update(): void
    {
    }
}
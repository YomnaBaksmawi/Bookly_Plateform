<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class CategoryApi
{
    #[OA\Get(
        path: '/categories',
        summary: 'List categories',
        description: 'Retrieve all available categories.',
        tags: ['Categories'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Categories retrieved successfully'
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
    public function index(): void
    {
    }

    #[OA\Get(
        path: '/categories/{category}',
        summary: 'Get category',
        description: 'Retrieve a specific category by ID.',
        tags: ['Categories'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'category',
                description: 'Category ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 404,
                description: 'Category not found'
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

    #[OA\Post(
        path: '/categories',
        summary: 'Create category',
        description: 'Create a new category. Available to admin and provider users.',
        tags: ['Categories'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'slug'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Dental Services'
                    ),
                    new OA\Property(
                        property: 'slug',
                        type: 'string',
                        example: 'dental-services'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Dental and oral care services.'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Category created successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Only admin and provider users can create categories'
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

    #[OA\Put(
        path: '/categories/{category}',
        summary: 'Update category',
        description: 'Update an existing category. Available to admin and provider users.',
        tags: ['Categories'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'category',
                description: 'Category ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Dental Services'
                    ),
                    new OA\Property(
                        property: 'slug',
                        type: 'string',
                        example: 'dental-services'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Dental and oral care services.'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category updated successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Only admin and provider users can update categories'
            ),
            new OA\Response(
                response: 404,
                description: 'Category not found'
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

    #[OA\Delete(
        path: '/categories/{category}',
        summary: 'Delete category',
        description: 'Delete an existing category. Available to admin and provider users.',
        tags: ['Categories'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'category',
                description: 'Category ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category deleted successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Only admin and provider users can delete categories'
            ),
            new OA\Response(
                response: 404,
                description: 'Category not found'
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests'
            ),
        ]
    )]
    public function destroy(): void
    {
    }
}
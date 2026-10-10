<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class PaymentApi
{
   
  #[OA\Post(
    path: '/payments',
    summary: 'Create a payment',
    description: 'Creates a payment for a booking using the configured payment gateway. Requires Sanctum authentication and an Idempotency-Key header.',
    tags: ['Payments'],
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'Idempotency-Key',
            in: 'header',
            required: true,
            description: 'Unique key used to safely retry the payment request without creating duplicate payments.',
            schema: new OA\Schema(
                type: 'string',
                example: 'payment-order-123456'
            )
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['booking_id', 'amount'],
            properties: [
                new OA\Property(
                    property: 'booking_id',
                    type: 'integer',
                    example: 6
                ),
                new OA\Property(
                    property: 'amount',
                    type: 'number',
                    format: 'float',
                    example: 99.99
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Payment created successfully'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
        new OA\Response(
            response: 409,
            description: 'Duplicate idempotency key'
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error'
        ),
    ]
)]
public function store(): void
{
}
#[OA\Get(
    path: '/provider/payments',
    summary: 'List provider payments',
    description: 'Returns a paginated list of payments for bookings associated with the authenticated provider.',
    tags: ['Payments'],
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Provider payments retrieved successfully'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
        new OA\Response(
            response: 403,
            description: 'Provider access required'
        ),
    ]
)]
public function providerIndex(): void
{
}
#[OA\Get(
    path: '/payments',
    summary: 'List payments',
    description: 'Returns a paginated list of payments. Customers can only see their own payments, while administrators can see all payments.',
    tags: ['Payments'],
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: false,
            description: 'Page number.',
            schema: new OA\Schema(
                type: 'integer',
                minimum: 1,
                example: 1
            )
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Payments retrieved successfully',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Payments retrieved successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(
                                property: 'current_page',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'data',
                                type: 'array',
                                items: new OA\Items(
                                    type: 'object',
                                    properties: [
                                        new OA\Property(
                                            property: 'id',
                                            type: 'integer',
                                            example: 1
                                        ),
                                        new OA\Property(
                                            property: 'booking_id',
                                            type: 'integer',
                                            example: 15
                                        ),
                                        new OA\Property(
                                            property: 'amount',
                                            type: 'number',
                                            format: 'float',
                                            example: 99.99
                                        ),
                                        new OA\Property(
                                            property: 'status',
                                            type: 'string',
                                            enum: [
                                                'pending',
                                                'paid',
                                                'failed',
                                                'refunded'
                                            ],
                                            example: 'paid'
                                        ),
                                        new OA\Property(
                                            property: 'provider',
                                            type: 'string',
                                            example: 'mock'
                                        ),
                                        new OA\Property(
                                            property: 'transaction_id',
                                            type: 'string',
                                            nullable: true,
                                            example: 'mock_550e8400-e29b-41d4-a716-446655440000'
                                        ),
                                        new OA\Property(
                                            property: 'created_at',
                                            type: 'string',
                                            format: 'date-time',
                                            example: '2026-09-20T10:30:00Z'
                                        ),
                                    ]
                                )
                            ),
                            new OA\Property(
                                property: 'first_page_url',
                                type: 'string',
                                example: 'http://localhost:8000/api/v1/payments?page=1'
                            ),
                            new OA\Property(
                                property: 'last_page',
                                type: 'integer',
                                example: 3
                            ),
                            new OA\Property(
                                property: 'per_page',
                                type: 'integer',
                                example: 15
                            ),
                            new OA\Property(
                                property: 'total',
                                type: 'integer',
                                example: 42
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
    ]
)]
public function index(): void {}
#[OA\Get(
    path: '/payments/{payment}',
    summary: 'Get payment status',
    description: 'Returns the details and current status of a payment. Customers can only access payments belonging to their own bookings.',
    tags: ['Payments'],
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(
            name: 'payment',
            in: 'path',
            required: true,
            description: 'Payment ID',
            schema: new OA\Schema(
                type: 'integer',
                example: 1
            )
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Payment retrieved successfully',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Payment retrieved successfully.'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(
                                property: 'id',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'booking_id',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'amount',
                                type: 'number',
                                format: 'float',
                                example: 99.99
                            ),
                            new OA\Property(
                                property: 'status',
                                type: 'string',
                                example: 'paid'
                            ),
                            new OA\Property(
                                property: 'provider',
                                type: 'string',
                                example: 'mock'
                            ),
                            new OA\Property(
                                property: 'transaction_id',
                                type: 'string',
                                example: 'txn_mock_123456',
                                nullable: true
                            ),
                            new OA\Property(
                                property: 'created_at',
                                type: 'string',
                                format: 'date-time',
                                example: '2026-09-20T10:30:00Z'
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
            response: 404,
            description: 'Payment not found or does not belong to the authenticated customer'
        ),
    ]
)]
public function show(): void
{
}
 #[OA\Post(
    path: '/payments/webhook',
    summary: 'Handle payment provider webhook',
    description: 'Receives payment events from the payment provider. This endpoint does not require Sanctum authentication. The request must include a valid HMAC-SHA256 signature in X-Payment-Signature.',
    tags: ['Payments'],
    parameters: [
        new OA\Parameter(
            name: 'X-Payment-Signature',
            in: 'header',
            required: true,
            description: 'HMAC-SHA256 signature generated from the raw JSON request body using the configured webhook secret.',
            schema: new OA\Schema(
                type: 'string',
                example: '9f2d7b4c8e1a...'
            )
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['id', 'type', 'data'],
            properties: [
                new OA\Property(
                    property: 'id',
                    type: 'string',
                    example: 'evt_test_001',
                    description: 'Unique webhook event ID.'
                ),
                new OA\Property(
                    property: 'type',
                    type: 'string',
                    enum: [
                        'payment.succeeded',
                        'payment.failed',
                        'payment.refunded'
                    ],
                    example: 'payment.succeeded',
                    description: 'Webhook event type.'
                ),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    required: ['transaction_id'],
                    properties: [
                        new OA\Property(
                            property: 'transaction_id',
                            type: 'string',
                            example: 'mock_123456',
                            description: 'Transaction ID of the payment.'
                        )
                    ]
                )
            ],
            example: [
                'id' => 'evt_test_001',
                'type' => 'payment.succeeded',
                'data' => [
                    'transaction_id' => 'mock_123456',
                ],
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Webhook processed successfully'
        ),
        new OA\Response(
            response: 401,
            description: 'Invalid webhook signature'
        ),
        new OA\Response(
            response: 404,
            description: 'Payment not found'
        ),
        new OA\Response(
            response: 422,
            description: 'Invalid or unsupported webhook payload'
        )
    ]
)]
public function webhook(): void {}

#[OA\Post(
    path: '/payments/webhook/test-signature',
    summary: 'Generate webhook signature for local testing',
    description: 'Generates the HMAC-SHA256 signature for the given webhook payload. This endpoint is available only in the local environment and is intended for testing the payment webhook from Swagger.',
    tags: ['Payments'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['id', 'type', 'data'],
            properties: [
                new OA\Property(
                    property: 'id',
                    type: 'string',
                    example: 'evt_test_001',
                    description: 'Unique webhook event ID.'
                ),
                new OA\Property(
                    property: 'type',
                    type: 'string',
                    enum: [
                        'payment.succeeded',
                        'payment.failed',
                        'payment.refunded',
                    ],
                    example: 'payment.succeeded',
                    description: 'Payment webhook event type.'
                ),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    required: ['transaction_id'],
                    properties: [
                        new OA\Property(
                            property: 'transaction_id',
                            type: 'string',
                            example: 'mock_123456',
                            description: 'Transaction ID associated with the payment.'
                        ),
                    ]
                ),
            ],
            example: [
                'id' => 'evt_test_001',
                'type' => 'payment.succeeded',
                'data' => [
                    'transaction_id' => 'mock_123456',
                ],
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Webhook signature generated successfully',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'signature',
                        type: 'string',
                        example: '9f2d7b4c8e1a1234567890abcdef1234567890abcdef1234567890abcdef1234'
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 404,
            description: 'Endpoint is available only in the local environment'
        ),
    ]
)]
public function generateTestSignature(): void {}


}

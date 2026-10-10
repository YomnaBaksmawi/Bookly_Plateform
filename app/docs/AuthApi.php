<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class AuthApi
{
    #[OA\Post(
        path: '/auth/register',
        summary: 'Register a new user',
        description: 'Create a new provider or customer account.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation', 'role'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'John Doe'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'john@example.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'Password123!'
                    ),
                    new OA\Property(
                        property: 'password_confirmation',
                        type: 'string',
                        format: 'password',
                        example: 'Password123!'
                    ),
                    new OA\Property(
                        property: 'role',
                        type: 'string',
                        enum: ['provider', 'customer'],
                        example: 'customer'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully'
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
    public function register(): void
    {
    }

    #[OA\Post(
        path: '/auth/login',
        summary: 'Login',
        description: 'Authenticate a user and return a Sanctum Bearer token.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'john@example.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'Password123!'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful'
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials'
            ),
            new OA\Response(
                response: 403,
                description: 'User is suspended'
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests'
            ),
        ]
    )]
    public function login(): void
    {
    }

    #[OA\Get(
        path: '/auth/profile',
        summary: 'Get authenticated user profile',
        tags: ['Authentication'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
    public function profile(): void
    {
    }

    #[OA\Put(
        path: '/auth/profile',
        summary: 'Update authenticated user profile',
        tags: ['Authentication'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'John Updated'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'john.updated@example.com'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile updated successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function updateProfile(): void
    {
    }

    #[OA\Post(
        path: '/auth/logout',
        summary: 'Logout',
        description: 'Revoke the current Sanctum token.',
        tags: ['Authentication'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logged out successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
    public function logout(): void
    {
    }

    #[OA\Post(
        path: '/auth/logout-all',
        summary: 'Logout from all devices',
        description: 'Revoke all Sanctum tokens belonging to the authenticated user.',
        tags: ['Authentication'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logged out from all devices successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
    public function logoutAll(): void
    {
    }

    // #[OA\Post(
    //     path: '/auth/verify-email',
    //     summary: 'Verify email address',
    //     tags: ['Authentication'],
    //     security: [['sanctum' => []]],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: 'Email verified successfully'
    //         ),
    //         new OA\Response(
    //             response: 400,
    //             description: 'Invalid verification request'
    //         ),
    //         new OA\Response(
    //             response: 401,
    //             description: 'Unauthenticated'
    //         ),
    //     ]
    // )]
    // public function verifyEmail(): void
    // {
    // }

    // #[OA\Post(
    //     path: '/auth/resend-verification',
    //     summary: 'Resend email verification',
    //     tags: ['Authentication'],
    //     security: [['sanctum' => []]],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: 'Verification email sent successfully'
    //         ),
    //         new OA\Response(
    //             response: 401,
    //             description: 'Unauthenticated'
    //         ),
    //         new OA\Response(
    //             response: 429,
    //             description: 'Too many requests'
    //         ),
    //     ]
    // )]
    // public function resendVerification(): void
    // {
    // }

    #[OA\Post(
        path: '/auth/change-password',
        summary: 'Change password',
        tags: ['Authentication'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(
                        property: 'current_password',
                        type: 'string',
                        format: 'password',
                        example: 'OldPassword123!'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'NewPassword123!'
                    ),
                    new OA\Property(
                        property: 'password_confirmation',
                        type: 'string',
                        format: 'password',
                        example: 'NewPassword123!'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password changed successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated or current password is incorrect'
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
    public function changePassword(): void
    {
    }
}
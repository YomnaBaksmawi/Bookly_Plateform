<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Bookly API',
    version: '1.0.0',
    description: 'REST API for the Bookly service booking platform.'
)]
#[OA\Server(
    url: '/api/v1',
    description: 'Bookly API v1'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum Token'
)]

#[OA\Tag(name: 'Auth', description: 'Authentication and account management')]
#[OA\Tag(name: 'CSRF', description: 'API CSRF token management')]
#[OA\Tag(name: 'Provider', description: 'Provider profile management')]
#[OA\Tag(name: 'Availability', description: 'Provider availability management')]
#[OA\Tag(name: 'Locations', description: 'Provider locations management')]
#[OA\Tag(name: 'Categories', description: 'Service categories management')]
#[OA\Tag(name: 'Services', description: 'Services and service locations')]
#[OA\Tag(name: 'Working Hours', description: 'Working hours management')]
#[OA\Tag(name: 'Schedule Exceptions', description: 'Schedule exceptions management')]
#[OA\Tag(name: 'Public Holidays', description: 'Public holidays management')]
#[OA\Tag(name: 'Bookings', description: 'Booking management')]
#[OA\Tag(name: 'Payments', description: 'Payment management and webhooks')]
#[OA\Tag(name: 'Admin - Users', description: 'Admin user management')]
#[OA\Tag(name: 'Admin - Audit Logs', description: 'Admin audit log management')]
#[OA\Tag(name: 'Admin - Reports', description: 'Admin reports and analytics')]
#[OA\Tag(name: 'Admin - Locations', description: 'Admin location management')]
#[OA\Tag(name: 'Admin - Services', description: 'Admin service management')]
#[OA\Tag(name: 'Health', description: 'API health checks')]

class OpenApi
{
}
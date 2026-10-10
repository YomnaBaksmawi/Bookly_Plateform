<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/health',
    summary: 'Check API health',
    tags: ['Health'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'API is healthy'
        ),
        new OA\Response(
            response: 503,
            description: 'One or more services are unavailable'
        ),
    ]
)]
class HealthApi
{
}